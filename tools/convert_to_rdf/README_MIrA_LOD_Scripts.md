# MIrA LOD Scripts

This folder contains the script used for generating MIrA Linked Open Data (LOD) RDF dataset.


---

## `mira_xml_to_wikidata_aligned_final.py`

This is the XML to RDF conversion script.

It reads the MIrA XML source files and generates RDF aligned with the Wikidata Manuscripts data model. The script creates:

- one combined RDF file
- separate RDF files for each MIrA entity type:
  - library
  - manuscript
  - person
  - text

The script uses stable MIrA URIs, such as:

```text
https://mira.ie/entity/manuscript/{id}
https://mira.ie/entity/library/{id}
https://mira.ie/entity/person/{id}
https://mira.ie/entity/text/{id}
```

It also uses Wikidata direct properties where appropriate, for example:

```text
P31   instance of
P217  inventory number
P195  collection
P1071 location of creation
P571  inception
P2048 height
P2049 width
P953  full work available at URL
P1574 exemplar of
```



Default output structure:

```text
rdf/
  mira_wikidata_aligned.ttl
  mira_wikidata_aligned_evaluation_log.txt
  entities/
    library/
    manuscript/
    person/
    text/
```

Run:

```bash
python mira_xml_to_wikidata_aligned_final.py
```

---

Author: Dr. Sudhansu Bala Das  
Email: baladas.sudhansu@gmail.com  
Last refined: 15 June 2026
