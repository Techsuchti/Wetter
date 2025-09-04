/* ==========  LAYER-MENÜ  ========== */
let radarAnim   = null;   // Radar-Loop-Interval
let radarLayer  = null;   // Radar-Tile-Layer
let lightningInt= null;   // Blitz-Refresh
let nowcastInt  = null;   // Nowcast-Track-Loop
let lightningLayerGroup = L.layerGroup(); // Blitze
let nowcastLayerGroup   = L.layerGroup(); // Tracks

/* ==========  KARTE  ========== */
const map = L.map('map').setView([USER_LAT, USER_LON], 10);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; OSM'
}).addTo(map);

/* ==========  RADAR-ANIMATION  ========== */
function buildRadarFrames() {
  const now = Math.floor(Date.now() / 1000);
  const frames = [];
  for (let i = 0; i <= 24; i++) {           // 24 × 5 min = 2 h
    frames.push(`https://tilecache.rainviewer.com/v2/radar/${now - (23 - i) * 300}/256/{z}/{x}/{y}/2/1_1.png`);
  }
  return frames;
}

function startRadarAnim() {
  stopRadarAnim();                                    // altes Zeug weg
  const frames = buildRadarFrames();
  let idx = 0;
  radarLayer = L.tileLayer(frames[0], {opacity: 0.6, attribution: 'Radar &copy; RainViewer'});
  radarLayer.addTo(map);
  radarAnim = setInterval(() => {
    idx = (idx + 1) % frames.length;
    radarLayer.setUrl(frames[idx]);
  }, 500);   // 0,5 s
}

function stopRadarAnim() {
  if (radarAnim) { clearInterval(radarAnim); radarAnim = null; }
  if (radarLayer) { map.removeLayer(radarLayer); radarLayer = null; }
}

/* ==========  LIVE-BLITZE + ANIMATION  ========== */
function startLightning() {
  if (lightningInt) clearInterval(lightningInt);
  lightningInt = setInterval(async () => {
    const data = await fetch('https://blitzortung.org/live/lightning.json').then(r => r.json());
    data.forEach(strike => {
      const age = Date.now() - strike.time;
      if (age > 300000) return; // nur letzte 5 min
      const circle = L.circle([strike.lat, strike.lon], {
        color: 'yellow', fillColor: 'white',
        fillOpacity: 1 - age / 300000,
        radius: Math.max(3000, 10000 - age / 30) // schrumpft
      }).addTo(lightningLayerGroup);
      setTimeout(() => circle.remove(), 300000);
    });
  }, 5000); // alle 5 s neue Blitze
}

/* ==========  NOWCAST-TRACK-ANIMATION  ========== */
function startNowcastAnim() {
  if (nowcastInt) clearInterval(nowcastInt);
  fetch(`backend/modules/nowcast_track.php?lat=${USER_LAT}&lon=${USER_LON}`)
    .then(r => r.json())
    .then(track => {
      const clr = chroma.scale(['lime', 'red']).mode('lab');
      let idx = 0;
      nowcastInt = setInterval(() => {
        if (idx >= track.length - 1) { idx = 0; nowcastLayerGroup.clearLayers(); }
        L.polyline([track[idx], track[idx + 1]], {
          color: clr((idx + 1) / track.length).hex(), weight: 4
        }).addTo(nowcastLayerGroup);
        idx++;
      }, 300); // 0,3 s
    });
}

/* ==========  SATELLIT  ========== */
const satLayer = L.tileLayer.wms("https://eumetview.eumetsat.int/geoserver/wms", {
  layers: 'msg_ir108', format: 'image/png', transparent: true, opacity: 0.5
});

/* ==========  CHECKBOX-STEUERUNG  ========== */
document.getElementById('radar').addEventListener('change', e => {
  e.target.checked ? startRadarAnim() : stopRadarAnim();
});
document.getElementById('lightning').addEventListener('change', e => {
  if (e.target.checked) { lightningLayerGroup.addTo(map); startLightning(); }
  else { map.removeLayer(lightningLayerGroup); clearInterval(lightningInt); }
});
document.getElementById('supercell').addEventListener('change', e => {
  document.querySelectorAll('.supercell-circle').forEach(c => c.style.display = e.target.checked ? 'block' : 'none');
});
document.getElementById('satellite').addEventListener('change', e => {
  e.target.checked ? satLayer.addTo(map) : map.removeLayer(satLayer);
});

/* ==========  START: WAS ANGEHAKT IST  ========== */
if (document.getElementById('radar').checked)   { startRadarAnim(); }
if (document.getElementById('lightning').checked) { lightningLayerGroup.addTo(map); startLightning(); }
if (document.getElementById('supercell').checked) { /* Kreise bleiben sichtbar */ }
if (document.getElementById('satellite').checked) { satLayer.addTo(map); }

/* ==========  SUPERZELLEN (aus PHP)  ========== */
SUPERCELLS.forEach(c => {
  L.circle([c.lat, c.lon], {
    color: c.score > 70 ? 'red' : 'orange',
    fillOpacity: 0.3, radius: 3000,
    className: 'supercell-circle'
  }).bindPopup(`Score ${c.score} | ${c.dist} km`).addTo(map);
});

/* ==========  GEOLOCATION  ========== */
navigator.geolocation.getCurrentPosition(
  pos => {
    const {latitude, longitude} = pos.coords;
    map.setView([latitude, longitude], 11);
    document.getElementById('status').textContent = `Standort: ${latitude.toFixed(4)} / ${longitude.toFixed(4)}`;
  },
  () => document.getElementById('status').textContent = 'Standort nicht verfüglich – Fallback-Koordinaten verwendet.',
  {enableHighAccuracy: true}
);