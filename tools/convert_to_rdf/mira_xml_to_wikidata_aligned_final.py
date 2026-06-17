#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
MIrA XML → RDF Converter
(Wikidata-aligned Linked Open Data generation)

Author:
    Dr. Sudhansu Bala Das
    Email: baladas.sudhansu@gmail.com

Project:
    MIrA (Manuscripts with Irish Associations)
    GLOSSAM Project, University of Galway

Description:
    This script converts MIrA XML catalogue data into RDF aligned
    with the Wikidata WikiProject Manuscripts data model.

Main features:
    1. Generates one combined RDF dataset.
    2. Generates individual RDF files for each MIrA entity type:
        - library
        - manuscript
        - person
        - text

       No separate place entities are generated because places are
       linked directly using Wikidata QIDs.

    
Input files:
    - data/mss_mira/compiled/mss_compiled.xml
    - data/other/libraries.xml
    - data/other/people.xml
    - data/other/places.xml
    - data/other/texts.xml

Output:
    rdf/
        mira_wikidata_aligned.ttl
        mira_wikidata_aligned.jsonld
        mira_wikidata_aligned.rdf
        mira_wikidata_aligned_evaluation_log.txt

        entities/
            library/
            manuscript/
            person/
            text/

        Each entity is also written in Turtle (.ttl), JSON-LD (.jsonld),
        and RDF/XML (.rdf). Numeric filenames are zero-padded, e.g.:
            entities/manuscript/001.ttl

Reference:
    Wikidata WikiProject Manuscripts Data Model:
    https://www.wikidata.org/wiki/Wikidata:WikiProject_Manuscripts/Data_Model
"""

from __future__ import annotations

import argparse
import datetime as _dt
import os
import re
import shutil
import xml.etree.ElementTree as ET
from collections import Counter
from pathlib import Path
from typing import Optional

from rdflib import Graph, Literal, Namespace, URIRef
from rdflib.namespace import OWL, RDF, RDFS, SKOS, XSD

# ---------------------------------------------------------------------
# Namespaces
# ---------------------------------------------------------------------

WD = Namespace("http://www.wikidata.org/entity/")
WDT = Namespace("http://www.wikidata.org/prop/direct/")
MIRA = Namespace("https://mira.ie/entity/")

# Core Wikidata QIDs
Q_MANUSCRIPT = "Q87167"      # manuscript
Q_HUMAN = "Q5"              # human
Q_LIBRARY = "Q7075"         # library
Q_ORG = "Q43229"            # organization
Q_TEXT = "Q7725634"         # literary work / written work-like item, used for MIrA text entities

# Conservative switch: folios are not pages, so do not export as P1104 unless explicitly enabled.
EXPORT_FOLIOS_AS_P1104 = False

# ---------------------------------------------------------------------
# Basic helpers
# ---------------------------------------------------------------------

def norm(s: Optional[str]) -> str:
    return re.sub(r"\s+", " ", (s or "").strip())


def safe_id(s: Optional[str]) -> str:
    value = norm(s) or "unknown"
    return re.sub(r"[^A-Za-z0-9._-]+", "_", value)


def local_uri(kind: str, key: str) -> URIRef:
    """Create local MIrA entity URI. Important: use text, not work."""
    return URIRef(f"{MIRA}{kind}/{safe_id(key)}")


def wdtP(prop: str) -> URIRef:
    """Return proper Wikidata direct-property URI, always wdt:P####."""
    prop = prop.strip()
    if not prop.startswith("P"):
        prop = "P" + prop
    return WDT[prop]


def extract_qid(text: Optional[str]) -> Optional[str]:
    if not text:
        return None
    m = re.search(r"\bQ\d+\b", text)
    return m.group(0) if m else None


def extract_year(text: Optional[str]) -> Optional[str]:
    """
    Return a valid 4-digit gYear string if one can be safely extracted.
    Only explicit 3- or 4-digit years are converted.
    """
    if not text:
        return None
    text = str(text)

    m4 = re.search(r"\b(\d{4})\b", text)
    if m4:
        return m4.group(1)

    m3 = re.search(r"\b(\d{3})\b", text)
    if m3:
        return m3.group(1).zfill(4)

    return None


