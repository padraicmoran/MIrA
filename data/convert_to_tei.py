import os
import lxml.etree as ET

src = '002'
dir = os.getcwd()
srcPath = dir + '/data/mss/' + src + '.xml'
outPath = dir + '/data/mss_tei/' + src + '.xml'
xsltPath = dir + '/data/convert_to_tei.xsl'

# print ('Source file: ' + srcPath)

dom = ET.parse(srcPath)
xslt = ET.parse(xsltPath)
transform = ET.XSLT(xslt)
newdom = transform(dom)

str = ET.tostring(newdom, pretty_print=True)
print(str)

with open(outPath, 'wb') as f:
    newdom.write(f, encoding="utf-8", xml_declaration=True, pretty_print=True)