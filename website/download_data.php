<?php
// Download file from data folder (not public on www)

// Get inputs
$type   = isset($_GET['type']) ? trim($_GET['type']) : null;
$id     = isset($_GET['id'])   ? trim($_GET['id']) : null;
$format = isset($_GET['format']) ? trim($_GET['format']) : null;

// Set base path
$dataDir = '../data';

// Set content types
$allowed_types = ['library', 'manuscript', 'person', 'text'];
$allowed_formats = [
    '.xml'      => 'application/xml',
    '_tei.xml'  => 'application/xml',
    '.ttl'      => 'text/turtle',
    '.jsonld'   => 'application/ld+json',
    '.rdf'      => 'application/rdf+xml',
    ];
//echo 'Test: ' . isset($allowed_formats[$format]);
//exit;

// Validate 
if (!in_array($type, $allowed_types) || !isset($allowed_formats[$format]) || empty($id)) {
    http_response_code(404);
    echo "File not found.";
    exit;
}

// Get true path
// zero-pad ID for manuscript
if ($type === 'manuscript') $id = str_pad($id, 3, '0', STR_PAD_LEFT);
if ($format === '.xml') {
    if ($type === 'manuscript') {
        // manuscripts
        $fullPath = realpath($dataDir . '/mss_mira/' . $id . '.xml');
    }
    elseif ($type === 'library' || $type === 'person' || $type === 'text') {
        // temp: these files will need parsing for individual entity outputs?
        $fullPath = realpath($dataDir . '/other/' . $type . '.xml');
    }
    else $fullPath = false;
}
elseif ($format === '_tei.xml') {
    if ($type === 'manuscript') {
        // manuscripts
        $fullPath = realpath($dataDir . '/mss_tei-converted/' . $id . '.xml');
    }
    else $fullPath = false;
}
else {
    // all RDF formats
    $fullPath = realpath($dataDir . '/rdf/entities/' . $type . '/' . $id . $format);
}

// Security checks
if (
    $fullPath === false ||                                  // File doesn't exist
    strpos($fullPath, $baseDir) !== 0 ||                    // Escaped base directory
    !is_file($fullPath)                                     // Not a regular file
) {
    http_response_code(404);
    echo "File not found or access denied.";
    exit;
}

// Serve the XML file
header('Content-Type: ' . $contentTypes[$format] . ' charset=utf-8');
readfile($fullPath);
?>