def int_literal(value: Optional[str]) -> Optional[Literal]:
    if not value:
        return None
    m = re.search(r"\d+", str(value))
    if not m:
        return None
    return Literal(int(m.group(0)), datatype=XSD.integer)


def dimension_cm_literal(value: Optional[str]) -> Optional[Literal]:
    """
    Normalise page_h/page_w values to centimetres for Wikidata P2048/P2049.

    Some MIrA source values are encoded without a decimal point:
        225 = 22.5 cm
        155 = 15.5 cm

    These are not treated as millimetres here; the function simply restores
    the decimal scale by dividing values >= 100 by 10. Values below 100 are
    assumed already to be centimetres.
    """
    if not value:
        return None
    m = re.search(r"\d+(?:\.\d+)?", str(value))
    if not m:
        return None
    num = float(m.group(0))
    cm = num / 10.0 if num >= 100 else num
    return Literal(f"{cm:.2f}".rstrip("0").rstrip("."), datatype=XSD.decimal)


def bind_prefixes(g: Graph) -> None:
    g.bind("wd", WD)
    g.bind("wdt", WDT)
    g.bind("rdf", RDF)
    g.bind("rdfs", RDFS)
    g.bind("skos", SKOS)
    g.bind("owl", OWL)
    g.bind("xsd", XSD)
    g.bind("mira", MIRA)


def parse_xml(path: Path) -> ET.Element:
    return ET.parse(path).getroot()


def normalize_title_key(s: str) -> str:
    s = norm(s).lower()
    s = re.sub(r"[’'`]", "", s)
    s = re.sub(r"[^a-z0-9\s]", " ", s)
    return re.sub(r"\s+", " ", s).strip()


def output_file_stem(key: str) -> str:
    """
    Return a stable output filename stem.

    Numeric entity IDs are zero-padded to three digits:
        1   -> 001
        25  -> 025
        163 -> 163

    Non-numeric IDs keep the cleaned MIrA identifier.
    """
    clean = safe_id(key)
    return clean.zfill(3) if clean.isdigit() else clean

# ---------------------------------------------------------------------
# Authority loading
# ---------------------------------------------------------------------

def load_people(path: Path):
    qid_by_id = {}
    label_by_id = {}
    root = parse_xml(path)
    for p in root.findall("./person"):
        pid = p.get("id") or ""
        label = norm(" ".join(x for x in [p.findtext("firstNames"), p.findtext("surname")] if x))
        qid = None
        for xref in p.findall("xref"):
            if (xref.get("type") or "").lower() == "wikidata":
                qid = extract_qid(xref.text)
        if pid:
            qid_by_id[pid] = qid
            label_by_id[pid] = label or pid
    return qid_by_id, label_by_id


def load_libraries(path: Path):
    name_by_id = {}
    qid_by_id = {}
    city_by_id = {}
    country_by_id = {}
    coords_by_id = {}
    root = parse_xml(path)
    for lib in root.findall("./library"):
        lid = lib.get("id") or ""
        if not lid:
            continue
        name_by_id[lid] = norm(lib.findtext("name")) or lid
        city_by_id[lid] = norm(lib.findtext("city"))
        country_by_id[lid] = norm(lib.findtext("country"))
        coords_by_id[lid] = norm(lib.findtext("coords"))
        qid = None
        for xref in lib.findall("xref"):
            if (xref.get("type") or "").lower() == "wikidata":
                qid = extract_qid(xref.text)
        qid_by_id[lid] = qid
    return name_by_id, qid_by_id, city_by_id, country_by_id, coords_by_id


