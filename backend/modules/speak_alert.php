<?php
require_once __DIR__.'/../../config.php';
$lat=floatval($_GET['lat']??50.9);
$lon=floatval($_GET['lon']??6.9);
// Dummy-Daten – ersetze durch echte Werte aus DB/Cache
$risk=80; $dir='Süd-West'; $dist=8;
header('Content-Type: text/plain; charset=utf-8');
echo "Achtung! Tornado-Wahrscheinlichkeit $risk %. 
      Gewitterzelle $dist km Richtung $dir. Halten Sie sich bereit!";