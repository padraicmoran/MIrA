This script converts MIrA manuscript data from XML format into RDF (Turtle). Its purpose is to make the manuscript information easier to query and compare, and to prepare the data for possible integration with Wikidata in the future.

What the script currently does:
- Reads manuscript information from the main XML file in /data/
- Uses additional authority files for people, places, texts, and libraries
- Creates unique manuscript records in RDF
- Uses Wikidata identifiers when they are available
- Writes the RDF output to /data/rdf/ for further use

Why this matters:
- The original MIrA data is stored in XML, which is not easy to query
- RDF makes it possible to explore manuscripts using SPARQL and other tools
- The data becomes more reusable for digital research and linked data projects

What has not been done yet:
- No automatic lookup for missing Wikidata identifiers
- No reconciliation or validation against Wikidata
- No support yet for publishing or uploading back to Wikidata
- No integration with the MIrA website workflow
- No automated testing or CI process for generating RDF
- No public documentation of SPARQL query examples

Future improvements (planned or possible):
- Better reporting of what data was included or skipped
- Adding reconciliation to match more people and places to Wikidata
- Providing example SPARQL queries for researchers
- Making the output available online as Linked Open Data
- Improving deployment so the script can run automatically

Overall:
This conversion script is a first step toward exposing MIrA manuscript data in a structured, research-friendly form. It focuses on accuracy and clean modelling, and can be extended as the project develops.
