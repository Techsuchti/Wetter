<?php
require_once 'config.php';
require_once 'vendor/autoload.php';
use GuzzleHttp\Client;
$client = new Client(['timeout' => 8]);

$lat = floatval($_GET['lat'] ?? 50.9);
$lon = floatval($_GET['lon'] ?? 6.9);
define('USER_LAT', $lat);
define('USER_LON', $lon);

// Radar-Zeit
$maps = json_decode($client->get(RV_COVERAGE)->getBody(), true);
$latest = end($maps['radar']['past'])['time'] ?? time();

// Superzellen (Fallback falls 404)
$scFile = CACHE_DIR.'sc_'.md5("$lat,$lon").'.json';
if (!file_exists($scFile) || filemtime($scFile) < time()-300) {
    $files = glob(DWD_OPENDATA.'/weather/radar/RV/SC/SC_*.json');
    $scRaw = $files ? json_decode(@file_get_contents($files[0]), true) : ['features'=>[]];
    $out = [];
    foreach ($scRaw['features'] ?? [] as $f) {
        $score = $f['properties']['SC_SCORE'] ?? 0;
        $dist  = distanceKm($lat,$lon,$f['properties']['LAT'],$f['properties']['LON']);
        if ($dist <= ALERT_RADIUS_KM && $score >= SUPERCELL_SCORE_THRESHOLD) {
            $out[] = [
                'score'=>$score,'dist'=>$dist,
                'lat'=>$f['properties']['LAT'],'lon'=>$f['properties']['LON'],
                'dx'=>$f['properties']['DX'],'dy'=>$f['properties']['DY']
            ];
        }
    }
    file_put_contents($scFile, json_encode($out));
}
$supercells = json_decode(file_get_contents($scFile), true);

// Ensemble (Fallback)
$epsFile = CACHE_DIR.'eps_'.md5("$lat,$lon").'.json';
if (!file_exists($epsFile) || filemtime($epsFile) < time()-300) {
    $eps = json_decode(@file_get_contents(__DIR__.'/backend/modules/ensemble_icon.php?lat='.$lat.'&lon='.$lon), true);
    file_put_contents($epsFile, json_encode($eps));
}
$ensemble = json_decode(file_get_contents($epsFile), true);

// Warnungen **deaktiviert** (404-Sicher)
$warnings = [];
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Tornado-Alert Deutschland</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9/dist/leaflet.css"/>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="manifest" href="pwa/manifest.json">
</head>
<body>
<main>
  <h1>🌪 Tornado-Alert (Radius 10 km)</h1>
  <div id="status">GPS wird ermittelt …</div>

  <!-- Layer-Menü -->
  <div id="layerMenu">
    <label><input type="checkbox" id="radar" checked> Radar/Regen</label>
    <label><input type="checkbox" id="lightning" checked> Blitze (Live)</label>
    <label><input type="checkbox" id="supercell" checked> Superzellen</label>
    <label><input type="checkbox" id="satellite"> Satellit (IR)</label>
  </div>

  <div id="map"></div>
</main>

<script>
  const RV_TIME   = <?= $latest ?>;
  const USER_LAT  = <?= json_encode($lat) ?>;
  const USER_LON  = <?= json_encode($lon) ?>;
  const SUPERCELLS = <?= json_encode($supercells) ?>;
  const ENSEMBLE   = <?= json_encode($ensemble) ?>;
</script>
<script src="https://unpkg.com/leaflet@1.9/dist/leaflet.js"></script>
<script src="https://unpkg.com/chroma-js@2.4.2/chroma.min.js"></script>
<script src="assets/app.js"></script>
<script>navigator.serviceWorker.register('pwa/sw.js');</script>
</body>
</html>