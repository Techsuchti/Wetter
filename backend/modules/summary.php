<?php
require_once __DIR__.'/../../config.php';
$lat=floatval($_GET['lat']??50.9);
$lon=floatval($_GET['lon']??6.9);
$eps=json_decode(file_get_contents(__DIR__.'/ensemble_icon.php?lat='.$lat.'&lon='.$lon),true);
$risk=min(100,round(($eps['cape_median']/30)+($eps['shear_median']/2))); // simple Formel
header('Content-Type: application/json');
echo json_encode([
  'risk'=>$risk,
  'text'=>"CAPE ".round($eps['cape_median'])." – Shear ".round($eps['shear_median'])." m/s"
]);