<?php
function distanceKm($lat1,$lon1,$lat2,$lon2){
  $d=6371*acos(cos(deg2rad($lat1))*cos(deg2rad($lat2))*
        cos(deg2rad($lon2)-deg2rad($lon1))+sin(deg2rad($lat1))*
        sin(deg2rad($lat2)));
  return round($d,1);
}
function median($a){
  sort($a); $c=count($a); return $c%2?$a[$c/2]:($a[$c/2-1]+$a[$c/2])/2;
}
function readNewestGrib($dir,$pattern,$lat,$lon){
  // gibt neuste Datei zurück und extrahiert per wgrib2
  $f=`find $dir -name "$pattern" | sort -V | tail -1`;
  $tmp=tempnam(sys_get_temp_dir(),'grib'); file_put_contents($tmp,$f);
  exec("wgrib2 $tmp -lon $lon $lat -json 2>/dev/null",$out);
  unlink($tmp);
  return json_decode(implode('',$out),true)[0] ?? [];
}
function getWindUV($lat,$lon){
  $u=readNewestGrib(DWD_WN_U,'*.grib2',$lat,$lon)['UGRD'] ?? 0;
  $v=readNewestGrib(DWD_WN_V,'*.grib2',$lat,$lon)['VGRD'] ?? 0;
  return ['u'=>$u,'v'=>$v];
}