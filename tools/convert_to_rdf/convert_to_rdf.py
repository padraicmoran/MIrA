#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
MIrA TEI/XML → Wikidata-aligned RDF (Turtle)

This script converts MIrA TEI-encoded manuscript descriptions into RDF (Turtle),
aligned with the Wikidata WikiProject Manuscripts data model where data exists.

Repository context:
    This script is intended to be added to the MIrA GitHub repository under:
        /tools/convert_to_rdf/
    It reads MIrA XML source data directly from:
        /data/
        /data/other/
    and writes RDF output to:
        /data/rdf/
    in accordance with MIrA workflows and deployment conventions.

Input files (default paths, relative to the repo root):
    - data/mss_compiled.xml
    - data/other/people.xml
    - data/other/places.xml
    - data/other/texts.xml
    - data/other/libraries.xml

Output:
    - data/rdf/mira_wikidata_aligned.ttl

Design principles:
    - Reuse official Wikidata entities (wd:Q…) and direct properties (wdt:P…).
    - No local placeholder entities where a Wikidata QID exists.
    - If a Wikidata QID cannot be resolved, omit that triple and do not guess.
    - Every manuscript is still represented as a MIrA URI (mira:manuscript/…).
    - Output should be clean, ingestible and suitable for SPARQL querying.

Dependencies:
    - Python 3.11+
    - lxml          (XML parsing)
    - rdflib        (RDF graph + Turtle serialisation)

Install dependencies (from the MIrA repo root), e.g. in a virtual environment:

    pip install lxml rdflib

Usage (from the MIrA repo root):

    python tools/convert_to_rdf/convert_to_rdf.py

You can override file paths with command-line arguments, e.g.:

    python tools/convert_to_rdf/convert_to_rdf.py --out data/rdf/custom.ttl

Authorship & Credits:
    Script written by: Dr. Sudhansu Bala Das
    Email: baladas.sudhansu@gmail.com
    Date: 8 January 2026