def load_texts(path: Path):
    qid_by_id = {}
    title_by_id = {}
    author_by_id = {}
    qid_by_norm_title = {}
    id_by_norm_title = {}
    root = parse_xml(path)
    for t in root.findall("./text"):
        tid = t.get("id") or ""
        if not tid:
            continue
        title_el = t.find("title")
        title = norm("".join(title_el.itertext())) if title_el is not None else tid
        author = norm(t.findtext("author"))
        qid = None
        for xref in t.findall("xref"):
            if (xref.get("type") or "").lower() == "wikidata":
                qid = extract_qid(xref.text)
        qid_by_id[tid] = qid
        title_by_id[tid] = title
        author_by_id[tid] = author
        if title:
            nt = normalize_title_key(title)
            id_by_norm_title[nt] = tid
            if qid:
                qid_by_norm_title[nt] = qid
    return qid_by_id, title_by_id, author_by_id, qid_by_norm_title, id_by_norm_title


def load_places(path: Path):
    qid_by_id = {}
    name_by_id = {}
    root = parse_xml(path)

    def walk(el: ET.Element):
        pid = el.get("id") or ""
        if pid:
            first_name = ""
            name_el = el.find("name")
            if name_el is not None and name_el.text:
                first_name = norm(name_el.text)
            qid = None
            for xref in el.findall("xref"):
                if (xref.get("type") or "").lower() == "wikidata":
                    qid = extract_qid(xref.text)
            qid_by_id[pid] = qid
            name_by_id[pid] = first_name or pid
        for child in el.findall("place"):
            walk(child)

    for top in root.findall("./place"):
        walk(top)
    return qid_by_id, name_by_id

# ---------------------------------------------------------------------
# Entity emitters
# ---------------------------------------------------------------------

def add_external_same_as(g: Graph, subj: URIRef, qid: Optional[str]) -> None:
    if qid:
        g.add((subj, OWL.sameAs, WD[qid]))


def emit_people(g: Graph, people_qid_by_id, people_label_by_id, stats: Counter):
    """Always create local MIrA person entities so each person gets a file."""
    for pid, label in people_label_by_id.items():
        subj = local_uri("person", pid)
        qid = people_qid_by_id.get(pid)
        g.add((subj, wdtP("P31"), WD[Q_HUMAN]))
        add_external_same_as(g, subj, qid)
        if label:
            g.add((subj, RDFS.label, Literal(label)))
            g.add((subj, SKOS.prefLabel, Literal(label)))
        stats["person_entities"] += 1
        if qid:
            stats["person_wikidata_links"] += 1


def emit_libraries(g: Graph, lib_name_by_id, lib_qid_by_id, lib_city_by_id, lib_country_by_id, lib_coords_by_id, stats: Counter):
    """Always create local MIrA library entities so each library gets a file."""
    for lid, name in lib_name_by_id.items():
        subj = local_uri("library", lid)
        qid = lib_qid_by_id.get(lid)
        g.add((subj, wdtP("P31"), WD[Q_LIBRARY]))
        g.add((subj, wdtP("P31"), WD[Q_ORG]))
        add_external_same_as(g, subj, qid)
        g.add((subj, RDFS.label, Literal(name)))
        g.add((subj, SKOS.prefLabel, Literal(name)))
        stats["library_entities"] += 1
        if qid:
            stats["library_wikidata_links"] += 1


def emit_texts(g: Graph, text_qid_by_id, text_title_by_id, text_author_by_id, stats: Counter):
    """Always create local MIrA text entities so each text gets a file."""
    for tid, title in text_title_by_id.items():
        subj = local_uri("text", tid)  # important: text, not work
        qid = text_qid_by_id.get(tid)
        g.add((subj, wdtP("P31"), WD[Q_TEXT]))
        add_external_same_as(g, subj, qid)
        if title:
            g.add((subj, RDFS.label, Literal(title)))
            g.add((subj, SKOS.prefLabel, Literal(title)))
        stats["text_entities"] += 1
        if qid:
            stats["text_wikidata_links"] += 1


