# Extract edges for manuscripts
# If a manuscript has no place of provenance, then make edges from origins to libraries
# If is has, then 1) make edges from origins to provenances, and 2) from provenances to libraries
# Calculate weightings to reflect multiple associated places or libraries

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

# Loop through each manuscript node
for ms in mss_root.findall("./manuscript"):
    ms_id = ms.get('id')
    print(ms_id)
    if ms_id:
        # Get origin place IDs
        origin_place_ids = []
        for origin_place in ms.findall("./history/origin/place"):
            origin_place_id = origin_place.get('id')
            if origin_place_id:
                origin_place_ids.append(origin_place_id)
        # Get provenance place IDs
        prov_place_ids = []
        for prov_place in ms.findall("./history/provenance/place"):
            prov_place_id = prov_place.get('id')
            if prov_place_id:
                prov_place_ids.append(prov_place_id)
        # Get library IDs
        library_ids = []
        for identifier in ms.findall("./identifier"):
            library_id = identifier.get('libraryID')
            if library_id:
                library_ids.append(library_id)

        # If there are no provenance places, make edges from origins to libraries
        if not prov_place_ids:
            if origin_place_ids and library_ids:
                # Calculate weight based on number of edges
                num_edges = len(origin_place_ids) * len(library_ids)
                weight = 1 / num_edges
                # Create edges
                for origin_place_id in origin_place_ids:
                    for library_id in library_ids:
                        edges.append([origin_place_id, 'library_' + library_id, 'yes', weight, 'primary_movement', ms_id])

        # If there are provenance places, make edges from origins to provenances, and from provenances to libraries
        else:
            # Origins to provenances
            if origin_place_ids and prov_place_ids:
                # Calculate weight based on number of edges
                num_edges = len(origin_place_ids) * len(prov_place_ids)
                weight = 1 / num_edges
                # Create edges
                for origin_place_id in origin_place_ids:
                    for prov_place_id in prov_place_ids:
                        edges.append([origin_place_id, prov_place_id, 'yes', weight, 'primary_movement', ms_id])

            # Provenances to libraries
            if library_ids:
                num_edges = len(prov_place_ids) * len(library_ids)
                weight = 1 / num_edges
                # Create edges
                for prov_place_id in prov_place_ids:
                    for library_id in library_ids:
                        edges.append([prov_place_id, 'library_' + library_id, 'yes', weight, 'secondary_movement', ms_id])

# Write results to CSV
with open(output_path, mode='w', newline='', encoding='utf-8') as file:
    writer = csv.writer(file)
    writer.writerow(["node_id_from", "node_id_to", "directed", "weight", "type", "edge_label"])  # Header
    writer.writerows(edges)

print(f"Extracted {len(edges)} MS edges.")
print(f"Saved to {output_path}.")