"""

import re
import uuid
import argparse
from pathlib import Path

from lxml import etree
from rdflib import Graph, Namespace, URIRef, Literal
from rdflib.namespace import RDF, RDFS, SKOS, XSD

# ----------------------------------------------------------------------
# Repository-relative paths
# ----------------------------------------------------------------------

# This script is expected to live at: tools/convert_to_rdf/convert_to_rdf.py
# So the repo root is two levels up from this file's directory.
REPO_ROOT = Path(__file__).resolve().parents[2]  # .../MIrA/
DATA_DIR = REPO_ROOT / "data"
OTHER_DIR = DATA_DIR / "other"
RDF_DIR = DATA_DIR / "rdf"

# ----------------------------------------------------------------------
# Namespaces (prefixes used to construct Wikidata-aligned RDF triples)
# ----------------------------------------------------------------------

WD = Namespace("http://www.wikidata.org/entity/")
WDT = Namespace("http://www.wikidata.org/prop/direct/")
P = Namespace("http://www.wikidata.org/prop/")
PS = Namespace("http://www.wikidata.org/prop/statement/")
PQ = Namespace("http://www.wikidata.org/prop/qualifier/")
WB = Namespace("http://wikiba.se/ontology#")
MIRA = Namespace("https://mira.ie/entity/")

# ----------------------------------------------------------------------
# Core Wikidata QIDs
# ----------------------------------------------------------------------

Q_MANUSCRIPT = "Q87167"
Q_HUMAN = "Q5"
Q_LIBRARY = "Q7075"
Q_ORG = "Q43229"
Q_WORK = "Q7725634"
Q_PLACE = "Q618123"


# ----------------------------------------------------------------------
# Utility functions
# ----------------------------------------------------------------------

def norm(s: str) -> str:
    """Normalise whitespace in a string."""
    return re.sub(r"\s+", " ", (s or "").strip())


def safe_id(s: str) -> str:
    """Turn an arbitrary string into a URI-safe identifier part."""
    return re.sub(r"[^A-Za-z0-9._-]+", "_", s or "unknown")


def local_uri(kind: str, key: str) -> URIRef:
    """Construct a MIrA-local URI for an entity (person/place/library/manuscript/etc.)."""
    return URIRef(f"{MIRA}{kind}/{safe_id(key)}")


def extract_qid(text: str | None) -> str | None:
    """Extract a Wikidata QID from text, if present."""
    if not text:
        return None
    m = re.search(r"\bQ\d+\b", text)
    return m.group(0) if m else None


def normalize_title_key(s: str) -> str:
    """Normalise titles for matching (lowercase, strip punctuation, normalise spaces)."""
    s = norm(s).lower()
    s = re.sub(r"[’'`]", "", s)
    s = re.sub(r"[^a-z0-9\s]", " ", s)
    s = re.sub(r"\s+", " ", s).strip()
    return s


def extract_gYear(text: str | None) -> str | None:
    """
    Extract a valid xsd:gYear from free text.

    - Prefer 4+ digit years (e.g. '0845', '1200', '2001').
    - If only 3-digit years are present, pad to 4 digits (e.g. '850' -> '0850').
    """
    if not text:
        return None

    # Prefer 4+ digits first
    m4 = re.search(r"\b(\d{4,})\b", text)
    if m4:
        return m4.group(1)[:4]  # keep first 4 digits

    # Else allow 3 digits and pad
    m3 = re.search(r"\b(\d{3})\b", text)
    if m3:
        return m3.group(1).zfill(4)  # 850 -> 0850

    # Anything else is unsafe for gYear
    return None


# ----------------------------------------------------------------------
# Load authority data
# ----------------------------------------------------------------------

def load_people(path: str):
    """Load people authority data (people.xml)."""
    root = etree.parse(path).getroot()
    qid_by_id = {}
    label_by_id = {}
    for p in root.findall("./person"):
        pid = p.get("id") or ""
        label = norm(" ".join(filter(None, [
            p.findtext("firstNames"),
            p.findtext("surname")
        ])))
        qid = extract_qid(p.findtext("xref[@type='wikidata']"))
        qid_by_id[pid] = qid
        if label:
            label_by_id[pid] = label
    return qid_by_id, label_by_id


def load_places(path: str):
    """Load places authority data (places.xml)."""
    root = etree.parse(path).getroot()
    qid_by_id = {}
    names_by_id = {}

    def walk(el):
        pid = el.get("id") or ""
        qid = extract_qid(el.findtext("xref[@type='wikidata']"))
        names = [norm(n.text) for n in el.findall("name") if n.text and norm(n.text)]
        if pid:
            qid_by_id[pid] = qid
            names_by_id[pid] = names
        for ch in el.findall("place"):
            walk(ch)

    for p in root.findall("./place"):
        walk(p)

    return qid_by_id, names_by_id, root


def load_texts(path: str):
    """Load texts/works authority data (texts.xml)."""
    root = etree.parse(path).getroot()
    qid_by_id = {}
    title_by_id = {}
    qid_by_norm_title = {}
    for t in root.findall("./text"):
        tid = t.get("id") or ""
        title = norm(t.findtext("title"))
        qid = extract_qid(t.findtext("xref[@type='wikidata']"))
        qid_by_id[tid] = qid
        if title:
            title_by_id[tid] = title
            if qid:
                qid_by_norm_title[normalize_title_key(title)] = qid
    return qid_by_id, title_by_id, qid_by_norm_title


def load_libraries(path: str):
    """Load libraries authority data (libraries.xml)."""
    root = etree.parse(path).getroot()
    name_by_id = {}
    qid_by_id = {}
    for lib in root.findall("./library"):
        lid = lib.get("id") or ""
        name = norm(lib.findtext("name")) or lid
        qid = extract_qid(lib.findtext("xref[@type='wikidata']"))  # future-proof
        if lid:
            name_by_id[lid] = name
            qid_by_id[lid] = qid
    return name_by_id, qid_by_id


# ----------------------------------------------------------------------
# Main conversion
# ----------------------------------------------------------------------

def main():
    # ------------------------------------------------------------------
    # Argument parsing with sensible defaults for the MIrA repo
    # ------------------------------------------------------------------
    ap = argparse.ArgumentParser(
        description="Convert MIrA TEI/XML to Wikidata-aligned RDF (Turtle)."
    )
    ap.add_argument(
        "--mss",
        default=str(DATA_DIR / "mss_compiled.xml"),
        help="Path to compiled manuscripts XML (default: data/mss_compiled.xml)",
    )
    ap.add_argument(
        "--people",
        default=str(OTHER_DIR / "people.xml"),
        help="Path to people authority XML (default: data/other/people.xml)",
    )
    ap.add_argument(
        "--places",
        default=str(OTHER_DIR / "places.xml"),
        help="Path to places authority XML (default: data/other/places.xml)",
    )
    ap.add_argument(
        "--texts",
        default=str(OTHER_DIR / "texts.xml"),
        help="Path to texts authority XML (default: data/other/texts.xml)",
    )
    ap.add_argument(
        "--libraries",
        default=str(OTHER_DIR / "libraries.xml"),
        help="Path to libraries authority XML (default: data/other/libraries.xml)",
    )
    ap.add_argument(
        "--out",
        default=str(RDF_DIR / "mira_wikidata_aligned.ttl"),
        help="Output Turtle file (default: data/rdf/mira_wikidata_aligned.ttl)",
    )
    args = ap.parse_args()

    # Ensure RDF output directory exists
    RDF_DIR.mkdir(parents=True, exist_ok=True)

    # Load authority data
    people_qid_by_id, people_label_by_id = load_people(args.people)
    place_qid_by_id, place_names_by_id, places_root = load_places(args.places)
    text_qid_by_id, text_title_by_id, text_qid_by_norm_title = load_texts(args.texts)
    lib_name_by_id, lib_qid_by_id = load_libraries(args.libraries)

    # Initialise RDF graph and bind prefixes
    g = Graph()
    for pfx, ns in [
        ("wd", WD), ("wdt", WDT), ("p", P), ("ps", PS),
        ("pq", PQ), ("wikibase", WB), ("rdfs", RDFS),
        ("skos", SKOS), ("mira", MIRA)
    ]:
        g.bind(pfx, ns)

    # --------------------------------------------------------------
    # Authority entities
    # --------------------------------------------------------------

    # People: type and label
    for pid, qid in people_qid_by_id.items():
        label = people_label_by_id.get(pid)
        subj = WD[qid] if qid else local_uri("person", pid)
        g.add((subj, RDF.type, WD[Q_HUMAN]))
        if label:
            g.add((subj, RDFS.label, Literal(label)))
            g.add((subj, SKOS.prefLabel, Literal(label)))

    # Places: type and labels
    def emit_place(el):
        pid = el.get("id") or ""
        qid = place_qid_by_id.get(pid)
        names = [norm(n.text) for n in el.findall("name") if n.text and norm(n.text)]
        key = pid or (names[0] if names else f"place_{uuid.uuid4().hex[:8]}")
        subj = WD[qid] if qid else local_uri("place", key)

        g.add((subj, RDF.type, WD[Q_PLACE]))
        if names:
            g.add((subj, RDFS.label, Literal(names[0])))
            g.add((subj, SKOS.prefLabel, Literal(names[0])))
            for n in names[1:]:
                g.add((subj, SKOS.altLabel, Literal(n)))

        for ch in el.findall("place"):
            emit_place(ch)

    for p in places_root.findall("./place"):
        emit_place(p)

    # Works/texts: type and label
    for tid, qid in text_qid_by_id.items():
        title = text_title_by_id.get(tid)
        subj = WD[qid] if qid else local_uri("work", tid)
        g.add((subj, RDF.type, WD[Q_WORK]))
        if title:
            g.add((subj, RDFS.label, Literal(title)))
            g.add((subj, SKOS.prefLabel, Literal(title)))

    # Libraries: type and label (and QID if available)
    for lid, name in lib_name_by_id.items():
        qid = lib_qid_by_id.get(lid)
        subj = WD[qid] if qid else local_uri("library", lid)
        g.add((subj, RDF.type, WD[Q_LIBRARY]))
        g.add((subj, RDF.type, WD[Q_ORG]))
        g.add((subj, RDFS.label, Literal(name)))
        g.add((subj, SKOS.prefLabel, Literal(name)))

    # --------------------------------------------------------------
    # Manuscripts
    # --------------------------------------------------------------
    msroot = etree.parse(args.mss).getroot()

    for ms in msroot.findall("./manuscript"):
        mid = ms.get("id") or f"ms_{uuid.uuid4().hex[:8]}"
        subj = local_uri("manuscript", mid)

        # P31 = instance of → manuscript
        g.add((subj, WDT.P31, WD[Q_MANUSCRIPT]))

        ident = ms.find("./identifier")

        # P217 = shelfmark / inventory number
        shelf = norm(ms.findtext("./identifier/shelfmark"))
        if shelf:
            g.add((subj, WDT.P217, Literal(shelf)))

        # P195 = collection (library)
        lib_id = ident.get("libraryID") if ident is not None else None
        if lib_id:
            lib_qid = lib_qid_by_id.get(lib_id)
            if lib_qid:
                g.add((subj, WDT.P195, WD[lib_qid]))
            else:
                g.add((subj, WDT.P195, local_uri("library", lib_id)))

        # rdfs:label / skos:prefLabel for the manuscript
        lib_name = lib_name_by_id.get(lib_id) if lib_id else None
        label = shelf or f"MIRA manuscript {mid}"
        if shelf and lib_name:
            label = f"{lib_name} — {shelf}"
        g.add((subj, RDFS.label, Literal(label)))
        g.add((subj, SKOS.prefLabel, Literal(label)))

        # P571 = inception (date of origin as gYear)
        year_raw = ms.findtext("./history/term_post") or ms.findtext("./history/term_ante")
        gyear = extract_gYear(year_raw)
        if gyear:
            g.add((subj, WDT.P571, Literal(gyear, datatype=XSD.gYear)))

        # P1071 = location of creation (origin place)
        place_el = ms.find("./history/origin/place")
        if place_el is not None:
            pid = place_el.get("id")
            if pid and place_qid_by_id.get(pid):
                g.add((subj, WDT.P1071, WD[place_qid_by_id[pid]]))
            elif pid:
                g.add((subj, WDT.P1071, local_uri("place", pid)))

        # P1104 = number of pages/folios
        fol_raw = norm(ms.findtext("./description/folios"))
        m = re.search(r"\d+", fol_raw) if fol_raw else None
        if m:
            g.add((subj, WDT.P1104, Literal(int(m.group(0)), datatype=XSD.integer)))

        # P2048 / P2049 = physical dimensions (height/width)
        h_raw = norm(ms.findtext("./description/page_h"))
        w_raw = norm(ms.findtext("./description/page_w"))
        mh = re.search(r"\d+(?:\.\d+)?", h_raw) if h_raw else None
        mw = re.search(r"\d+(?:\.\d+)?", w_raw) if w_raw else None
        if mh:
            g.add((subj, WDT.P2048, Literal(mh.group(0), datatype=XSD.decimal)))
        if mw:
            g.add((subj, WDT.P2049, Literal(mw.group(0), datatype=XSD.decimal)))

        # P953 = full work available at URL (if present)
        link = ms.find("./refs/link")
        if link is not None and link.get("href"):
            g.add((subj, WDT.P953, Literal(link.get("href"), datatype=XSD.anyURI)))

        # P1574 = exemplar of (link to work/text via normalised title)
        for title_el in ms.findall("./description/contents/msItem/title"):
            t = norm("".join(title_el.itertext()))
            if not t:
                continue
            qid = text_qid_by_norm_title.get(normalize_title_key(t))
            if qid:
                g.add((subj, WDT.P1574, WD[qid]))

    # Serialise RDF graph to Turtle
    g.serialize(destination=args.out, format="turtle")
    print(f"Written RDF to {args.out}")


if __name__ == "__main__":
    main()