def emit_manuscripts(g: Graph,
                     mss_path: Path,
                     lib_name_by_id,
                     place_qid_by_id,
                     text_id_by_norm_title,
                     people_qid_by_id,
                     stats: Counter,
                     warnings: list[str]):
    root = parse_xml(mss_path)

    for ms in root.findall("./manuscript"):
        mid = ms.get("id") or f"unknown_{stats['manuscript_entities']+1}"
        subj = local_uri("manuscript", mid)

        g.add((subj, wdtP("P31"), WD[Q_MANUSCRIPT]))

        ident = ms.find("./identifier")
        lib_id = ident.get("libraryID") if ident is not None else None
        shelf = norm(ms.findtext("./identifier/shelfmark"))

        if shelf:
            g.add((subj, wdtP("P217"), Literal(shelf)))

        # Label
        lib_name = lib_name_by_id.get(lib_id, "") if lib_id else ""
        label = f"{lib_name} — {shelf}" if lib_name and shelf else (shelf or f"MIrA manuscript {mid}")
        g.add((subj, RDFS.label, Literal(label)))
        g.add((subj, SKOS.prefLabel, Literal(label)))

        # P195 collection/repository: point to the local MIrA library entity.
        # The library entity itself can have owl:sameAs to Wikidata when a QID exists.
        if lib_id:
            g.add((subj, wdtP("P195"), local_uri("library", lib_id)))
            stats["manuscript_p195_local_library"] += 1

        # P571 date range: include both term_post and term_ante when available.
        # If the two years are identical, RDF stores only one visible triple because
        # RDF graphs cannot contain duplicate triples. The log records this case.
        start_raw = norm(ms.findtext("./history/term_post"))
        end_raw = norm(ms.findtext("./history/term_ante"))
        start_y = extract_year(start_raw)
        end_y = extract_year(end_raw)

        if start_y and end_y:
            if start_y == end_y:
                g.add((subj, wdtP("P571"), Literal(start_y, datatype=XSD.gYear)))
                stats["manuscript_p571_single_year_start_equals_end"] += 1
            else:
                g.add((subj, wdtP("P571"), Literal(start_y, datatype=XSD.gYear)))
                g.add((subj, wdtP("P571"), Literal(end_y, datatype=XSD.gYear)))
                stats["manuscript_p571_start"] += 1
                stats["manuscript_p571_end"] += 1
        elif start_y:
            g.add((subj, wdtP("P571"), Literal(start_y, datatype=XSD.gYear)))
            stats["manuscript_p571_start_only"] += 1
            warnings.append(f"manuscript {mid}: start year exists ({start_y}) but end year is missing.")
        elif end_y:
            g.add((subj, wdtP("P571"), Literal(end_y, datatype=XSD.gYear)))
            stats["manuscript_p571_end_only"] += 1
            warnings.append(f"manuscript {mid}: end year exists ({end_y}) but start year is missing.")

        # P1071 place of creation: link directly to Wikidata QIDs.
        for place_el in ms.findall("./history/origin//place"):
            pid = place_el.get("id")
            if not pid:
                continue
            pqid = place_qid_by_id.get(pid)
            if pqid:
                g.add((subj, wdtP("P1071"), WD[pqid]))
                stats["manuscript_p1071"] += 1
            else:
                warnings.append(f"manuscript {mid}: origin place '{pid}' has no Wikidata QID; P1071 omitted.")

        # Folios: do not export as P1104 by default because folios are not pages.
        folios = int_literal(ms.findtext("./description/folios"))
        if folios is not None:
            stats["folios_seen_not_exported"] += 1
            if EXPORT_FOLIOS_AS_P1104:
                g.add((subj, wdtP("P1104"), folios))
                stats["manuscript_p1104"] += 1

        # P2048/P2049 dimensions: convert mm-like values to cm.
        height = dimension_cm_literal(ms.findtext("./description/page_h"))
        width = dimension_cm_literal(ms.findtext("./description/page_w"))
        if height is not None:
            g.add((subj, wdtP("P2048"), height))
            stats["manuscript_p2048_cm"] += 1
        if width is not None:
            g.add((subj, wdtP("P2049"), width))
            stats["manuscript_p2049_cm"] += 1

        # P953 online reference / full work available at URL where present.
        for link in ms.findall("./refs/link"):
            href = link.get("href")
            if href:
                g.add((subj, wdtP("P953"), Literal(href, datatype=XSD.anyURI)))
                stats["manuscript_p953"] += 1

        # P1574 exemplar of: link to local MIrA text entities when a title can be matched.
        contents_text = ""
        contents_el = ms.find("./description/contents")
        if contents_el is not None:
            contents_text = norm("".join(contents_el.itertext()))

        matched_text_ids = set()
        for title_el in ms.findall("./description/contents/msItem/title"):
            title = norm("".join(title_el.itertext()))
            tid = text_id_by_norm_title.get(normalize_title_key(title))
            if tid:
                matched_text_ids.add(tid)

        normalized_contents = normalize_title_key(contents_text)
        for title_key, tid in text_id_by_norm_title.items():
            if title_key and title_key in normalized_contents:
                matched_text_ids.add(tid)

        for tid in sorted(matched_text_ids):
            g.add((subj, wdtP("P1574"), local_uri("text", tid)))
            stats["manuscript_p1574"] += 1

        # People mentioned in manuscript records, only when role is explicitly present.
        for person_el in ms.findall(".//person"):
            pid = person_el.get("id") or person_el.get("personID")
            if not pid:
                continue
            role = (person_el.get("role") or "").lower()
            role_prop = {"author": "P50", "scribe": "P11603", "translator": "P655", "editor": "P98"}.get(role)
            if role_prop:
                g.add((subj, wdtP(role_prop), local_uri("person", pid)))
                stats["manuscript_people_roles"] += 1
            elif pid in people_qid_by_id:
                # Person is known but no safe manuscript role is encoded.
                stats["people_seen_without_role"] += 1

        stats["manuscript_entities"] += 1

