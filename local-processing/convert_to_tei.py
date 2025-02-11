# Functionality to do:
# 1) Multi-part manuscripts
# 2) Add settlement(city) and repository (library) information.

import os
import lxml.etree as ET
import subprocess

dir = os.getcwd()
src_dir = dir + '/data/mss/'
out_dir = dir + '/data/mss_tei/'
xslt_path = dir + '/local-processing/convert_to_tei.xslt'

# Basic transform function wuth LXML library; supports XSLT 1.0 only
def xslt1(file_name):

    # Load XML and XSLT files into tree objects
    src_tree = ET.parse(src_dir + file_name)
    xslt_tree = ET.parse(xslt_path)

    # Create and apply a transformer object 
    transformer = ET.XSLT(xslt_tree)
    out_tree = transformer(src_tree)

    # Output
    out_text = ET.tostring(out_tree, pretty_print=True)
    with open(out_dir + file_name, 'wb') as f:
        f.write(out_text)
    print('Done: ' + file_name)

# Transform function using Saxon JAR file, accessed via command line; supports XSLT 2.0
# To set up: created a bash script "saxon" (in usr/local/bin) to call the JAR file (in /usr/local/lib/):
#   #!/bin/bash
#   java -cp /usr/local/lib/saxon/saxon-he.jar:/usr/local/lib/saxon/xmlresolver-4.1.0.jar net.sf.saxon.Transform "$@"
# xmlresolver JAR also needed.
def xslt2(file_name):

    # Construct the command
    command = [
        'saxon',
        '-s:' + src_dir + file_name,
        '-xsl:' + xslt_path,
        '-o:' + out_dir + file_name
    ]
    # print(command)
    # Execute the command
    result = subprocess.run(command, shell=False, capture_output=True, text=True)

    # Check the result
    if result.returncode == 0:
        print("Transformation successful: " + file_name)
        print(result.stdout)
    else:
        print("Transformation failed:" + file_name)
        print(result.stderr)

# file_name = '001.xml'
files = sorted(os.listdir(src_dir))
xml_files = [f for f in files if f.endswith('.xml')]
for xml_file in xml_files:
    xslt2(xml_file)
