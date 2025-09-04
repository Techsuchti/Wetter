<?php
// liefert nur Meta-URL – echte Tiles kommen per JS-WMS
header('Content-Type: application/json');
echo json_encode([
  'wms'=>EUMETSAT_WMS,
  'layers'=>[
     'IR10.8'=>'msg_ir108',
     'RGBNight'=>'msg_rgb_night_microphysics'
  ]
]);