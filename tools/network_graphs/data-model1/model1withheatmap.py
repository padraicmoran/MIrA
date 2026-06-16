"""
Model 1 — Network metrics for MIrA (Places + Manuscripts)

Code written by: Dr Sudhansu Bala Das
Email: baladas.sudhansu@gmail.com

Purpose
-------
This script analyses MIrA Data Model 1, where manuscripts are represented as
nodes and are connected to places of origin/provenance. Place hierarchy edges
are also included, so the model captures both manuscript-place associations
and broader geographic structure.

Important note on edge weights
------------------------------
In this project, edge weight means relationship strength/certainty.

- For in-strength, out-strength, total strength, PageRank, and eigenvector
  centrality, the edge weight is used directly.
- Degree centrality is kept as the standard graph-theoretic measure of how
  many direct connections a node has. It is count-based by definition. The
  corresponding weight-aware activity measure is Total strength.
- For shortest-path metrics such as betweenness and closeness, NetworkX treats
  edge values as distance/cost. Therefore this script creates an internal
  distance value:

      distance = 1 / weight

  This means stronger manuscript links become shorter paths.

Expected input files
--------------------
Place this script in the same folder as:

- nodes_places.csv
- nodes_mss.csv
- edges_mss.csv
- edges_places-hierarchy.csv

Main outputs
------------
This version writes both tables and heatmaps.

Paper-ready:
- model1_place_metrics.csv
- model1_place_metrics_percentiles.csv
- model1_manuscript_metrics.csv
- model1_manuscript_metrics_percentiles.csv

Audit:
- audit/model1_all_nodes_audit.csv
"""

import os
import numpy as np
import pandas as pd
import networkx as nx


# ============================================================
# SETTINGS
# ============================================================

OUTPUT_DIR = "output_model1_final"
AUDIT_DIR = os.path.join(OUTPUT_DIR, "audit")
os.makedirs(OUTPUT_DIR, exist_ok=True)
os.makedirs(AUDIT_DIR, exist_ok=True)

NODES_PLACES_FILE = "nodes_places.csv"
NODES_MSS_FILE = "nodes_mss.csv"
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
# HELPER FUNCTIONS
# ============================================================

def require_file(path: str) -> None:
    if not os.path.exists(path):
        raise FileNotFoundError(f"Required input file not found: {path}")


def normalise_nodes(df: pd.DataFrame, node_type_default: str) -> pd.DataFrame:
    df = df.copy()

    if "node_id" in df.columns:
        df = df.rename(columns={"node_id": "id"})

    if "id" not in df.columns:
        raise ValueError(f"Node file must contain 'node_id' or 'id'. Found: {list(df.columns)}")

    if "display_text" not in df.columns:
        df["display_text"] = df["id"]

    if "node_type" not in df.columns:
        df["node_type"] = node_type_default

    df["id"] = df["id"].astype(str)
    df["display_text"] = df["display_text"].fillna(df["id"]).astype(str)
    df["node_type"] = df["node_type"].fillna(node_type_default).astype(str)

    return df


def normalise_edges(df: pd.DataFrame, default_type: str) -> pd.DataFrame:
    """
    Normalise MIrA edge CSV formats to source, target, weight, type.

    Supports:
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
            f"Expected node_id_from/node_id_to, parent_id/child_id, or source/target. Found: {list(df.columns)}"
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
    DiGraph stores one edge per source-target pair. If the CSV contains parallel
    relationships between the same two nodes, their weights are summed.
    """
    grouped = (
        edges_df
        .groupby(["source", "target"], as_index=False)
        .agg(
            weight=("weight", "sum"),
            type=("type", lambda x: ";".join(sorted(set(map(str, x)))))
        )
    )
    grouped["distance"] = 1.0 / grouped["weight"]
    return grouped


def percentile_rank(series: pd.Series) -> pd.Series:
    """
    Convert a metric column into percentile values from 0 to 1.
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
    Eigenvector centrality is calculated on the undirected weighted graph. This
    provides a stable measure of whether a node is connected to other influential
    nodes in this sparse manuscript-place network.
    """
    UG = G.to_undirected()
    try:
        return nx.eigenvector_centrality(UG, weight="weight", max_iter=5000, tol=1e-06)
    except Exception:
        return {n: 0.0 for n in G.nodes()}


