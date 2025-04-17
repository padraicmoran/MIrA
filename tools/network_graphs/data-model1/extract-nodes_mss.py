# Extract manuscript IDs from mss_compiled.xml

import xml.etree.ElementTree as ET
import csv
import os

current_dir = os.path.dirname(os.path.abspath(__file__))

xml_source = 'data/mss_compiled.xml'
output_path = current_dir + '/nodes_mss.csv'

# Open the XML file
mss_tree = ET.parse(xml_source)
mss_root = mss_tree.getroot()

# Find places of origin and provenance
target_path = "./manuscript"

nodes = set()
for ms in mss_root.findall(target_path):
    ms_id = ms.get('id')
    if ms_id:
        nodes.add(ms_id)  # Add to set to eliminate duplicates

# Write results to CSV
with open(output_path, mode='w', newline='', encoding='utf-8') as file:
    writer = csv.writer(file)
    writer.writerow(["node_id", "display_text", "node_type", "lat", "lng"])  # Header
    for ms_id in nodes:
        writer.writerow([
            'ms_' + ms_id, 
            ms_id, 
            'manuscript',
            '', ''  # no lat/lng for manuscripts
            ])

print(f"Extracted {len(nodes)} manuscripts.")
print(f"Saved to {output_path}.")