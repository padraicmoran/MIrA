"""
Model 2 — MIrA weighted network metrics 

Code written by: Dr Sudhansu Bala Das
Email: baladas.sudhansu@gmail.com

Purpose
-------
This script analyses MIrA Data Model 2, where manuscripts are represented as
directed place-to-place / place-to-library relationships.


1) Model 2 can contain repeated source-target pairs, because several manuscripts
   may connect the same two places/libraries. A simple NetworkX DiGraph keeps
   only one edge per source-target pair. Therefore this script first aggregates
   repeated/parallel edges by summing their weights.

2) In this project, edge weight means relationship strength/certainty.

3) Metrics use weights as follows:
   - In-strength, Out-strength, Total strength: sum of edge weights.
   - PageRank: uses edge weight directly.
   - Eigenvector centrality: calculated on the undirected weighted graph.
   - Betweenness and closeness: use distance = 1 / weight, because NetworkX
     treats shortest-path edge attributes as distance/cost.
   - Degree centrality: standard count-based centrality. It is kept as a
     structural connectivity measure. The weight-aware equivalent is
     Total strength.

4) Percentile tables are calculated separately:
   - place percentiles are calculated among PLACE nodes only;
   - library percentiles are calculated among LIBRARY nodes only.

Expected input files
--------------------
Place this script in the same folder as:
- nodes_places.csv
- nodes_libraries.csv
- edges_mss.csv
- edges_places-hierarchy.csv

Outputs
-------
Folder: output_model2_tables/

- model2_place_metrics.csv
- model2_place_metrics_percentiles.csv
- model2_library_metrics.csv
- model2_library_metrics_percentiles.csv

Audit tables:
- audit/model2_all_nodes_audit.csv
- audit/model2_aggregated_edges_audit.csv
"""

import os
import numpy as np
import pandas as pd
import networkx as nx


# ============================================================
# SETTINGS
# ============================================================

OUTPUT_DIR = "output_model2_tables"
AUDIT_DIR = os.path.join(OUTPUT_DIR, "audit")
os.makedirs(OUTPUT_DIR, exist_ok=True)
os.makedirs(AUDIT_DIR, exist_ok=True)

NODES_PLACES_FILE = "nodes_places.csv"
NODES_LIBRARIES_FILE = "nodes_libraries.csv"
EDGES_MSS_FILE = "edges_mss.csv"
EDGES_HIER_FILE = "edges_places-hierarchy.csv"

PAPER_METRICS = [
    "In-strength",
    "Out-strength",
    "Total strength",
    "Degree centrality",
    "Betweenness centrality",
    "Closeness centrality",
    "Eigenvector centrality",
    "PageRank",
]


# ============================================================
# HELPERS
# ============================================================

def require_file(path: str) -> None:
    if not os.path.exists(path):
        raise FileNotFoundError(f"Required input file not found: {path}")


def normalise_nodes(df: pd.DataFrame, default_type: str) -> pd.DataFrame:
    df = df.copy()

    if "node_id" in df.columns:
        df = df.rename(columns={"node_id": "id"})

    if "id" not in df.columns:
        raise ValueError(f"Node file must contain 'node_id' or 'id'. Found: {list(df.columns)}")

    if "display_text" not in df.columns:
        df["display_text"] = df["id"]

    if "node_type" not in df.columns:
        df["node_type"] = default_type

    df["id"] = df["id"].astype(str)
    df["display_text"] = df["display_text"].fillna(df["id"]).astype(str)
    df["node_type"] = df["node_type"].fillna(default_type).astype(str)

    return df


def normalise_edges(df: pd.DataFrame, default_type: str) -> pd.DataFrame:
    """
    Normalise MIrA edge CSVs to source, target, weight, type.

    Supported formats:
    - node_id_from / node_id_to
    - source / target
    - parent_id / child_id
    """
    df = df.copy()

    if "node_id_from" in df.columns and "node_id_to" in df.columns:
        df = df.rename(columns={"node_id_from": "source", "node_id_to": "target"})
    elif "parent_id" in df.columns and "child_id" in df.columns:
        df = df.rename(columns={"parent_id": "source", "child_id": "target"})
    elif "source" in df.columns and "target" in df.columns:
        pass
    else:
        raise ValueError(
            "Edges file missing expected columns. "
            f"Expected node_id_from/node_id_to, parent_id/child_id, or source/target. "
            f"Found columns: {list(df.columns)}"
        )

    df["source"] = df["source"].astype(str)
    df["target"] = df["target"].astype(str)

    if "weight" not in df.columns:
        df["weight"] = 1.0

    df["weight"] = pd.to_numeric(df["weight"], errors="coerce").fillna(1.0).astype(float)
    df.loc[df["weight"] <= 0, "weight"] = 1e-9

    if "type" not in df.columns:
        df["type"] = default_type
    df["type"] = df["type"].fillna(default_type).astype(str)

    return df[["source", "target", "weight", "type"]]


