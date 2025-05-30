<?php
/* 
Network graph
*/
function networkGraph($results) {
  global $placeInfo, $libraries;
  $showLibraries = false;
  if (cleanInput('lib') == 'y') $showLibraries = 1;

  print '<h3 id="network" class="mt-5 pt-2">Network graph</h3>';
  if (sizeof($results) > 50) print '<p id="slowLoadWarning" class="bg-warning rounded py-1 px-3">Large result sets may take several seconds to draw.</p>';

  /* PREPARE DATA
  */

  // set up some blank arrays
  $nodeList = array();    // containing arrays [id, label, x, y, type]
  $edgeList = array();    // containing arrays [from, to, type, weight]
  $place_list = array();
  $library_list = array();

  // cycle through MSS in this result set
  foreach($results as $ms) {
    $msID = strval($ms['id']);

    // add a node for this MS
    array_push($nodeList, array(
      $msID, 
      makeMsHeading($ms),
      null, 
      null, 
      'ms'
    ));

    // check origin place(s) for this manuscript
    $checkOriginPlaces = $ms->xpath ('//manuscript[@id="' . $msID  . '"]//origin/place/@id');
    if ($checkOriginPlaces) {
      $weight = 1 / (count($checkOriginPlaces));
      foreach ($checkOriginPlaces as $place) {
        // add to list
        array_push($place_list, strval($place['id'])); 
        // add edge
        array_push($edgeList, array($msID, $place['id'], 'origin', $weight));
      }
    }

    // check provenance place(s) for this manuscript
    $checkProvPlaces = $ms->xpath ('//manuscript[@id="' . $msID  . '"]//provenance/place/@id');
    if ($checkProvPlaces) {
        $weight = 1 / (count($checkProvPlaces));
      foreach ($checkProvPlaces as $place) {
        // add to list
        array_push($place_list, strval($place['id']));  
        // add edge
        array_push($edgeList, array($msID, $place['id'], 'prov', $weight));
      }
    }

    if ($showLibraries) {
      // check libraries for this manuscript
      $checkLibraries = $ms->xpath ('//manuscript[@id="' . $msID  . '"]//identifier/@libraryID');
      foreach ($checkLibraries as $libID) {
        // add to list
        array_push($library_list, strval($libID));
        // add edge
        array_push($edgeList, array($msID, 'library_' . strval($libID), 'library', 1));
      }
    }
  }

  // create node data for places
  $place_list = array_unique($place_list);        // remove duplicates
  foreach ($place_list as $placeID) {
    $type = 'place';
    if ($placeInfo[$placeID]['type'] == 'region') $type = 'region';
    $coords = processCoords($placeInfo[$placeID]['coords']);
    array_push($nodeList, array(
      $placeID, 
      $placeInfo[$placeID]['name'],
      $coords[0], 
      $coords[1], 
      $type
    ));
    // add edge for parent, if there is one
    if ($placeInfo[$placeID]['parentID']) {
      // check that the parent is in the list
      if (in_array($placeInfo[$placeID]['parentID'], $place_list)) {
        array_push($edgeList, array($placeID, $placeInfo[$placeID]['parentID'], 'place_parent', 1));
      }
    }
  }

  if ($showLibraries) {
    // create node data for libraries
    $library_list = array_unique($library_list);        // remove duplicates
    foreach ($library_list as $libID) {
      $type = 'place';
      $coords = processCoords($libraries[$libID]['coords']);
      array_push($nodeList, array(
        'library_' . $libID, 
        $libraries[$libID]['shortName'],
        $coords[0], 
        $coords[1], 
        'library'
      ));
    }
  }

?>

<script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>

<!-- custom control buttons -->
<div id="customButtons" class="float-end">
<button id="btnToggleFixed" class="btn btn-secondary" onclick="toggleFixed(this); ">Geo-locations</button>
<button class="btn btn-secondary" onclick="redraw(); ">Redraw</button>
<button class="btn btn-secondary" onclick="fullScreen(document.getElementById('networkGraph'));">Full screen</button>
</div>

<p>Black or grey arrows indicate origin, blue arrows indicate provenance.
Medieval places are in green, modern libraries in pink.
Double-click any node for more information.
</p>

<!-- canvas -->
<div id="networkGraph" class="border border-secondary rounded shadow bg-light" style="height: 480px; ">
</div>

<script type="text/javascript">

var fixed = true;

// create an array with nodes
var nodes = new vis.DataSet([
<?php
  // write nodes
  foreach ($nodeList as $node) {
    print nodeString($node);
  }
?>
]);

// create an array with edges
var edges = new vis.DataSet([
<?php
  // write edges
  foreach ($edgeList as $edge) {
    print edgeString($edge);
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
  },
  physics: {
    solver: "forceAtlas2Based",
    forceAtlas2Based: {
      springLength: 100
    },
    minVelocity: 0.75
  }
};
var network = new vis.Network(container, data, options);

// turn off geo-locations on load; this results in a loose network, but stil with some geographical representation
toggleFixed(document.getElementById('btnToggleFixed'));

// custom actions

// go to link on double click
network.on('doubleClick', function (params) {
  thisNode = params.nodes[0];
  if (thisNode != undefined) {
    url = data.nodes.get(thisNode).url;
    if (url != undefined) window.location.href = url;
  }
});

// toggle fixed geo-locations
function toggleFixed(el) {
  if (fixed) {
    nodes.forEach(fixedOff);
    fixed = false;
    el.innerHTML = 'Geo-locations: off';
  }
  else {
    nodes.forEach(fixedOn);
    fixed = true;
    el.innerHTML = 'Geo-locations: on';
    redraw();
  }
}
function fixedOn(thisNode) {
  if (thisNode.category = 'place') {
    nodes.update({ id: thisNode.id, fixed: { x: true, y: true } });
  }
}
function fixedOff(thisNode) {
  if (thisNode.category = 'place') {
    nodes.update({ id: thisNode.id, fixed: { x: false, y: false } });
  }
}

function redraw() {
  network = new vis.Network(container, data, options);
}

function fullScreen(el) {
  if (el.requestFullscreen) el.requestFullscreen();
  else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen(); /* Safari */
  else if (el.msRequestFullscreen) { el.msRequestFullscreen() /* IE11 */  }

}

// when loaded, turn off slow load warning after 
network.on("stabilizationIterationsDone", function () {
  document.getElementById("slowLoadWarning").classList.add("d-none");
});

</script>

<?php
}

// take coords string and return x, y values, adjusted for graph canvas
function processCoords($strCoords) {
  $coords = explode(',', $strCoords);
  if (sizeof($coords) == 2) {
    $x = $coords[1] * 150;
    $y = ($coords[0] * -200) + 9400;
  }
  else {
    $x = $y = null;
  }
  return array($x, $y);
}

// return a JavaScript object sting for each node
function nodeString($node) {
  switch($node[4]) {
    case 'ms':
      $str = '{
        id: "' . $node[0] . '", 
        label: "' . $node[0]  . '", 
        title: "' . $node[1]  . '", 
        shape: "circle", 
        color: "darkred", 
        url: "/' . $node[0] . '",
        category: "ms"
      },' . "\n";
      break;
    case 'place':
    case 'region':
      if ($node[4] == 'region') $fontSize = 45;
      else $fontSize = 30;
      $str = '{
        id: "' . $node[0] . '", 
        label: "' . $node[1]  . '", 
        shape: "box", 
        color: "green", 
        url: "/places/' . $node[0] . '",
        x: ' . $node[2] . ',
        y: ' . $node[3] . ',
        fixed: { x: true, y: true },
        category: "place",
        font: { size: ' . $fontSize . '}
      },' . "\n";
      break;
    case 'library':
        $str = '{
          id: "' . $node[0] . '", 
          label: "' . $node[1]  . '", 
          shape: "box", 
          color: "indianred", 
          url: "/library/' . substr($node[0], strlen('library_')) . '",
          x: ' . $node[2] . ',
          y: ' . $node[3] . ',
          fixed: { x: true, y: true },
          category: "place"
        },' . "\n";
        break;
    default:
      $str = '';
  }
  return $str;

}

