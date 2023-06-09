<?php
/* 
Network graph
*/
function networkGraph($results) {
  global $placeInfo;

  /* PREPARE DATA
  */

  // set up some blank arrays
  $placeList = array();
  $edgeFrom = $edgeTo = $edgeTypes = array();

  // find all place IDs in this result set
  // add unique IDs to a unique list (will be nodes later)
  // create an edge record for each one
  
  foreach($results as $ms) {
    $msID = strval($ms['id']);
    $edgeAssigned = false;

    // check for places and retrieve IDs
    // check if this MS links to one or more origin places
    $checkOriginPlaces = $ms->xpath ('//manuscript[@id="' . $msID  . '"]//origin/place/@id');
    foreach ($checkOriginPlaces as $placeFound) {
      $placeID = strval($placeFound['id']);
      // add ID to place list if new
      if (! in_array($placeID, $placeList)) array_push($placeList, $placeID);
      // build edge list
      array_push($edgeFrom, $placeID);
      array_push($edgeTo, $msID);
      array_push($edgeTypes, 'origin');
      $edgeAssigned = true;
    }
      
    // check if this MS links to one or more provenance places
    $checkProvPlaces = $ms->xpath ('//manuscript[@id="' . $msID  . '"]//provenance/place/@id'); // don't understand why this works! expect //place/@id, but no results
    foreach ($checkProvPlaces as $placeFound) {
      $placeID = strval($placeFound['id']);
      // add to list if new
      if (! in_array($placeID, $placeList)) array_push($placeList, $placeID);
      // build edge list
      array_push($edgeFrom, $msID);
      array_push($edgeTo, $placeID);
      array_push($edgeTypes, 'prov');
      $edgeAssigned = true;
    }

    // find MSS without a place assigned; assigned them to fake place "ubique"
    // (if not linked to a fixed node, they will drift far away)
    if (! $edgeAssigned) {
      array_push($edgeFrom, $msID);
      array_push($edgeTo, 'ubique');
      array_push($edgeTypes, 'hidden');
    }
  }

?>

<script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>

<button class="btn btn-secondary float-end" onclick="fullScreen(document.getElementById('networkGraph'));">Full screen</button>

<form>
Black lines indicate origin, blue arrows provenance.
To view a manuscript, enter the number here: 
<input type="text" id="msNum" class="" style="width: 50px; ">
<input type="submit" class="btn btn-success" value="go" onclick="n = document.getElementById('msNum').value; if (n.length > 0) { location.href= '/' + n; } return false; ">
</form>



<div id="networkGraph" class="border border-secondary rounded shadow bg-light" style="height: 480px; ">
</div>

<script type="text/javascript">

// create an array with nodes
var nodes = new vis.DataSet([

<?php

  /* OUTPUT DATA
  */
  
  // node for MSS without location (if needed)
  if (in_array('hidden', $edgeTypes)) {
  print '{ id: "ubique", 
    label: "Not yet assigned", 
      shape: "box", 
      color: "grey",
      x: -1100, y: -8900, fixed: { x: true, y: true }  
    }, ';
  }

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

    // handle map coords
    $coords = explode(',', $placeInfo[$place]['coords']);
    $x = $coords[1] * 150;
    $y = $coords[0] * -200;
    $geoInfo = ', x: ' . $x . ', y: ' . $y . ', fixed: { x: true, y: true }';

    $regionAttributes = '';
    if ($placeInfo[$place]['type'] == 'region') $regionAttributes = ', font: { size: 46 } ';
    
    print '{ id: "' . $place . '", 
      label: "' . $placeInfo[$place]['name'] . '", 
      shape: "box", 
      color: "green"
      ' . $geoInfo . ' 
      ' . $regionAttributes . ' 
    },' . "\n";
  }
?>
]);



// create an array with edges
var edges = new vis.DataSet([

<?php
  for ($n = 0; $n < count($edgeFrom); $n++) {
    if ($edgeTypes[$n] == 'origin') {
      print '{ from: "' . $edgeFrom[$n] . '", to: "' . $edgeTo[$n] . '", color: "black", width: 2 },' . "\n";
    }  
    elseif ($edgeTypes[$n] == 'prov') {
      print '{ from: "' . $edgeFrom[$n] . '", to: "' . $edgeTo[$n] . '", arrows: "to", color: "blue", width: 2 },' . "\n";
    }
    else {  // hidden edges
      print '{ from: "' . $edgeFrom[$n] . '", to: "' . $edgeTo[$n] . '", hidden: true  },' . "\n";
    }
  }
?>

]);



// create the network object
var container = document.getElementById("networkGraph");
var data = {
  nodes: nodes,
  edges: edges,
};
var options = {
  configure: {
    enabled: false  /* config panel */
  },
  interaction: {
    navigationButtons: true,
    hover: true
  },
  layout: {
  },
  nodes: {
    font: {
      color: 'white',
      size: 25
    }
  }
};
var network = new vis.Network(container, data, options);

</script>

<script type="text/javascript">

function fullScreen(el) {
  if (el.requestFullscreen) el.requestFullscreen();
  else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen(); /* Safari */
  else if (el.msRequestFullscreen) { el.msRequestFullscreen() /* IE11 */  }

}

</script>

<?php
}
?>