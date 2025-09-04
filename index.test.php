<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

use GuzzleHttp\Client;
$client = new Client(['timeout' => 8]);

$lat = floatval($_GET['lat'] ?? 50.9);
$lon = floatval($_GET['lon'] ?? 6.9);
define('USER_LAT', $lat);
define('USER_LON', $lon);

$maps = json_decode($client->get(RV_COVERAGE)->getBody(), true);
$latest = end($maps['radar']['past'])['time'] ?? time();
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Tornado-Test</title>
</head>
<body>
  <h1>Test OK</h1>
  <p>Standort: <?= $lat ?> / <?= $lon ?></p>
  <p>Radar-Zeit: <?= $latest ?></p>
</body>
</html>
