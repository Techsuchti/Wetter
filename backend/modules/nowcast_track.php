<?php
require_once __DIR__.'/../../config.php';
$lat0=floatval($_GET['lat']??50.9);
$lon0=floatval($_GET['lon']??6.9);
$steps=30; $dt=60; // 30×1 min
$track=[[$lat0,$lon0]];
for($i=0;$i<$steps;$i++){
  $uv=getWindUV($track[$i][0],$track[$i][1]);
  $lat1=$track[$i][0] + $uv['v']*$dt/111000;
  $lon1=$track[$i][1] + $uv['u']*$dt/(111000*cos(deg2rad($track[$i][0])));
  $track[]=[$lat1,$lon1];
}
header('Content-Type: application/json');
echo json_encode($track);