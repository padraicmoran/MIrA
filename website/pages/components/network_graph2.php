<?php
/* 
Network graph
*/
require_once 'network_graph_common.php';

function networkGraph2($results) {
  global $placeInfo, $libraries;
  $showLibraries = true;

  print '<h3 id="network" class="mt-5 pt-2">Network graph</h3>';
  if (sizeof($results) > 50) print '<p id="slowLoadWarning" class="bg-warning rounded py-1 px-3">Large result sets may take several seconds to draw.</p>';

  /* PREPARE DATA
  */

  // set up some blank arrays
  $nodeList = array();    // containing arrays [id, label, x, y, type]
  $edgeList = array();    // containing arrays [from, to, type, weight, label]
  $place_list = array();
  $library_list = array();

  // cycle through MSS in this result set
  foreach($results as $ms) {
    $msID = strval($ms['id']);

    // check origin place(s) for this manuscript
    $checkOriginPlaces = $ms->xpath ('//manuscript[@id="' . $msID  . '"]//origin/place/@id');
    if ($checkOriginPlaces) {
      $originWeight = 1 / (count($checkOriginPlaces));
      foreach ($checkOriginPlaces as $place) {
        // add to place list
        array_push($place_list, strval($place['id'])); 
      }
    }

    // check provenance place(s) for this manuscript
    $checkProvPlaces = $ms->xpath ('//manuscript[@id="' . $msID  . '"]//provenance/place/@id');
    if ($checkProvPlaces) {
      $provWeight = 1 / (count($checkProvPlaces));
      foreach ($checkProvPlaces as $place) {
        // add to list
        array_push($place_list, strval($place['id']));  
      }
    }

    // join origins to provenances
    if ($checkOriginPlaces && $checkProvPlaces) {
      foreach ($checkOriginPlaces as $origin) {
        foreach ($checkProvPlaces as $prov) {
          array_push($edgeList, array(strval($origin['id']), strval($prov['id']), 'origin', ($originWeight + $provWeight) / 2, $msID));
        }
      }
    }

    if ($showLibraries) {
      // check libraries for this manuscript
      $checkLibraries = $ms->xpath ('//manuscript[@id="' . $msID  . '"]//identifier/@libraryID');
      foreach ($checkLibraries as $libID) {
        // add to list
        array_push($library_list, strval($libID));
        // if there is a provenance, add edge from provenance to library
        if ($checkProvPlaces) {
          foreach ($checkProvPlaces as $prov) {
            array_push($edgeList, array(strval($prov['id']), 'library_' . strval($libID), 'prov', $provWeight, $msID));
          }
        }
        // if only origin, add edge from origin to library
        else {
          foreach ($checkOriginPlaces as $origin) {
            array_push($edgeList, array(strval($origin['id']), 'library_' . strval($libID), 'origin', $originWeight, $msID));
          }
        }
        array_push($edgeList, array($msID, 'library_' . strval($libID), 'library', 1, ''));
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
        array_push($edgeList, array($placeID, $placeInfo[$placeID]['parentID'], 'place_parent', 1, ''));
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

<p>Blue arrows indicate primary movement (from place of origin), red arrows secondary movement (from place of provenance).
Medieval places are in green, modern libraries in pink.
Lines are more transparent when there are multiple options.
Double-click any node or edge for more information.
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
    print edgeString2($edge);
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
    enabled: false /* config panel */
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
  edges: {
    font: {
      color: 'black',
      size: 25,
      background: "rgba(255,255,255,0.8)",
      align: "top"
    }
  },
  physics: {
    solver: "forceAtlas2Based",
    forceAtlas2Based: {
      springConstant: 0.01
    },
    minVelocity: 0.75
  }
};
var network = new vis.Network(container, data, options);

// turn off geo-locations on load; this results in a loose network, but stil with some geographical representation
toggleFixed(document.getElementById('btnToggleFixed'));

// custom actions

// go to link on double click
network.on("doubleClick", function (params) {
  if (params.nodes.length > 0) {
    // A node was double-clicked
    let nodeId = params.nodes[0];
    let node = data.nodes.get(nodeId);
    if (node && node.url) {
      window.location.href = node.url;
    }
  } else if (params.edges.length > 0) {
    // An edge was double-clicked
    let edgeId = params.edges[0];
    let edge = data.edges.get(edgeId);
    if (edge && edge.url) {
      window.location.href = edge.url;
    }
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

// return a JavaScript object string for each edge
function edgeString2($edge) {
  $label = $edge[4];
  if ($edge[3] < 1) $label .= '?';
  switch($edge[2]) {
    case 'origin':
      $str = '{
        from: "' . $edge[0] . '", 
        to: "' . $edge[1] . '",
        label: "' . $label . '",
        url: "/' . $edge[4] . '",
        arrows: "to", 
        color: { color: "rgba(50, 50, 200, ' . $edge[3] . ')" }, 
        width: 2
      },' . "\n";
      break;
    case 'prov':
      $str = '{
        from: "' . $edge[0] . '", 
        to: "' . $edge[1] . '",
        label: "' . $label . '",
        arrows: "to", 
        color: { color: "rgba(200, 50, 50, ' . $edge[3] . ')" }, 
        width: 2
      },' . "\n";
      break;
    case 'place_parent':
      $str = '{
        from: "' . $edge[0] . '", 
        to: "' . $edge[1] . '",
        arrows: "from", 
        color: { color: "rgba(150, 255, 150, 25)" }, 
        width: 6
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