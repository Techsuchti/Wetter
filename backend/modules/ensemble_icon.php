<?php
require_once __DIR__.'/../../config.php';
$lat=floatval($_GET['lat']??50.9);
$lon=floatval($_GET['lon']??6.9);
$runs=[];
for($m=0;$m<20;$m++){
  $c=readNewestGrib(DWD_EPS."member_$m/","CAPE_*_D2.grib2",$lat,$lon)['CAPE']??0;
  $s=readNewestGrib(DWD_EPS."member_$m/","Shear-*_D2.grib2",$lat,$lon)['Shear-0-6km']??0;
  $runs[]=['cape'=>$c,'shear'=>$s];
}
$capes=array_column($runs,'cape'); $shears=array_column($runs,'shear');
header('Content-Type: application/json');
echo json_encode([
  'cape_median'=>median($capes),
  'cape_min'=>min($capes),
  'cape_max'=>max($capes),
  'shear_median'=>median($shears)
]);