// return a JavaScript object string for each edge
function edgeString($edge) {
  if ($edge[3] < 1) $label = '?';
  else $label = '';

  switch($edge[2]) {
    case 'origin':
      $str = '{
        from: "' . $edge[0] . '", 
        to: "' . $edge[1] . '",
        label: "' . $label . '",
        arrows: "from", 
        color: { color: "rgba(0, 0, 0, ' . $edge[3] . ')" }, 
        width: 2
      },' . "\n";
      break;
    case 'prov':
      $str = '{
        from: "' . $edge[0] . '", 
        to: "' . $edge[1] . '",
        label: "' . $label . '",
        arrows: "to", 
        color: { color: "rgba(0, 0, 204, ' . $edge[3] . ')" }, 
        width: 2
      },' . "\n";
      break;
    case 'place_parent':
      $str = '{
        from: "' . $edge[0] . '", 
        to: "' . $edge[1] . '",
        label: "' . $label . '",
        arrows: "from", 
        color: { color: "rgba(150, 255, 150, 25)" }, 
        width: 8
      },' . "\n";
      break;
    case 'library':
      $str = '{
        from: "' . $edge[0] . '", 
        to: "' . $edge[1] . '",
        arrows: "to", 
        color: "indianred", 
        dashes: true,
        width: 2
      },' . "\n";
      break;
    default: // hidden edges
      $str = '{ 
        from: "' . $edge[0] . '", 
        to: "' . $edge[1] . '",
        hidden: true
      },' . "\n";
    }
  return $str;
}

?>