def aggregate_parallel_edges(edges_df: pd.DataFrame) -> pd.DataFrame:
    """
    Important for Model 2:
    Multiple manuscripts may connect the same source-target pair.
    DiGraph cannot store true parallel edges, so we aggregate them by summing
    weights before building the graph.
    """
    aggregated = (
        edges_df
        .groupby(["source", "target"], as_index=False)
        .agg(
            weight=("weight", "sum"),
            edge_count=("weight", "size"),
            type=("type", lambda x: ";".join(sorted(set(map(str, x)))))
        )
    )

    # Internal path cost used only for shortest-path metrics.
    aggregated["distance"] = 1.0 / aggregated["weight"]
    return aggregated


def percentile_rank(series: pd.Series) -> pd.Series:
    """
    Convert one metric column into percentile values from 0 to 1.
    """
    s = pd.to_numeric(series, errors="coerce").fillna(0.0)

    if s.nunique() <= 1:
        return pd.Series([0.0] * len(s), index=s.index)

    return s.rank(method="average", pct=True)


def safe_pagerank(G: nx.DiGraph) -> dict:
    try:
        return nx.pagerank(G, weight="weight", max_iter=5000)
    except Exception:
        return {n: 0.0 for n in G.nodes()}


def safe_eigenvector_centrality(G: nx.DiGraph) -> dict:
    """
    For sparse directed historical graphs, directed eigenvector centrality can
    become unstable or uninformative. We therefore calculate it on the
    undirected weighted graph, measuring whether a node is connected to other
    influential nodes.
    """
    UG = G.to_undirected()

    try:
        return nx.eigenvector_centrality(
            UG,
            weight="weight",
            max_iter=5000,
            tol=1e-06
        )
    except Exception:
        return {n: 0.0 for n in G.nodes()}


# ============================================================
# BUILD GRAPH
# ============================================================

def build_graph() -> nx.DiGraph:
    for f in [NODES_PLACES_FILE, NODES_LIBRARIES_FILE, EDGES_MSS_FILE, EDGES_HIER_FILE]:
        require_file(f)

    nodes_places = normalise_nodes(pd.read_csv(NODES_PLACES_FILE), "place")
    nodes_libraries = normalise_nodes(pd.read_csv(NODES_LIBRARIES_FILE), "library")

    edges_mss = normalise_edges(pd.read_csv(EDGES_MSS_FILE), "manuscript")
    edges_hier = normalise_edges(pd.read_csv(EDGES_HIER_FILE), "hierarchy")

    nodes_df = pd.concat([nodes_places, nodes_libraries], ignore_index=True)
    edges_df = pd.concat([edges_mss, edges_hier], ignore_index=True)

    aggregated_edges = aggregate_parallel_edges(edges_df)
    aggregated_edges.to_csv(
        os.path.join(AUDIT_DIR, "model2_aggregated_edges_audit.csv"),
        index=False
    )

    G = nx.DiGraph()

    for _, row in nodes_df.iterrows():
        node_id = row["id"]
        G.add_node(
            node_id,
            label=row.get("display_text", node_id),
            type=row.get("node_type", "unknown"),
            lat=row.get("lat", np.nan),
            lng=row.get("lng", np.nan),
        )

    for _, row in aggregated_edges.iterrows():
        source = row["source"]
        target = row["target"]

        if source not in G:
            G.add_node(source, label=source, type="unknown")
        if target not in G:
            G.add_node(target, label=target, type="unknown")

        G.add_edge(
            source,
            target,
            weight=float(row["weight"]),
            distance=float(row["distance"]),
            edge_count=int(row["edge_count"]),
            type=row["type"],
        )

    G.remove_edges_from(nx.selfloop_edges(G))
    return G


# ============================================================
# METRICS
# ============================================================