# ---------------------------------------------------------------------
# Output helpers
# ---------------------------------------------------------------------

def triples_for_subject(source: Graph, subj: URIRef) -> Graph:
    out = Graph()
    bind_prefixes(out)
    for triple in source.triples((subj, None, None)):
        out.add(triple)
    return out


SERIALIZATIONS = [
    ("ttl", "turtle"),
    ("jsonld", "json-ld"),
    ("rdf", "xml"),
]


def serialize_graph_all_formats(g: Graph, base_path: Path) -> None:
    """
    Write one RDF graph in all requested formats:
        - Turtle (.ttl)
        - JSON-LD (.jsonld)
        - RDF/XML (.rdf)
    """
    for ext, fmt in SERIALIZATIONS:
        g.serialize(destination=str(base_path.with_suffix(f".{ext}")), format=fmt)


def write_entity_files(g: Graph, out_dir: Path) -> Counter:
    counts = Counter()
    entities_dir = out_dir / "entities"
    for folder in ["library", "manuscript", "person", "text"]:
        d = entities_dir / folder
        if d.exists():
            shutil.rmtree(d)
        d.mkdir(parents=True, exist_ok=True)

    for subj in sorted(set(g.subjects()), key=str):
        s = str(subj)
        if not s.startswith(str(MIRA)):
            continue
        rest = s[len(str(MIRA)):]
        parts = rest.split("/", 1)
        if len(parts) != 2:
            continue
        kind, key = parts
        if kind not in {"library", "manuscript", "person", "text"}:
            continue

        eg = triples_for_subject(g, subj)
        if len(eg) == 0:
            continue

        stem = output_file_stem(key)
        base_path = entities_dir / kind / stem
        serialize_graph_all_formats(eg, base_path)

        counts[f"entity_file_{kind}"] += 1
        counts[f"entity_file_{kind}_ttl"] += 1
        counts[f"entity_file_{kind}_jsonld"] += 1
        counts[f"entity_file_{kind}_rdfxml"] += 1

    return counts


