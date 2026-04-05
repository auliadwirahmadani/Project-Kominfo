@extends('layouts.geonav')

@section('content')

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<link rel="stylesheet" href="https://unpkg.com/leaflet-minimap/dist/Control.MiniMap.min.css"/>
<script src="https://unpkg.com/leaflet-minimap/dist/Control.MiniMap.min.js"></script>

<link rel="stylesheet" href="https://unpkg.com/leaflet.locatecontrol/dist/L.Control.Locate.min.css"/>
<script src="https://unpkg.com/leaflet.locatecontrol/dist/L.Control.Locate.min.js"></script>

<link rel="stylesheet" href="https://unpkg.com/leaflet-compass/dist/leaflet-compass.min.css"/>
<script src="https://unpkg.com/leaflet-compass/dist/leaflet-compass.min.js"></script>

<style>
/* =========================
   FIX AGAR MAP MUNCUL DI LARAVEL
========================= */
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
}

main, .py-4 {
    height: 100%;
    padding: 0 !important;
}

/* Map fullscreen */
#map {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 0;
}

/* === STYLING KONTROL UMUM === */
.leaflet-control {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 8px;
    transition: all 0.2s ease;
}

.leaflet-control:hover {
    box-shadow: 0 6px 16px rgba(0,0,0,0.25);
}

