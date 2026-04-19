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
/* Silver Modern Theme for Leaflet Controls */
.leaflet-control-zoom a, 
.leaflet-control-layers-toggle, 
.leaflet-control-locate a,
.leaflet-control-layers {
    background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%) !important;
    color: #475569 !important;
    border: 1px solid #cbd5e1 !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
    transition: all 0.3s ease !important;
}
.leaflet-control-zoom a, .leaflet-control-locate a { 
    width: 40px !important; 
    height: 40px !important; 
    line-height: 40px !important; 
    font-size: 18px !important; 
    border-radius: 8px !important; 
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}
/* Memperbaiki radius zoom saat di-group */
.leaflet-control-zoom { background: transparent !important; border: none !important; box-shadow: none !important; }
.leaflet-control-zoom-in { border-bottom-left-radius: 0 !important; border-bottom-right-radius: 0 !important; border-bottom: 1px solid #e2e8f0 !important; }
.leaflet-control-zoom-out { border-top-left-radius: 0 !important; border-top-right-radius: 0 !important; border-top: none !important;}

/* Interaksi Hover Sleek */
.leaflet-control-zoom a:hover, 
.leaflet-control-layers-toggle:hover, 
.leaflet-control-locate a:hover {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
    color: #0f172a !important;
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
}

/* Memperbaiki ikon layers & compass agar tidak kepotong/hitam */
.leaflet-control-layers-toggle { width: 40px !important; height: 40px !important; border-radius: 8px !important; }
.leaflet-top.leaflet-left { top: 90px; left: 20px; }
.leaflet-control-minimap { border-radius: 10px !important; border: 2px solid #ef4444 !important; }
.leaflet-bottom.leaflet-left { left: 20px; bottom: 20px; }
.leaflet-bottom.leaflet-right { right: 20px; bottom: 20px; }
/* Info Control Kursor UI */
.info-control { 
    background: rgba(255, 255, 255, 0.95); 
    backdrop-filter: blur(12px);
    border: 1px solid rgba(226, 232, 240, 0.9);
    padding: 14px 18px; 
    border-radius: 16px; 
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
    font-family: 'Inter', sans-serif;
    min-width: 250px;
}
.info-control-title {
    font-size: 10px; text-transform: uppercase; font-weight: 700; color: #64748b; 
    letter-spacing: 0.08em; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;
}
.info-control-row {
    display: flex; align-items: center; gap: 10px; margin-bottom: 8px; color: #0f172a;
}
.info-control-row:last-child { margin-bottom: 0; }
.info-icon-wrapper {
    width: 26px; height: 26px; border-radius: 8px; display: flex; align-items: center; justify-content: center;
    background: #f1f5f9; color: #dc2626; shrink-0;
}
.info-value { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; font-weight: 600; }

/* =========================
    STYLING CUSTOM POPUP MERAH PUTIH (METADATA)
========================= */
.red-white-popup .leaflet-popup-content-wrapper {
    padding: 0;
    overflow: hidden;
    border-radius: 12px;
    border: 2px solid #dc2626;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
}
.red-white-popup .leaflet-popup-content {
    margin: 0;
    width: 350px !important;
}
.red-white-popup .leaflet-popup-tip {
    background: #dc2626;
}
.red-white-popup a.leaflet-popup-close-button {
    color: white !important;
    top: 12px !important;
    right: 12px !important;
    font-size: 18px !important;
    font-weight: bold;
    z-index: 10;
}

.metadata-header {
    background: #dc2626;
    color: white;
    padding: 15px;
    border-bottom: 4px solid #991b1b;
}

.metadata-body {
    background: white;
    padding: 0;
    max-height: 350px;
    overflow-y: auto;
}

.metadata-section-title {
    background: #fef2f2;
    color: #991b1b;
    padding: 8px 15px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    border-bottom: 1px solid #fee2e2;
}

.metadata-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}

.metadata-table tr {
    border-bottom: 1px solid #f3f4f6;
}

.metadata-table td {
    padding: 10px 15px;
    vertical-align: top;
}

.metadata-label {
    color: #dc2626;
    font-weight: 700;
    width: 35%;
    font-size: 11px;
}

.metadata-value {
    color: #374151;
    font-weight: 500;
    line-height: 1.4;
}

.abstrak-box {
    padding: 12px 15px;
    background: #fff;
    font-size: 12px;
    color: #4b5563;
    line-height: 1.6;
    text-align: justify;
}

.custom-scroll::-webkit-scrollbar {
    width: 6px;
}
.custom-scroll::-webkit-scrollbar-thumb {
    background: #fca5a5;
    border-radius: 10px;
}
</style>

<div id="map"></div>

<script src="https://unpkg.com/shpjs@latest/dist/shp.js"></script>
<script>
// ==========================================
// 1. DEKLARASI FUNGSI PETA SUPER GLOBAL
// ==========================================
function styleDefault() { return { color: "#b91c1c", weight: 3, fillColor: "#f87171", fillOpacity: 0.6 }; }
function styleHover() { return { color: "#7f1d1d", weight: 4, fillColor: "#ef4444", fillOpacity: 0.8 }; }

window.fetchDynamicLayers = function() {
    if (!window.map || !window.allGeoLayers) return;

    var bounds = window.map.getBounds();
    var minLng = bounds.getWest();
    var minLat = bounds.getSouth();
    var maxLng = bounds.getEast();
    var maxLat = bounds.getNorth();

    var categoryId = document.getElementById('filterCategory')?.value ?? '';
    var singleLayerId = window.activeSearchLayerId || '';
    
    // Tampilkan indikator loading jika diperlukan
    var url = `/api/map/features?min_lng=${minLng}&min_lat=${minLat}&max_lng=${maxLng}&max_lat=${maxLat}&category_id=${categoryId}&single_layer_id=${singleLayerId}`;

    var currentFetchId = Date.now();
    window.lastFetchId = currentFetchId;

    fetch(url)
        .then(res => {
            if (!res.ok) throw new Error("Gagal memuat API PostGIS");
            return res.json();
        })
        .then(data => {
            if (window.lastFetchId !== currentFetchId) return; // Abaikan fetch yang overlap/usang
            if (window.isPopupActive) return; // MENCEGAH BUG ASYNC: Jangan reset layer jika user berhasil buka popup sebelum fetch selesai!

            window.allGeoLayers.clearLayers();
            var metadataDict = data.postgis_features?.metadata_dict || {};

            // Helper function for Popup Builder
            function buildPopupHTML(meta, props) {
                let valTitle      = meta.judul || meta.title || "Data Spasial";
                let valIdentifier = meta.identifier || meta.identifier_peta || '-';
                let valAbstrak    = meta.abstract || meta.abstrak || meta.abstrak_deskripsi_peta || 'Tidak ada deskripsi tersedia.';
                let valInstansi   = meta.organization || meta.organisasi || meta.instansi || '-';
                let valTipeData   = meta.data_type || meta.tipe_data || '-';
                let valTahun      = meta.year || meta.tahun || '-';
                let valPublikasi  = meta.publication_date || meta.waktu_publikasi || '-';
                let valSumber     = meta.source || meta.sumber_data || '-';
                let valCRS        = meta.crs || meta.sistem_koordinat || '-';
                let valSkala      = meta.scale || meta.skala || '-';
                
                var regionName = props.NAMOBJ || props.Name || props.name || "Area Spasial";

                let attrRows = '';
                for (const [key, value] of Object.entries(props)) {
                    if (key !== '_geospatial_id' && key !== 'NAMOBJ' && key !== 'Name' && key !== 'name' && value) {
                        // Hilangkan properti internal dan kosong
                        attrRows += `<tr><td class="metadata-label">${key}</td><td class="metadata-value">${value}</td></tr>`;
                    }
                }
                
                let attrSection = attrRows ? `
                    <div class="metadata-section-title">Atribut Spasial Fitur</div>
                    <table class="metadata-table">${attrRows}</table>
                ` : '';

                return `
                    <div class="w-full">
                        <div class="metadata-header">
                            <div style="font-size: 10px; opacity: 0.9; font-weight: bold; text-transform: uppercase;">Geoportal Metadata</div>
                            <div style="font-size: 16px; font-weight: 800; line-height: 1.2; margin-top: 2px;">${valTitle}</div>
                            <div style="font-size: 11px; margin-top: 5px; opacity: 0.8;"><i class="fas fa-map-marker-alt mr-1"></i> Atribut Utama: ${regionName}</div>
                        </div>
                        <div class="metadata-body custom-scroll">
                            <div class="metadata-section-title">Abstrak / Deskripsi</div>
                            <div class="abstrak-box">${valAbstrak}</div>
                            
                            ${attrSection}
                            
                            <div class="metadata-section-title">Informasi Umum</div>
                            <table class="metadata-table">
                                <tr><td class="metadata-label">Identifier</td><td class="metadata-value">${valIdentifier}</td></tr>
                                <tr><td class="metadata-label">Instansi</td><td class="metadata-value">${valInstansi}</td></tr>
                                <tr><td class="metadata-label">Tipe Data</td><td class="metadata-value">${valTipeData}</td></tr>
                                <tr><td class="metadata-label">Tahun</td><td class="metadata-value">${valTahun}</td></tr>
                                <tr><td class="metadata-label">Publikasi</td><td class="metadata-value">${valPublikasi}</td></tr>
                            </table>
                            <div class="metadata-section-title">Detail Teknis</div>
                            <table class="metadata-table">
                                <tr><td class="metadata-label">Sumber Data</td><td class="metadata-value">${valSumber}</td></tr>
                                <tr><td class="metadata-label">CRS</td><td class="metadata-value">${valCRS}</td></tr>
                                <tr><td class="metadata-label">Skala</td><td class="metadata-value">${valSkala}</td></tr>
                            </table>
                        </div>
                    </div>`;
            }

            // 1. RENDER POSTGIS LAYER (MVT/BBOX)
            var pgData = data.postgis_features;
            if (pgData && pgData.features && pgData.features.length > 0) {
                var pgLayer = L.geoJSON(pgData, {
                    style: styleDefault,
                    onEachFeature: function(feature, layer) {
                        var props = feature.properties || {};
                        var geoId = props._geospatial_id;
                        var meta = metadataDict[geoId] || {};
                        layer.bindPopup(buildPopupHTML(meta, props), { className: 'red-white-popup', minWidth: 320, maxWidth: 350 });
                        layer.on({
                            mouseover: function(e) { if(e.target.setStyle) e.target.setStyle(styleHover()); if(e.target.bringToFront) e.target.bringToFront(); },
                            mouseout:  function(e) { if(e.target.setStyle) e.target.setStyle(styleDefault()); }
                        });
                    }
                });
                window.allGeoLayers.addLayer(pgLayer);
                
                if (singleLayerId !== '' && pgLayer.getBounds().isValid()) {
                    window.map.fitBounds(pgLayer.getBounds(), { padding: [30, 30], maxZoom: 14 });
                }
            }

            // 2. RENDER STATIC FALLBACK LAYERS (Bagi data yg ga diconvert ke Postgis)
            if (!window.loadedStaticLayersData) window.loadedStaticLayersData = {};
            
            var staticLayers = data.static_layers || [];
            staticLayers.forEach(layerInfo => {
                var meta = metadataDict[layerInfo.id] || {};
                var lid = layerInfo.id;
                
                // Helper draw component
                function drawToMap(gData) {
                    var lObj = L.geoJSON(gData, {
                        style: styleDefault,
                        onEachFeature: function(f, l) {
                            l.bindPopup(buildPopupHTML(meta, f.properties || {}), { className: 'red-white-popup', minWidth: 320, maxWidth: 350 });
                            l.on({
                                mouseover: function(e) { if(e.target.setStyle) e.target.setStyle(styleHover()); if(e.target.bringToFront) e.target.bringToFront(); },
                                mouseout:  function(e) { if(e.target.setStyle) e.target.setStyle(styleDefault()); }
                            });
                        }
                    });
                    window.allGeoLayers.addLayer(lObj);
                    if (singleLayerId !== '' && lObj.getBounds().isValid()) {
                        window.map.fitBounds(lObj.getBounds(), { padding: [30, 30], maxZoom: 14 });
                    }
                }

                // Gunakan cache layer yang udah sukses keparsing supaya gk render file 80MB lagi kalo cm ngegeser peta dikit!!
                if (window.loadedStaticLayersData[lid]) {
                    let cachedData = window.loadedStaticLayersData[lid];
                    if (Array.isArray(cachedData)) cachedData.forEach(d => drawToMap(d));
                    else drawToMap(cachedData);
                    return;
                }
                
                async function loadStatic() {
                    try {
                        let parsedData;
                        if (layerInfo.type === 'zip') {
                            try {
                                parsedData = await shp(layerInfo.url);
                            } catch (e) {
                                console.warn("shp.js failed to load ZIP for layer", lid, e);
                                return;
                            }
                        } else {
                            const resJson = await fetch(layerInfo.url);
                            parsedData = await resJson.json();
                        }

                        // Simpan ke Cache JS Global
                        window.loadedStaticLayersData[lid] = parsedData;

                        if (Array.isArray(parsedData)) parsedData.forEach(d => drawToMap(d));
                        else drawToMap(parsedData);

                    } catch(err) {
                        console.error('Failed processing static layer', lid, err);
                    }
                }
                
                loadStatic();
            });
        })
        .catch(error => console.error("Error PostGIS BBOX:", error));
};

// Agar kompatibel dengan filter navbar geonav.blade.php
window.loadMapData = function(layersData) {
    // Pada arsitektur baru PostGIS, filter parameters diambil saat fetchDynamicLayers dari DOM (document.getElementById).
    // Sehingga fungsi loadMapData ini cukup me-reload fitur di map.
    window.fetchDynamicLayers();
    
    // Opsional: jika ingin fitBounds ke hasil filter
    if (layersData && layersData.length > 0) {
        // Untuk saat ini biarkan tidak fit bounds karena bisa pindah tiba-tiba, biarkan dinamis
    }
};

// ==========================================
// 2. INISIALISASI PETA
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    
    // Tangkap parameter 'layer_id' dari URL agar map langsung membuka layer tersebut
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('layer_id')) {
        window.activeSearchLayerId = urlParams.get('layer_id');
    }

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
        this._div.innerHTML = `
            <div class="info-control-title">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                Informasi Kursor Peta
            </div>
            <div class="info-control-row">
                <div class="info-icon-wrapper"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg></div>
                <span class="info-value" id="map-time">-</span>
            </div>
            <div class="info-control-row">
                <div class="info-icon-wrapper"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></div>
                <span class="info-value" id="map-coords">-</span>
            </div>
        `;
        return this._div;
    };
    info.addTo(map);

    map.on('mousemove', function(e) {
        document.getElementById('map-time').textContent = new Date().toLocaleString('id-ID');
        document.getElementById('map-coords').textContent = `Lat: ${e.latlng.lat.toFixed(6)}, Lng: ${e.latlng.lng.toFixed(6)}`;
    });

    window.allGeoLayers = L.featureGroup().addTo(map);

    window.isPopupActive = false;
    map.on('popupopen', function() { window.isPopupActive = true; });
    map.on('popupclose', function() { 
        window.isPopupActive = false; 
        window.fetchDynamicLayers(); // Muat ulang posisi terakhir setelah popup ditutup
    });

    // Event Dinamis PostGIS: Ambil data BBOX hanya ketika pergeseran layer berhenti
    // TIDAK dijalankan jika ada popup yang terbuka agar layer tidak ter-reset dan popup hilang
    map.on('moveend', function() {
        if (!window.isPopupActive) {
            window.fetchDynamicLayers();
        }
    });

    // Ambil data pertama kali setelah inisialisasi peta
    // Kasih delay kecil agar load bounds sukses dulu secara internal Leaflet
    setTimeout(() => {
        window.fetchDynamicLayers();
    }, 200);
});
</script>

@endsection