def write_outputs(g: Graph, out_dir: Path, stats: Counter, warnings: list[str]) -> None:
    out_dir.mkdir(parents=True, exist_ok=True)

    combined_base = out_dir / "mira_wikidata_aligned"
    serialize_graph_all_formats(g, combined_base)

    file_counts = write_entity_files(g, out_dir)
    stats.update(file_counts)

    log_path = out_dir / "mira_wikidata_aligned_evaluation_log.txt"
    with open(log_path, "w", encoding="utf-8") as f:
        f.write("=== MIrA XML → Wikidata-aligned RDF evaluation ===\n")
        f.write(f"Date: {_dt.datetime.now().isoformat()}\n\n")
        f.write(f"Combined triples: {len(g)}\n\n")
        f.write("[Coverage]\n")
        for k in sorted(stats):
            f.write(f"- {k}: {stats[k]}\n")
        f.write("\n[Warnings]\n")
        if warnings:
            for w in warnings:
                f.write(f"- {w}\n")
        else:
            f.write("(none)\n")
        f.write("\n[Notes]\n")
        f.write("- Separate entity files are written for library, manuscript, person, and text only.\n")
        f.write("- No separate place files are written; places are linked to Wikidata QIDs where available.\n")
        f.write("- Manuscript page_h/page_w values are normalised to centimetres for P2048/P2049.\n")
        f.write("- If term_post and term_ante are different, both years are exported as P571.\n")
        f.write("- If term_post and term_ante are identical, RDF stores one P571 triple and the case is counted in the log.\n")
        if not EXPORT_FOLIOS_AS_P1104:
            f.write("- Folio counts are seen but not exported as P1104, because P1104 means number of pages.\n")
        f.write("- Local MIrA entities may use owl:sameAs to record Wikidata QIDs.\n")
        f.write("- Combined and per-entity RDF files are written in Turtle, JSON-LD, and RDF/XML.\n")
        f.write("- Numeric entity filenames are zero-padded to three digits, e.g. 001.ttl.\n")

    print("Done.")
    print(f"Combined RDF: {combined_base}.ttl / .jsonld / .rdf")
    print(f"Entity folder: {out_dir / 'entities'}")
    print(f"Log: {log_path}")

# ---------------------------------------------------------------------
# Build graph and CLI
# ---------------------------------------------------------------------

def build_graph(args) -> tuple[Graph, Counter, list[str]]:
    stats = Counter()
    warnings = []

    people_qid_by_id, people_label_by_id = load_people(Path(args.people))
    lib_name_by_id, lib_qid_by_id, lib_city_by_id, lib_country_by_id, lib_coords_by_id = load_libraries(Path(args.libraries))
    text_qid_by_id, text_title_by_id, text_author_by_id, text_qid_by_norm_title, text_id_by_norm_title = load_texts(Path(args.texts))
    place_qid_by_id, place_name_by_id = load_places(Path(args.places))

    g = Graph()
    bind_prefixes(g)

    emit_people(g, people_qid_by_id, people_label_by_id, stats)
    emit_libraries(g, lib_name_by_id, lib_qid_by_id, lib_city_by_id, lib_country_by_id, lib_coords_by_id, stats)
    emit_texts(g, text_qid_by_id, text_title_by_id, text_author_by_id, stats)
    emit_manuscripts(
        g=g,
        mss_path=Path(args.mss),
        lib_name_by_id=lib_name_by_id,
        place_qid_by_id=place_qid_by_id,
        text_id_by_norm_title=text_id_by_norm_title,
        people_qid_by_id=people_qid_by_id,
        stats=stats,
        warnings=warnings,
    )

    return g, stats, warnings


def parse_args():
    ap = argparse.ArgumentParser(description="MIrA XML → Wikidata-aligned RDF")
    ap.add_argument("--mss", default="data/mss_mira/compiled/mss_compiled.xml", help="Input mss_compiled.xml")
    ap.add_argument("--people", default="data/other/people.xml", help="Input people.xml")
    ap.add_argument("--places", default="data/other/places.xml", help="Input places.xml")
    ap.add_argument("--texts", default="data/other/texts.xml", help="Input texts.xml")
    ap.add_argument("--libraries", default="data/other/libraries.xml", help="Input libraries.xml")
    ap.add_argument("--outdir", default="data/rdf", help="Output folder")
    return ap.parse_args()


def main():
    args = parse_args()
    required = [args.mss, args.people, args.places, args.texts, args.libraries]
    missing = [p for p in required if not Path(p).is_file()]
    if missing:
        raise SystemExit("Missing input file(s):\n" + "\n".join(f"- {m}" for m in missing))
    g, stats, warnings = build_graph(args)
    write_outputs(g, Path(args.outdir), stats, warnings)


if __name__ == "__main__":
    main()
