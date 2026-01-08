<?php
/* 
Network graph: common functions
*/

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


?>