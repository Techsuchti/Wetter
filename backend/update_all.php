<?php
// frischt die Module-JSONs auf
$lat=50.9;$lon=6.9; // Beispiel – kann dynamisch werden
@file_get_contents(__DIR__.'/modules/nowcast_track.php?lat='.$lat.'&lon='.$lon);
@file_get_contents(__DIR__.'/modules/ensemble_icon.php?lat='.$lat.'&lon='.$lon);