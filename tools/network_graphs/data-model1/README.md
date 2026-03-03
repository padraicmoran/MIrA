# Model 1 — Network metrics (places ↔ manuscripts + place hierarchy)

This folder contains a small, self-contained workflow for **computing network metrics** for the MIrA *Model 1* graph (place nodes + manuscript nodes + place-hierarchy links).  
It produces **CSV tables** that can be opened in Excel or analysed in Python/R.

## Contents

- `network_metrics_model1.py` — builds the directed graph and computes metrics
- Input CSVs (expected in this folder):
  - `nodes_places.csv`
  - `nodes_mss.csv`
  - `edges_mss.csv`
  - `edges_places-hierarchy.csv`
- Output folder (created automatically):
  - `output/metrics_all_nodes.csv`
  - `output/metrics_place_nodes.csv`
  - `output/metrics_manuscript_nodes.csv`
  - `output/metrics_place_nodes_percentiles.csv`

## What the script does

1. Loads node + edge CSV files.
2. Builds a **directed** NetworkX graph (`DiGraph`) with edge weights (default weight = 1 if missing).
3. Computes standard network metrics (degrees, weighted degrees/strength, and centralities).
4. Writes results as CSV tables for reproducible analysis.

## How to run (Windows)

Open **Command Prompt** or **Git Bash**, then:

```bash
cd /c/Users/Sudhansu/MIrA/tools/network_graphs/data-model1
python network_metrics_model1.py
