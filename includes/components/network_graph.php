<?php
/* 
Network graph
*/
function networkGraph($results) {
  global $placeInfo;

  // set up some blank arrays
  $placeList = array();
  $edgeFrom = $edgeTo = $edgeTypes = array();

  // find all place IDs in this result set
  // add unique IDs to a unique list (will be nodes later)
  // create an edge record for each one
  
  foreach($results as $ms) {
    $msID = strval($ms['id']);
    
    // check for places and retrieve IDs
    // check orgins first
    $checkOriginPlaces = $ms->xpath ('//manuscript[@id="' . $msID  . '"]//origin/place/@id');
    foreach ($checkOriginPlaces as $placeFound) {
      $placeID = strval($placeFound['id']);
      // add to list if new
      if (! in_array($placeID, $placeList)) array_push($placeList, $placeID);
      // build edge list
      array_push($edgeFrom, $placeID);
      array_push($edgeTo, $msID);
      array_push($edgeTypes, 'origin');
    }
  
    // same for provenances
    $checkProvPlaces = $ms->xpath ('//manuscript[@id="' . $msID  . '"]//provenance/place/@id'); // don't understand why this works! expect //place/@id, but no results
    foreach ($checkProvPlaces as $placeFound) {
      $placeID = strval($placeFound['id']);
      // add to list if new
      if (! in_array($placeID, $placeList)) array_push($placeList, $placeID);
      // build edge list
      array_push($edgeFrom, $placeID);
      array_push($edgeTo, $msID);
      array_push($edgeTypes, 'prov');
    }
  }
?>

<script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>

<div id="networkGraph" class="border border-secondary rounded shadow" id="mapLibrariesContainer" style="height: 480px; ">
</div>

<script type="text/javascript">

// create an array with nodes
var nodes = new vis.DataSet([
<?php
  // write nodes for MSS
  foreach ($results as $ms) {
    print '{ id: ' . $ms['id'] . ', 
      label: "' . $ms['id'] . '", 
      title: "' . $ms->identifier['libraryID'] . ', ' . $ms->identifier->shelfmark . '", 
      shape: "circle", 
      color: "darkred"  
    },' . "\n";
  }

  // write nodes for places
  foreach ($placeList as $place) {
    /*
    $coords = explode(',', $placeInfo[$place]['coords']);
    $x = $coords[0] * -200;
    $y = $coords[1] * 50;
    JS:   x: ' . $x . ', y: ' . $y . ', fixed: { x: true, y: true }  
    */

    print '{ id: "' . $place . '", 
      label: "' . $placeInfo[$place]['name'] . '", 
      shape: "box", 
      color: "green"
    },' . "\n";
  }
?>
  ]);

// create an array with edges
var edges = new vis.DataSet([
<?php
  for ($n = 0; $n < count($edgeFrom); $n++) {
    if ($edgeTypes[$n] == 'origin') $options = ', color: "black", width: 2 ';
    else $options = ', color: "blue", width: 2 ';
    print '{ from: "' . $edgeFrom[$n] . '", to: "' . $edgeTo[$n] . '", arrows: "to"' . $options . ' },' . "\n";
  }
?>
]);

// create a network
var container = document.getElementById("networkGraph");
var data = {
  nodes: nodes,
  edges: edges,
};
var options = {
  nodes: {
    font: {
      color: 'white',
      size: 20
    }
  },
  interaction: {
    navigationButtons: true,
    hover: true
  },
  physics: true
};
var network = new vis.Network(container, data, options);
</script>

<?php
//var_dump($placeList);
}
?>