def build_model1_graph() -> nx.DiGraph:
    for f in [NODES_PLACES_FILE, NODES_MSS_FILE, EDGES_MSS_FILE, EDGES_HIER_FILE]:
        require_file(f)

    nodes_places = normalise_nodes(pd.read_csv(NODES_PLACES_FILE), "place")
    nodes_mss = normalise_nodes(pd.read_csv(NODES_MSS_FILE), "manuscript")

    edges_mss = normalise_edges(pd.read_csv(EDGES_MSS_FILE), "manuscript")
    edges_hier = normalise_edges(pd.read_csv(EDGES_HIER_FILE), "hierarchy")

    nodes_df = pd.concat([nodes_places, nodes_mss], ignore_index=True)
    edges_df = pd.concat([edges_mss, edges_hier], ignore_index=True)
    edges_df = aggregate_parallel_edges(edges_df)

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

    for _, row in edges_df.iterrows():
        s = row["source"]
        t = row["target"]

        if s not in G:
            G.add_node(s, label=s, type="unknown")
        if t not in G:
            G.add_node(t, label=t, type="unknown")

        G.add_edge(
            s,
            t,
            weight=float(row["weight"]),
            distance=float(row["distance"]),
            type=row.get("type", "unknown"),
        )

    G.remove_edges_from(nx.selfloop_edges(G))
    return G


