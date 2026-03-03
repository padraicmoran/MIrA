#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Network Metrics Export (no plots)
---------------------------------

Author: Dr Sudhansu Bala Das
Email:  baladas.sudhansu@gmail.com

Purpose
-------
This script builds a directed graph from:
- place nodes + manuscript nodes
- manuscript edges + place-hierarchy edges

It then computes standard network metrics and saves them as CSV tables.

Inputs (CSV, keep them in the same folder as this script)
---------------------------------------------------------
- nodes_places.csv
- nodes_mss.csv
- edges_mss.csv
- edges_places-hierarchy.csv

Outputs (CSV)
-------------
- output/metrics_all_nodes.csv
- output/metrics_place_nodes.csv
- output/metrics_manuscript_nodes.csv
- output/metrics_place_nodes_percentiles.csv

Metric notes (simple explanation)
---------------------------------
- In_Degree / Out_Degree:
    counts of incoming/outgoing edges (ignores weights)

- In_Strength_WeightSum / Out_Strength_WeightSum:
    sum of incoming/outgoing edge weights
    (if all weights = 1, strength == degree)

- DegreeCentrality:
    degree normalised by (n - 1)

- BetweennessCentrality:
    how often a node lies on shortest paths (bridge/broker role)

- ClosenessCentrality_Undirected:
    how close a node is to all others (computed on undirected version for stability)

- EigenvectorCentrality:
    high if connected to other highly connected nodes (prestige-like measure)

- PageRank:
    influence measure for directed graphs (random-walk style)

Percentiles (place nodes only)
------------------------------
To compare metrics on the same 0..1 scale, we also compute percentiles among
place nodes only. This is useful later for heatmaps or ranking tables.

