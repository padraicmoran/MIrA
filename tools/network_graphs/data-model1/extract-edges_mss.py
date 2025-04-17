# Extract manuscript IDs and associated place IDs from mss_compiled.xml
#
# Steps:
# Search through mss_compiled.xml
# For every node "manuscript", check "manuscript/history/origin" has any children "place""
# If so, extract manuscript/@id and place/@id for each child "place"
# Export as edges_mss.csv

import xml.etree.ElementTree as ET
import csv
import os

current_dir = os.path.dirname(os.path.abspath(__file__))

xml_source = 'data/mss_compiled.xml'
output_path = current_dir + '/edges_mss.csv'

# Open the XML file
mss_tree = ET.parse(xml_source)
mss_root = mss_tree.getroot()

edges = []

# Loop through each manuscript node, check for origin places
for ms in mss_root.findall("./manuscript"):
    ms_id = ms.get('id')
    if ms_id:
        origin = ms.find("./history/origin")
        if origin:
            places = origin.findall("place")
            # calculate weight, based on 1 divided by number of places
            num_places = len(places)
            if num_places > 0:
                weight = 1 / num_places  
                for place in origin.findall("place"):
                    place_id = place.get('id')
                    if place_id:
                        edges.append([place_id, 'ms_' + ms_id, 'yes', weight, 'origin'])

# Loop through each manuscript node, check for provenance places
for ms in mss_root.findall("./manuscript"):
    ms_id = ms.get('id')
    if ms_id:
        prov = ms.find("./history/provenance")
        if prov:
            places = prov.findall("place")
            # calculate weight, based on 1 divided by number of places
            num_places = len(places)
            if num_places > 0:
                weight = 1 / num_places  
                for place in prov.findall("place"):
                    place_id = place.get('id')
                    if place_id:
                        edges.append(['ms_' + ms_id, place_id, 'yes', weight, 'provenance'])

# Write results to CSV
with open(output_path, mode='w', newline='', encoding='utf-8') as file:
    writer = csv.writer(file)
    writer.writerow(["node_id_from", "node_id_to", "directed", "weight", "type"])  # Header
    writer.writerows(edges)

print(f"Extracted {len(edges)} MS edges.")
print(f"Saved to {output_path}.")

