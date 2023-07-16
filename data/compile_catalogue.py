# Compiles the collections of individual MS files in /data/mss into one file /data/mss.xml.
# Run this file after updating individual manuscripts.  

import os
import sys
from xml.etree.ElementTree import Element, ElementTree

# load master file
sourcePath = "./data/mss/"
targetFile = "mss.xml"

# prepare new output file
outputTree = ElementTree()
outputTree._setroot(Element("document"))
outputRoot = outputTree.getroot()

# compile component files
mss = os.listdir(sourcePath)
mss.sort()
for ms in mss:
    print(ms)
    msTree = ElementTree().parse(sourcePath + ms)
    outputRoot.append(msTree)        

# sort output file
# TO DO: sort by library, then shelfmark_indexer, then shelfmark


# write output file
outputTree.write("./data/mss.xml", encoding='utf-8', xml_declaration=True)    
print("Done.")
