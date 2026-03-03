"""
Model 2 — Network metrics for MIrA (Places + Libraries)

Author: Dr Sudhansu Bala Das
Email: baladas.sudhansu@gmail.com

What this script does
---------------------
1) Loads Model 2 node/edge CSVs (places, libraries; manuscript edges; place hierarchy).
2) Builds a directed NetworkX graph (DiGraph).
3) Computes standard structural network metrics (NO semantics, NO plots).
4) Writes results to CSV tables in the output_model2/ folder.

Expected input files (in the same folder as this script)
--------------------------------------------------------
- nodes_places.csv
- nodes_libraries.csv
- edges_mss.csv
- edges_places-hierarchy.csv

Outputs
-------
- output_model2/metrics_all_nodes.csv
- output_model2/metrics_place_nodes.csv
- output_model2/metrics_library_nodes.csv
"""

import os
import numpy as np
import pandas as pd
import networkx as nx


# ----------------------------
# SETTINGS
# ----------------------------
OUTPUT_DIR = "output_model2"
os.makedirs(OUTPUT_DIR, exist_ok=True)

NODES_PLACES_FILE = "nodes_places.csv"
NODES_LIBRARIES_FILE = "nodes_libraries.csv"
EDGES_MSS_FILE = "edges_mss.csv"
EDGES_HIER_FILE = "edges_places-hierarchy.csv"


# ----------------------------
# HELPERS
# ----------------------------
def normalize_edges(df: pd.DataFrame) -> pd.DataFrame:
    """
    Normalise MIrA edge CSV formats to common columns: source, target, weight, type.

    Supports:
    - edges_mss.csv with node_id_from / node_id_to
    - edges_places-hierarchy.csv with parent_id / child_id
    """
    df = df.copy()

    if "node_id_from" in df.columns and "node_id_to" in df.columns:
        df = df.rename(columns={"node_id_from": "source", "node_id_to": "target"})
    elif "parent_id" in df.columns and "child_id" in df.columns:
        df = df.rename(columns={"parent_id": "source", "child_id": "target"})
    else:
        raise ValueError(f"Edges file missing expected columns. Found: {list(df.columns)}")

    # Ensure a numeric weight column exists
    if "weight" not in df.columns:
        df["weight"] = 1.0
    df["weight"] = pd.to_numeric(df["weight"], errors="coerce").fillna(1.0)

    # Optional edge type (kept for traceability)
    if "type" not in df.columns:
        df["type"] = "unknown"

    return df


def safe_eigenvector_centrality(G: nx.DiGraph) -> dict:
    """
    Eigenvector centrality can fail to converge on some graphs.
    If it fails, return zeros rather than crashing.
    """
    try:
        return nx.eigenvector_centrality(G, weight="weight", max_iter=2000)
    except nx.PowerIterationFailedConvergence:
        return {n: 0.0 for n in G.nodes()}


# ----------------------------
# MAIN
# ----------------------------
def main():
    print("Reading CSV inputs (Model 2)...")

    nodes_places = pd.read_csv(NODES_PLACES_FILE)
    nodes_libraries = pd.read_csv(NODES_LIBRARIES_FILE)
    edges_mss = pd.read_csv(EDGES_MSS_FILE)
    edges_hier = pd.read_csv(EDGES_HIER_FILE)

    # Normalise node IDs
    nodes_places = nodes_places.rename(columns={"node_id": "id"})
    nodes_libraries = nodes_libraries.rename(columns={"node_id": "id"})

    # Normalise edge schemas
    edges_mss = normalize_edges(edges_mss)
    edges_hier = normalize_edges(edges_hier)

    # Combine nodes and edges
    nodes_df = pd.concat([nodes_places, nodes_libraries], ignore_index=True)
    edges_df = pd.concat([edges_mss, edges_hier], ignore_index=True)

    # ----------------------------
    # BUILD GRAPH
    # ----------------------------
    print("Building the directed network...")
    G = nx.DiGraph()

    # Add nodes with useful attributes
    for _, row in nodes_df.iterrows():
        node_id = row["id"]
        G.add_node(
            node_id,
            label=row.get("display_text", node_id),
            type=row.get("node_type", "unknown"),
            lat=row.get("lat", np.nan),
            lng=row.get("lng", np.nan),
        )

    # Add edges (weighted)
    for _, row in edges_df.iterrows():
        s, t = row["source"], row["target"]
        w = float(row.get("weight", 1.0))
        etype = row.get("type", "unknown")

        # Ensure endpoints exist even if missing from node tables
        if s not in G:
            G.add_node(s, label=s, type="unknown")
        if t not in G:
            G.add_node(t, label=t, type="unknown")

        G.add_edge(s, t, weight=w, type=etype)

    # Remove self-loops
    G.remove_edges_from(nx.selfloop_edges(G))

    print(f"Graph ready: {G.number_of_nodes()} nodes, {G.number_of_edges()} edges")

    # ----------------------------
    # METRICS (NETWORK ONLY)
    # ----------------------------
    print("Computing metrics (this may take a bit on larger graphs)...")

    # Degrees
    in_deg = dict(G.in_degree())
    out_deg = dict(G.out_degree())

    # Strength = sum of weights
    in_strength = dict(G.in_degree(weight="weight"))
    out_strength = dict(G.out_degree(weight="weight"))

    # Centralities
    deg_cent = nx.degree_centrality(G)
    btw_cent = nx.betweenness_centrality(G, weight="weight", normalized=True)

    # Closeness on undirected version is more stable for disconnected/directed graphs
    cls_cent_und = nx.closeness_centrality(G.to_undirected())

    pagerank = nx.pagerank(G, weight="weight")
    eig_cent = safe_eigenvector_centrality(G)

    # ----------------------------
    # SAVE TABLES
    # ----------------------------
    df_all = pd.DataFrame({
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

    out_all = os.path.join(OUTPUT_DIR, "metrics_all_nodes.csv")
    df_all.to_csv(out_all, index=False)
    print(f"Saved: {out_all}")

    # Place-only table
    df_places = df_all[df_all["Type"] == "place"].copy()
    out_places = os.path.join(OUTPUT_DIR, "metrics_place_nodes.csv")
    df_places.to_csv(out_places, index=False)
    print(f"Saved: {out_places}")

    # Library-only table (common node_type labels: library/libraries)
    df_lib = df_all[df_all["Type"].isin(["library", "libraries"])].copy()
    out_lib = os.path.join(OUTPUT_DIR, "metrics_library_nodes.csv")
    df_lib.to_csv(out_lib, index=False)
    print(f"Saved: {out_lib}")

    print("\n Finished. You can now open the CSVs in Excel, pandas, or R.")


if __name__ == "__main__":
    main()