def compute_metrics(G: nx.DiGraph) -> pd.DataFrame:
    print("Computing Model 1 metrics...")

    in_degree_count = dict(G.in_degree())
    out_degree_count = dict(G.out_degree())

    in_strength = dict(G.in_degree(weight="weight"))
    out_strength = dict(G.out_degree(weight="weight"))
    total_strength = {
        n: in_strength.get(n, 0.0) + out_strength.get(n, 0.0)
        for n in G.nodes()
    }

    degree_centrality = nx.degree_centrality(G)

    # Shortest-path centralities use internal distance = 1 / weight.
    betweenness = nx.betweenness_centrality(G, weight="distance", normalized=True)

    # Undirected closeness measures overall accessibility in the sparse graph.
    UG = G.to_undirected()
    closeness = nx.closeness_centrality(UG, distance="distance")

    eigenvector = safe_eigenvector_centrality(G)
    pagerank = safe_pagerank(G)

    df = pd.DataFrame({
        "Node": list(G.nodes()),
        "Label": [G.nodes[n].get("label", n) for n in G.nodes()],
        "Type": [G.nodes[n].get("type", "unknown") for n in G.nodes()],

        # Audit-only count measures
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

    df["Mean percentile"] = 0.0
    return df


def make_percentile_table(df: pd.DataFrame) -> pd.DataFrame:
    out = df[["Node", "Label", "Type"]].copy()
    for metric in PAPER_METRICS:
        out[metric] = percentile_rank(df[metric])

    out["Mean percentile"] = out[PAPER_METRICS].mean(axis=1)
    out = out.sort_values("Mean percentile", ascending=False).reset_index(drop=True)
    return out


def save_tables(df_all: pd.DataFrame, df_percentiles: pd.DataFrame) -> None:
    df_all.to_csv(os.path.join(AUDIT_DIR, "model1_all_nodes_audit.csv"), index=False)

    df_place = df_all[df_all["Type"].str.lower().eq("place")].copy()
    df_mss = df_all[df_all["Type"].str.lower().isin(["manuscript", "ms", "mss"])].copy()

    df_place_paper = df_place[["Node", "Label", "Type"] + PAPER_METRICS].copy()
    df_mss_paper = df_mss[["Node", "Label", "Type"] + PAPER_METRICS].copy()

    place_pct = df_percentiles[df_percentiles["Type"].str.lower().eq("place")].copy()
    mss_pct = df_percentiles[df_percentiles["Type"].str.lower().isin(["manuscript", "ms", "mss"])].copy()

    df_place_paper.to_csv(os.path.join(OUTPUT_DIR, "model1_place_metrics.csv"), index=False)
    place_pct.to_csv(os.path.join(OUTPUT_DIR, "model1_place_metrics_percentiles.csv"), index=False)

    df_mss_paper.to_csv(os.path.join(OUTPUT_DIR, "model1_manuscript_metrics.csv"), index=False)
    mss_pct.to_csv(os.path.join(OUTPUT_DIR, "model1_manuscript_metrics_percentiles.csv"), index=False)

    print(f"Saved paper-ready tables in: {OUTPUT_DIR}")
    print(f"Saved audit file in: {AUDIT_DIR}")


# ============================================================
# HEATMAPS
# ============================================================

import matplotlib.pyplot as plt


def plot_heatmap(df_pct: pd.DataFrame, places: list, filename: str, height_per_row: float = 0.28) -> None:
    """
    Plot a heatmap without a figure title. This keeps the figure clean for
    article use; the caption should explain what the figure shows.
    """
    subset = df_pct[df_pct["Label"].isin(places)].copy()
    if subset.empty:
        print(f"Skipping empty heatmap: {filename}")
        return

    subset["Label"] = pd.Categorical(subset["Label"], categories=places, ordered=True)
    subset = subset.sort_values("Label")

    data = subset.set_index("Label")[PAPER_METRICS]

    fig_height = max(4, len(data) * height_per_row)
    fig_width = 11

    fig, ax = plt.subplots(figsize=(fig_width, fig_height))
    im = ax.imshow(data.values, aspect="auto", vmin=0, vmax=1, cmap="coolwarm")

    ax.set_xticks(np.arange(len(PAPER_METRICS)))
    ax.set_xticklabels(PAPER_METRICS, rotation=45, ha="right")
    ax.set_yticks(np.arange(len(data.index)))
    ax.set_yticklabels(data.index)

    ax.set_xlabel("Metric percentile among places (0 = lowest, 1 = highest)")
    ax.set_ylabel("Place")

    cbar = fig.colorbar(im, ax=ax)
    cbar.set_label("Metric percentile")

    ax.set_xticks(np.arange(-0.5, len(PAPER_METRICS), 1), minor=True)
    ax.set_yticks(np.arange(-0.5, len(data.index), 1), minor=True)
    ax.grid(which="minor", color="white", linestyle="-", linewidth=0.5)
    ax.tick_params(which="minor", bottom=False, left=False)

    plt.tight_layout()
    outpath = os.path.join(OUTPUT_DIR, filename)
    plt.savefig(outpath, dpi=300, bbox_inches="tight")
    plt.close()
    print(f"Saved: {outpath}")


def save_heatmaps(place_pct: pd.DataFrame) -> None:
    place_pct = place_pct.copy()
    place_pct = place_pct.sort_values("Mean percentile", ascending=False)

    all_places = place_pct["Label"].tolist()
    top10 = place_pct.head(10)["Label"].tolist()
    bottom10 = place_pct.tail(10)["Label"].tolist()

    plot_heatmap(place_pct, all_places, "figure_model1_all_place_metrics.png", height_per_row=0.24)
    plot_heatmap(place_pct, top10, "figure_model1_top10_places.png", height_per_row=0.55)
    plot_heatmap(place_pct, bottom10, "figure_model1_bottom10_places.png", height_per_row=0.55)

    manuscript_centres = [
        "Reichenau", "St Gall", "Bobbio", "Laon", "Regensburg",
        "Corbie", "Strasbourg", "Freising", "Fulda", "Reims",
        "Fleury", "Verdun", "Echternach", "Canterbury", "Würzburg"
    ]
    manuscript_centres = [p for p in manuscript_centres if p in all_places]
    plot_heatmap(place_pct, manuscript_centres, "figure_model1_manuscript_centres.png", height_per_row=0.45)

    regional_places = [
        "Ireland*", "France", "Germany", "Italy", "England",
        "Northumbria", "Brittany", "Switzerland", "Wales", "Salzburg"
    ]
    regional_places = [p for p in regional_places if p in all_places]
    plot_heatmap(place_pct, regional_places, "figure_model1_regional_places.png", height_per_row=0.55)


# ============================================================
# MAIN
# ============================================================

def main():
    print("Building Model 1 graph...")
    G = build_model1_graph()
    print(f"Graph ready: {G.number_of_nodes()} nodes, {G.number_of_edges()} edges")

    df_all = compute_metrics(G)
    df_pct = make_percentile_table(df_all)

    pct_lookup = df_pct.set_index("Node")["Mean percentile"].to_dict()
    df_all["Mean percentile"] = df_all["Node"].map(pct_lookup).fillna(0.0)

    save_tables(df_all, df_pct)

    place_pct = df_pct[df_pct["Type"].str.lower().eq("place")].copy()
    save_heatmaps(place_pct)

    print("\nDone. Tables and heatmaps are in:", os.path.abspath(OUTPUT_DIR))


if __name__ == "__main__":
    main()
