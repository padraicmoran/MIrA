# Model 2 — Network metrics (Places + Libraries)

This folder contains a reproducible workflow for computing **structural network metrics** for MIrA **Model 2**, where the graph includes:

- **Place nodes** (`nodes_places.csv`)
- **Library nodes** (`nodes_libraries.csv`)
- **Library → Place** links (from `edges_mss.csv` in this model)
- **Place → Place** hierarchy links (`edges_places-hierarchy.csv`)

The goal is to support Digital Humanities network analysis by exporting **metrics tables (CSV)** and (optionally) **percentile heatmaps** for place nodes.

---

## Files

### Scripts
- `network_metrics_model2.py`  
  Builds the directed graph and exports network metrics as CSV tables.



### Input CSVs
These files should be present in this folder:
- `nodes_places.csv`
- `nodes_libraries.csv`
- `edges_mss.csv`
- `edges_places-hierarchy.csv`

### Outputs
Created automatically when you run the script:

- `output_model2/metrics_all_nodes.csv`  
  Metrics for all nodes (places + libraries).

- `output_model2/metrics_place_nodes.csv`  
  Metrics for place nodes only.

- `output_model2/metrics_library_nodes.csv`  
  Metrics for library nodes only.

- `output_model2/metrics_place_nodes_percentiles.csv`  
  Place metrics converted to percentile ranks (0–1), plus a combined mean score.

---

## What the script computes

The outputs include standard network measures such as:

- **In_Degree / Out_Degree** (number of incoming/outgoing connections)
- **In_Strength_WeightSum / Out_Strength_WeightSum** (sum of edge weights)
- **DegreeCentrality**
- **BetweennessCentrality** (weighted shortest paths)
- **ClosenessCentrality_Undirected** (computed on the undirected version for stability)
- **EigenvectorCentrality**
- **PageRank**

### Note on “Strength”
Strength is the **sum of edge weights**.

- If all edge weights are 1, strength behaves like degree.
- If weights represent counts or intensities, strength captures cumulative connection weight.

---

## Percentiles (0–1) for places

For interpretability (and to avoid domination by a single metric), place metrics are also converted to percentile ranks:

- **0** = lowest among places  
- **1** = highest among places  

A mean score is computed across selected metrics to provide a balanced “structural prominence” ranking.

---

## How to run

From this folder:

```bash
python network_metrics_model2.py
