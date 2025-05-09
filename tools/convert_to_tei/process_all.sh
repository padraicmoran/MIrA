#!/bin/bash
# This script processes all XML files in the input_folder
# and transforms them using stylesheet.xsl, saving the
# output to the output_folder.
# To run: bash process_all.sh

input_folder="/home/padraic/OneDrive/Sites/mira.ie/Repository/data/mss"
stylesheet="convert_to_tei.xslt"
output_folder="/home/padraic/OneDrive/Sites/mira.ie/Repository/data/mss_tei"

for f in "$input_folder"/*.xml; do
  echo "Processing file: $f" # Output the current file being processed
  saxon -s:"$f" -xsl:"$stylesheet" -o:"$output_folder/$(basename "$f")"
  echo "Output saved to: $output_folder/$(basename "$f" .xml).out"
done
