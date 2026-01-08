# Extract library nodes from MSS XML
# 

import xml.etree.ElementTree as ET
import csv
import os

current_dir = os.path.dirname(os.path.abspath(__file__))

xml_source = 'data/mss_compiled.xml'
output_path = current_dir + '/nodes_libraries.csv'

# Open the XML file
mss_tree = ET.parse(xml_source)
mss_root = mss_tree.getroot()

# Select nodes (all MSS for now, can be a subset if desired)
target_path = "./manuscript"

# Extract library IDs, using a set to avoid duplicates
nodes = set()
for ms in mss_root.findall(target_path):
    identifiers = ms.findall("identifier")
    for identifier in identifiers:
        # extract value of libraryID attribute from <identifier>
        library_id = identifier.get('libraryID')
        if library_id:
            nodes.add(library_id)

# Make list of all library display labels and coords
libraries_tree = ET.parse('data/other/libraries.xml')
libraries_root = libraries_tree.getroot()

library_labels = {}
library_coords = {}

for library in libraries_root.findall(".//library"):
    library_id = library.get('id')
    # get name
    library_name = library.findtext("name")
    if library_id and library_name:
        library_labels[library_id] = library_name
    # get coords
    this_library_coords = library.findtext("coords")
    if library_id and this_library_coords:
        library_coords[library_id] = this_library_coords


# Write results to CSV
with open(output_path, mode='w', newline='', encoding='utf-8') as file:
    writer = csv.writer(file)
    writer.writerow(["node_id", "display_text", "node_type", "lat", "lng"])  # Header
    for library_id in nodes:
        writer.writerow([
            'library_' + library_id,
            library_labels.get(library_id, library_id),  # Use library ID as fallback
            'library',
            library_coords.get(library_id, "").split(",")[0].strip(), 
            library_coords.get(library_id, "").split(",")[1].strip()
            ],)

print(f"Extracted nodes for {len(nodes)} libraries.")
print(f"Saved to {output_path}.")