"""

from __future__ import annotations

import os
import numpy as np
import pandas as pd
import networkx as nx


# =============================================================================
# SETTINGS (edit filenames here if needed)
# =============================================================================

OUTPUT_DIR = "output"
os.makedirs(OUTPUT_DIR, exist_ok=True)

# Input files (run script in same folder as these CSVs)
NODES_PLACES_FILE = "nodes_places.csv"
NODES_MSS_FILE = "nodes_mss.csv"
EDGES_MSS_FILE = "edges_mss.csv"
EDGES_HIER_FILE = "edges_places-hierarchy.csv"


# =============================================================================
# HELPERS
# =============================================================================

def normalize_edges(df: pd.DataFrame) -> pd.DataFrame:
    """
    Make edge tables consistent so we always have:
      source, target, weight, type

    Supported formats:
    - node_id_from / node_id_to  (common in exported edge lists)
    - parent_id / child_id       (common in hierarchy edges)
    """
    df = df.copy()

    if "node_id_from" in df.columns and "node_id_to" in df.columns:
        df = df.rename(columns={"node_id_from": "source", "node_id_to": "target"})
    elif "parent_id" in df.columns and "child_id" in df.columns:
        df = df.rename(columns={"parent_id": "source", "child_id": "target"})
    else:
        raise ValueError(
            f"Edges file missing expected columns. Found: {list(df.columns)}"
        )

    # If weight is missing, treat the graph as unweighted (weight=1 for all edges)
    if "weight" not in df.columns:
        df["weight"] = 1.0
    df["weight"] = pd.to_numeric(df["weight"], errors="coerce").fillna(1.0)

    # Keep a basic edge type column for traceability
    if "type" not in df.columns:
        df["type"] = "unknown"

    return df


def percentile_rank(series: pd.Series) -> pd.Series:
    """
    Convert a numeric series to percentile ranks in [0, 1].

    - 0.0 = lowest value in the series
    - 1.0 = highest value in the series
    - ties get an averaged percentile
    """
    n = series.shape[0]
    if n <= 1:
        return pd.Series([0.0] * n, index=series.index)

    ranks = series.rank(method="average", ascending=True)
    return (ranks - 1) / (n - 1)


def build_graph(nodes_places: pd.DataFrame,
                nodes_mss: pd.DataFrame,
                edges_mss: pd.DataFrame,
                edges_hierarchy: pd.DataFrame) -> nx.DiGraph:
    """
    Build a directed graph with node metadata.

    
    """
    nodes_df = pd.concat([nodes_places, nodes_mss], ignore_index=True)
    edges_df = pd.concat([edges_mss, edges_hierarchy], ignore_index=True)

    G = nx.DiGraph()

    # Add nodes
    for _, row in nodes_df.iterrows():
        node_id = row["id"]
        G.add_node(
            node_id,
            label=row.get("display_text", node_id),
            type=row.get("node_type", "unknown"),
            lat=row.get("lat", np.nan),
            lng=row.get("lng", np.nan),
        )

    # Add edges
    for _, row in edges_df.iterrows():
        s, t = row["source"], row["target"]
        w = float(row.get("weight", 1.0))
        etype = row.get("type", "unknown")

        # If an endpoint was missing from nodes.csv, add it anyway
        if s not in G:
            G.add_node(s, label=s, type="unknown")
        if t not in G:
            G.add_node(t, label=t, type="unknown")

        G.add_edge(s, t, weight=w, type=etype)

    # Remove self-loops (usually not informative for DH interpretation)
    G.remove_edges_from(nx.selfloop_edges(G))
    return G


def compute_metrics(G: nx.DiGraph) -> pd.DataFrame:
    """
    Compute metrics for every node in the graph and return a tidy DataFrame.
    """
    # Basic degrees (counts)
    in_deg = dict(G.in_degree())
    out_deg = dict(G.out_degree())

    # Strength (sum of weights)
    in_strength = dict(G.in_degree(weight="weight"))
    out_strength = dict(G.out_degree(weight="weight"))

    # Centralities
    deg_cent = nx.degree_centrality(G)
    btw_cent = nx.betweenness_centrality(G, weight="weight", normalized=True)

    # Closeness: computed on undirected version for stability
    cls_cent_und = nx.closeness_centrality(G.to_undirected())

    # PageRank: good for directed influence
    pagerank = nx.pagerank(G, weight="weight")

    # Eigenvector sometimes fails to converge on real-world graphs
    try:
        eig_cent = nx.eigenvector_centrality(G, weight="weight", max_iter=2000)
    except nx.PowerIterationFailedConvergence:
        eig_cent = {n: 0.0 for n in G.nodes()}

    df = pd.DataFrame({
        "Node": list(G.nodes()),
        "Label": [G.nodes[n].get("label", n) for n in G.nodes()],
        "Type": [G.nodes[n].get("type", "unknown") for n in G.nodes()],

        "In_Degree": [in_deg[n] for n in G.nodes()],
        "Out_Degree": [out_deg[n] for n in G.nodes()],
        "In_Strength_WeightSum": [in_strength[n] for n in G.nodes()],
        "Out_Strength_WeightSum": [out_strength[n] for n in G.nodes()],

        "DegreeCentrality": [deg_cent[n] for n in G.nodes()],
        "BetweennessCentrality": [btw_cent[n] for n in G.nodes()],
        "ClosenessCentrality_Undirected": [cls_cent_und[n] for n in G.nodes()],
        "EigenvectorCentrality": [eig_cent[n] for n in G.nodes()],
        "PageRank": [pagerank[n] for n in G.nodes()],
    })

    return df


def compute_place_percentiles(df_places: pd.DataFrame) -> pd.DataFrame:
    """
    Compute percentile-normalised metrics for places only.

    This gives you a clean 0..1 scale per metric (among places),
    useful for ranking or later visualisation.
    """
    metrics_for_pct = [
        "In_Degree",
        "Out_Degree",
        "In_Strength_WeightSum",
        "Out_Strength_WeightSum",
        "DegreeCentrality",
        "BetweennessCentrality",
        "ClosenessCentrality_Undirected",
        "EigenvectorCentrality",
        "PageRank",
    ]

    df_pct = df_places[["Label"] + metrics_for_pct].copy()
    for col in metrics_for_pct:
        df_pct[col] = percentile_rank(df_pct[col])

    # A simple overall score (mean percentile across a compact “research set”)
    research_metrics = [
        "In_Degree",
        "BetweennessCentrality",
        "ClosenessCentrality_Undirected",
        "EigenvectorCentrality",
        "PageRank",
    ]
    df_pct["MeanResearchScore"] = df_pct[research_metrics].mean(axis=1)

    # Sort so the top of the table is “most structurally prominent”
    df_pct = df_pct.sort_values("MeanResearchScore", ascending=False)

    return df_pct


# =============================================================================
# MAIN
# =============================================================================

def main() -> None:
    print("Reading CSV inputs...")

    # Load node tables
    nodes_places = pd.read_csv(NODES_PLACES_FILE)
    nodes_mss = pd.read_csv(NODES_MSS_FILE)

    # Load edge tables
    edges_mss = pd.read_csv(EDGES_MSS_FILE)
    edges_hierarchy = pd.read_csv(EDGES_HIER_FILE)

    # Standardise the node ID column name
    nodes_places = nodes_places.rename(columns={"node_id": "id"})
    nodes_mss = nodes_mss.rename(columns={"node_id": "id"})

    # Standardise edges (source/target/weight/type)
    edges_mss = normalize_edges(edges_mss)
    edges_hierarchy = normalize_edges(edges_hierarchy)

    print("Building the directed network...")
    G = build_graph(nodes_places, nodes_mss, edges_mss, edges_hierarchy)
    print(f"Graph ready: {G.number_of_nodes()} nodes, {G.number_of_edges()} edges")

    print("Computing metrics (this may take a bit on larger graphs)...")
    df_all = compute_metrics(G)

    # Export all nodes
    out_all = os.path.join(OUTPUT_DIR, "metrics_all_nodes.csv")
    df_all.to_csv(out_all, index=False)

    # Export place nodes
    df_places = df_all[df_all["Type"] == "place"].copy()
    out_places = os.path.join(OUTPUT_DIR, "metrics_place_nodes.csv")
    df_places.to_csv(out_places, index=False)

    # Export manuscript nodes (Type naming can vary)
    df_mss_only = df_all[df_all["Type"].isin(["manuscript", "ms", "mss"])].copy()
    out_mss = os.path.join(OUTPUT_DIR, "metrics_manuscript_nodes.csv")
    df_mss_only.to_csv(out_mss, index=False)

    print(f"Saved: {out_all}")
    print(f"Saved: {out_places}")
    print(f"Saved: {out_mss}")

    # Percentile table for places 
    if df_places.empty:
        print("No place nodes detected (Type != 'place'). Skipping percentile table.")
        return

    df_places_pct = compute_place_percentiles(df_places)
    out_pct = os.path.join(OUTPUT_DIR, "metrics_place_nodes_percentiles.csv")
    df_places_pct.to_csv(out_pct, index=False)
    print(f"Saved: {out_pct}")

    print("\n Finished. You can now open the CSVs in Excel, pandas, or R.")


if __name__ == "__main__":
    main()