def compute_metrics(G: nx.DiGraph) -> pd.DataFrame:
    print("Computing corrected weighted Model 2 metrics...")

    in_degree_count = dict(G.in_degree())
    out_degree_count = dict(G.out_degree())

    in_strength = dict(G.in_degree(weight="weight"))
    out_strength = dict(G.out_degree(weight="weight"))
    total_strength = {
        n: in_strength.get(n, 0.0) + out_strength.get(n, 0.0)
        for n in G.nodes()
    }

    degree_centrality = nx.degree_centrality(G)

    # Shortest-path metrics use distance = 1 / weight.
    betweenness = nx.betweenness_centrality(
        G,
        weight="distance",
        normalized=True
    )

    # Undirected closeness gives overall accessibility in a sparse network.
    UG = G.to_undirected()
    closeness = nx.closeness_centrality(
        UG,
        distance="distance"
    )

    eigenvector = safe_eigenvector_centrality(G)
    pagerank = safe_pagerank(G)

    df = pd.DataFrame({
        "Node": list(G.nodes()),
        "Label": [G.nodes[n].get("label", n) for n in G.nodes()],
        "Type": [G.nodes[n].get("type", "unknown") for n in G.nodes()],

        # Audit-only count metrics
        "In-degree count": [in_degree_count[n] for n in G.nodes()],
        "Out-degree count": [out_degree_count[n] for n in G.nodes()],

        # Paper metrics
        "In-strength": [in_strength[n] for n in G.nodes()],
        "Out-strength": [out_strength[n] for n in G.nodes()],
        "Total strength": [total_strength[n] for n in G.nodes()],
        "Degree centrality": [degree_centrality[n] for n in G.nodes()],
        "Betweenness centrality": [betweenness[n] for n in G.nodes()],
        "Closeness centrality": [closeness[n] for n in G.nodes()],
        "Eigenvector centrality": [eigenvector[n] for n in G.nodes()],
        "PageRank": [pagerank[n] for n in G.nodes()],
    })

    return df


def make_percentiles_within_subset(df: pd.DataFrame) -> pd.DataFrame:
    """
    Percentiles are calculated within the given subset only.
    For example, place percentiles are calculated among places only.
    """
    out = df[["Node", "Label", "Type"]].copy()

    for metric in PAPER_METRICS:
        out[metric] = percentile_rank(df[metric])

    out["Mean percentile"] = out[PAPER_METRICS].mean(axis=1)
    out = out.sort_values("Mean percentile", ascending=False).reset_index(drop=True)

    return out


# ============================================================
# SAVE TABLES
# ============================================================

def save_tables(df_all: pd.DataFrame) -> None:
    df_all.to_csv(os.path.join(AUDIT_DIR, "model2_all_nodes_audit.csv"), index=False)

    df_places = df_all[df_all["Type"].str.lower().eq("place")].copy()
    df_libraries = df_all[df_all["Type"].str.lower().isin(["library", "libraries"])].copy()

    place_percentiles = make_percentiles_within_subset(df_places)
    library_percentiles = make_percentiles_within_subset(df_libraries)

    # Paper-ready raw metric tables
    df_places[["Node", "Label", "Type"] + PAPER_METRICS].to_csv(
        os.path.join(OUTPUT_DIR, "model2_place_metrics.csv"),
        index=False
    )
    df_libraries[["Node", "Label", "Type"] + PAPER_METRICS].to_csv(
        os.path.join(OUTPUT_DIR, "model2_library_metrics.csv"),
        index=False
    )

    # Paper-ready percentile tables
    place_percentiles.to_csv(
        os.path.join(OUTPUT_DIR, "model2_place_metrics_percentiles.csv"),
        index=False
    )
    library_percentiles.to_csv(
        os.path.join(OUTPUT_DIR, "model2_library_metrics_percentiles.csv"),
        index=False
    )

    print(f"Saved paper-ready tables in: {OUTPUT_DIR}")
    print(f"Saved audit tables in: {AUDIT_DIR}")


# ============================================================
# MAIN
# ============================================================

def main() -> None:
    print("Building corrected Model 2 weighted graph...")
    G = build_graph()

    print(f"Graph ready: {G.number_of_nodes()} nodes, {G.number_of_edges()} aggregated edges")

    df_all = compute_metrics(G)
    save_tables(df_all)

    print("\nDone.")
    print("Output folder:", os.path.abspath(OUTPUT_DIR))


if __name__ == "__main__":
    main()
