<?php
define('CACHE_DIR', __DIR__.'/cache/');
define('ALERT_RADIUS_KM', 10);
define('SUPERCELL_SCORE_THRESHOLD', 50);
define('DWD_OPENDATA', 'https://opendata.dwd.de');
define('DWD_CAP', DWD_OPENDATA.'/weather/warnings/gemeinden/warnings.json');
define('RV_COVERAGE', 'https://api.rainviewer.com/public/weather-maps.json');
define('DWD_WN_U', DWD_OPENDATA.'/weather/radar/RV/WN/u/');
define('DWD_WN_V', DWD_OPENDATA.'/weather/radar/RV/WN/v/');
define('DWD_EPS', DWD_OPENDATA.'/weather/nwp/icon-d2-eps/grib/');
define('EUMETSAT_WMS', 'https://eumetview.eumetsat.int/geoserver/wms');
require_once __DIR__.'/backend/modules/helpers.php';
