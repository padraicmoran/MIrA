<?php
/*
This script receives requests for entity URLs in RDF data and redirects 
to an appropriate resource.

.htaccess diverts requests for /entity/{type}/{id} here.
Then:
1) Requests from web browsers are redirected to a human-readable page.
2) Requests from RDF clients are redirected to the RDF data for the entity.
    RDF data is in Turtle, JSON-LD, or RDF/XML format, depending on the client's Accept header.
 
*/

// 1. Grab and sanitize the inputs
$type = isset($_GET['type']) ? trim($_GET['type']) : null;
$id   = isset($_GET['id'])   ? trim($_GET['id']) : null;

// Validate the allowed types
$allowed_types = ['library', 'manuscript', 'person', 'text'];

if (!in_array($type, $allowed_types) || empty($id)) {
    http_response_code(400);
    echo "400 Bad Request: Invalid type or missing ID.";
    exit;
}

// 2. Determine the requested format via Content Negotiation
$acceptHeader = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
$format = determine_format($acceptHeader);

// 3. Route the request based on the format
if ($format === 'html') {
    // Requester is a human/browser -> Redirect to the UI page
    handle_human_redirect($type, $id);
} else {
    // Requester is a machine -> Serve or redirect to the data file
    handle_machine_request($type, $id, $format);
}

/**
 * Parses the Accept header to determine if the client wants HTML or Linked Data.
 */
function determine_format($acceptHeader) {
    // 1. Check for standard human browsers first.
    // If text/html is present, we almost always want to serve the HTML landing page.
    if (strpos($acceptHeader, 'text/html') !== false) {
        return 'html';
    }

    // 2. If it's not a standard browser request, check for machine data formats.
    if (strpos($acceptHeader, 'text/turtle') !== false) {
        return 'ttl';
    }
    if (strpos($acceptHeader, 'application/ld+json') !== false) {
        return 'jsonld';
    }
    if (strpos($acceptHeader, 'application/rdf+xml') !== false || strpos($acceptHeader, 'application/xml') !== false) {
        return 'rdf';
    }

    // 3. Fallback default if they accept anything (*/*) or it's unparseable
    return 'html';
}

/**
 * Handles 303 Redirects for human viewers to your HTML landing pages.
 */
function handle_human_redirect($type, $id) {
    // User-facing URL structure here
    $redirect_url = "/{$type}/{$id}";
    
    // 303 See Other is the standard for Linked Data URI dereferencing
    header("HTTP/1.1 303 See Other");
    header("Location: " . $redirect_url);
    exit;
}

/**
 * Handles delivering the semantic data files to machines.
 */
function handle_machine_request($type, $id, $format) {
    // Define the content types mapping
    $content_types = [
        'ttl'    => 'text/turtle; charset=utf-8',
        'jsonld' => 'application/ld+json; charset=utf-8',
        'rdf'    => 'application/rdf+xml; charset=utf-8'
    ];

    $baseDir = realpath('../data/rdf/entities');
    $file_path = $baseDir . "/{$type}/{$id}.{$format}";
    if (file_exists($file_path)) {
        header("Content-Type: " . $content_types[$format]);
        readfile($file_path);
        exit;
    } else {
        http_response_code(404);
        echo "404 Not Found: Data file not found.";
        exit;
    }
}


?>