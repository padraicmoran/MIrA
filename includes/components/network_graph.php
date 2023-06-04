<?php
/* 
Network graph
*/
function networkGraph($results) {

  // make associative array with place IDs => names
  if (file_exists('data/places.xml')) {
    $xml_places = simplexml_load_file('data/places.xml');
    $placeList = array();
    foreach ($xml_places->place as $place) {
      $i = strval($place['id']);
      $n = strval($place->name);
      $placeList[$i] = $n;
    }
  }

  // find place IDs in this result set
  // add unique IDs to a unique list (will be nodes)
  // create an edge record for each one
  $placeList = array();
  $edgeFrom = array();
  $edgeTo = array();
  foreach($results as $ms) {
    // check for places and retrieve IDs
    $msID = strval($ms['id']);
    $checkPlaces = $ms->xpath ('//manuscript[@id="' . $msID  . '"]//place/@id');
    foreach ($checkPlaces as $placeFound) {
      $placeID = strval($placeFound['id']);

      // build unique list
      if (! in_array($placeID, $placeList)) array_push($placeList, $placeID);

      // build edge list
      array_push($edgeFrom, $msID);
      array_push($edgeTo, $placeID);
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
    print '{ id: "' . $place . '", 
      label: "' . $place . '", 
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
    print '{ from: "' . $edgeFrom[$n] . '", to: "' . $edgeTo[$n] . '", color: "black" },' . "\n";
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
  }
};
var network = new vis.Network(container, data, options);
</script>

<?php
}
?>