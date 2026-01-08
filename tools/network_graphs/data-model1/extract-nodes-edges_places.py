# Extract place IDs from mss_compiled.xml
#
# Steps:
# Search through mss_compiled.xml
# Find any <place> inside <origin> or <provenance>, extract @id
# Use set to eliminate duplicates
# Look up IDs in places.xml and get display labels
# Export as nodes.csv
#
# 

import xml.etree.ElementTree as ET
import csv
import os

current_dir = os.path.dirname(os.path.abspath(__file__))

xml_source = 'data/mss_compiled.xml'
output_path1 = current_dir + '/nodes_places.csv'
output_path2 = current_dir + '/edges_places-hierarchy.csv'

# Open the XML file
mss_tree = ET.parse(xml_source)
mss_root = mss_tree.getroot()

# Find places of origin and provenance
target_paths = ["./manuscript/history/origin/place", "./manuscript/history/provenance/place"]


# Function to find any parent ID for a place
def find_parent_place(target_id, root):
    for place in root.findall(".//place"):
        for child in place.findall("place"):
            if child.get("id") == target_id:
                return place.get("id")  # Return the parent place's ID
    return None


# Start main processing
nodes = set()
for path in target_paths:
    for place in mss_root.findall(path):
        ms_place_id = place.get('id')
        if ms_place_id:
            nodes.add(ms_place_id)  # Add to set to eliminate duplicates

# Make list of all place display labels and coords
places_tree = ET.parse('data/other/places.xml')
places_root = places_tree.getroot()

place_labels = {}
place_coords = {}
place_parentID = {}

for place in places_root.findall(".//place"):
    place_id = place.get('id')
    # get name
    place_name = place.findtext("name")
    if place_id and place_name:
        place_labels[place_id] = place_name
    # get coords
    this_place_coords = place.findtext("coords")
    if place_id and this_place_coords:
        place_coords[place_id] = this_place_coords
    # get any parentID
    place_parentID[place_id] = find_parent_place(place_id, places_root)

# Write results to CSV
with open(output_path1, mode='w', newline='', encoding='utf-8') as file:
    writer = csv.writer(file)
    writer.writerow(["node_id", "display_text", "node_type", "lat", "lng"])  # Header
    for place_id in nodes:
        writer.writerow([
            place_id, 
            place_labels.get(place_id, "Unknown"), 
            'place',
            place_coords.get(place_id, "").split(",")[0].strip(), 
            place_coords.get(place_id, "").split(",")[1].strip()
            ],)

print(f"Extracted {len(nodes)} nodes for place IDs.")
print(f"Saved to {output_path1}.")


# Write edges to CSV where the parentID is also a place
with open(output_path2, mode='w', newline='', encoding='utf-8') as file:
    writer = csv.writer(file)
    writer.writerow(["node_id_from", "node_id_to", "directed", "weight", "type"])  # Header
    for place_id in nodes:
        if place_parentID[place_id]:
            writer.writerow([place_parentID[place_id], place_id, 'yes', 1, 'place_hierarchy'])

print(f"Extracted edges for place hierarchies.")
print(f"Saved to {output_path2}.")