.leaflet-top.leaflet-right { top: 90px; right: 20px; }
.leaflet-top.leaflet-right .leaflet-control { margin-bottom: 10px; }
.leaflet-control-zoom a { background-color: #ef4444 !important; color: white !important; width: 40px !important; height: 40px !important; line-height: 40px !important; font-size: 18px !important; }
.leaflet-control-layers-toggle { background-color: #ef4444 !important; background-image: none !important; }
.leaflet-control-locate a { background-color: #3b82f6 !important; color: white !important; width: 40px !important; height: 40px !important; line-height: 40px !important; border-radius: 8px !important; }
.leaflet-top.leaflet-left { top: 90px; left: 20px; }
.leaflet-control-minimap { border-radius: 10px !important; border: 2px solid #ef4444 !important; }
.leaflet-bottom.leaflet-left { left: 20px; bottom: 20px; }
.leaflet-bottom.leaflet-right { right: 20px; bottom: 20px; }
.info-control { background: rgba(31, 41, 55, 0.9); color: white; padding: 12px 16px; border-radius: 10px; font-size: 13px; }
</style>

<div id="map"></div>

<script>
// ==========================================
// 1. DEKLARASI FUNGSI PETA SUPER GLOBAL
// ==========================================
function styleDefault() { return { color: "#b91c1c", weight: 3, fillColor: "#f87171", fillOpacity: 0.6 }; }
function styleHover() { return { color: "#7f1d1d", weight: 4, fillColor: "#ef4444", fillOpacity: 0.8 }; }

window.loadMapData = function(layersData) {
    if (!window.map || !window.allGeoLayers) {
        alert("Sistem Peta sedang dimuat, mohon tunggu sebentar.");
        return;
    }

    // Paksa map untuk me-refresh ukurannya (mencegah peta nge-bug jadi abu-abu)
    window.map.invalidateSize();
    window.allGeoLayers.clearLayers();

    if (!layersData || layersData.length === 0) return;

    layersData.forEach(function(item) {
        if (!item.file_path) return;

        var fileUrl = item.url ? item.url : "{{ asset('storage') }}/" + item.file_path;

        fetch(fileUrl)
            .then(res => {
                if (!res.ok) throw new Error("File GeoJSON tidak ditemukan (Error 404)");
                return res.json();
            })
            .then(data => {
                
                // ====================================================
                // 🚀 SUPER ROBOT AUTO-FIX UNTUK FILE GEOJSON RUSAK DARI QGIS
                // ====================================================
                let validFeatures = [];

                function fixGeometry(geom) {
                    if (!geom || !geom.coordinates) return null;
                    if (!geom.type) {
                        let c = geom.coordinates;
                        // Tebak otomatis tipe polygon berdasarkan kedalaman array
                        if (Array.isArray(c[0]) && Array.isArray(c[0][0]) && Array.isArray(c[0][0][0])) geom.type = "MultiPolygon";
                        else if (Array.isArray(c[0]) && Array.isArray(c[0][0])) geom.type = "Polygon";
                        else if (Array.isArray(c[0])) geom.type = "LineString";
                        else geom.type = "Point";
                    }
                    return geom;
                }

                // Ambil dan perbaiki satu per satu
                if (data.type === "FeatureCollection" && data.features) {
                    data.features.forEach(f => {
                        f.geometry = fixGeometry(f.geometry);
                        if(f.geometry) validFeatures.push(f);
                    });
                } else if (data.type === "Feature") {
                    data.geometry = fixGeometry(data.geometry);
                    if(data.geometry) validFeatures.push(data);
                } else if (data.coordinates) { 
                    // Jika file mentah tanpa feature
                    let fixedGeom = fixGeometry(data);
                    if (fixedGeom) validFeatures.push({ type: "Feature", properties: {}, geometry: fixedGeom });
                }

                // Jika file benar-benar kosong/rusak parah
                if (validFeatures.length === 0) {
                    alert("⚠️ PERINGATAN:\nFile untuk peta '" + item.layer_name + "' format koordinatnya kosong atau tidak dikenali Leaflet.");
                    return;
                }

                // Bungkus kembali ke GeoJSON yang sehat
                let safeGeoJSON = {
                    type: "FeatureCollection",
                    features: validFeatures
                };
                // ====================================================

                // Gambar Peta
                var geoLayer = L.geoJSON(safeGeoJSON, {
                    style: styleDefault,
                    onEachFeature: function(feature, layer) {
                        var props = feature.properties || {};
                        var name = props.NAMOBJ || props.Name || props.name || item.layer_name || "Wilayah Terpilih";

                        layer.bindPopup("<b>" + name + "</b>");

                        layer.on('mouseover', function(e) {
                            if (e.target.setStyle) e.target.setStyle(styleHover());
                            if (e.target.bringToFront) e.target.bringToFront();
                        });

                        layer.on('mouseout', function(e) {
                            if (e.target.setStyle) e.target.setStyle(styleDefault());
                        });

                        layer.on('click', function(e) {
                            if (e.target.getBounds) window.map.fitBounds(e.target.getBounds(), { padding: [50,50] });
                        });
                    }
                });

                window.allGeoLayers.addLayer(geoLayer);

                // Zoom otomatis ke titik koordinat file
                if (window.allGeoLayers.getLayers().length > 0) {
                    window.map.fitBounds(window.allGeoLayers.getBounds(), { padding: [60,60], maxZoom: 12 });
                }
            })
            .catch(error => {
                alert("⚠️ GAGAL MENGUNDUH PETA: \nTidak dapat membaca file untuk '" + item.layer_name + "'.\nDetail: " + error.message);
            });
    });
};

// ==========================================
// 2. INISIALISASI PETA
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    
    var map = L.map('map', { zoomControl: false, attributionControl: false }).setView([-3.8, 102.3], 8);
    window.map = map;

    var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 });
    var satellite = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        subdomains: ['mt0','mt1','mt2','mt3'], maxZoom: 20
    });
    osm.addTo(map);

    L.control.zoom({ position: 'topright' }).addTo(map);
    L.control.layers({ "🗺️ Peta Dasar": osm, "🛰️ Citra Satelit": satellite }, null, { position: 'topright' }).addTo(map);
    L.control.locate({ position: 'topright', flyTo: true, keepCurrentZoomLevel: true, locateOptions: { enableHighAccuracy: true }, showCompass: true }).addTo(map);

    var miniLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
    new L.Control.MiniMap(miniLayer, { toggleDisplay: true, position: 'topleft', width: 150, height: 150, zoomLevelOffset: -5 }).addTo(map);

    L.control.scale({ position: 'bottomleft', metric: true, imperial: false }).addTo(map);
    new L.Control.Compass({ position: 'bottomright', autoActive: true, showDigit: true }).addTo(map);

    var info = L.control({ position: 'bottomright' });
    info.onAdd = function () {
        this._div = L.DomUtil.create('div', 'info-control');
        this._div.innerHTML = `<div style="font-size:11px;margin-bottom:4px;">Gerakkan mouse</div><div><b>🕐</b> <span id="map-time">-</span><br><b>📍</b> <span id="map-coords">Lat: -, Lng: -</span></div>`;
        return this._div;
    };
    info.addTo(map);

    map.on('mousemove', function(e) {
        document.getElementById('map-time').textContent = new Date().toLocaleString('id-ID');
        document.getElementById('map-coords').textContent = `Lat: ${e.latlng.lat.toFixed(6)}, Lng: ${e.latlng.lng.toFixed(6)}`;
    });

    window.allGeoLayers = L.featureGroup().addTo(map);

    var initialLayers = @json($layers ?? []);
    if (initialLayers.length > 0) {
        window.loadMapData(initialLayers);
    }
});
</script>

@endsection