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

    window.map.invalidateSize();
    window.allGeoLayers.clearLayers();

    if (!layersData || layersData.length === 0) return;

    layersData.forEach(function(item) {
        if (!item.file_path && !item.url) return;

        var fileUrl;
        if (item.url) {
            fileUrl = item.url;
        } else {
            var cleanPath = item.file_path.replace('public/', '');
            fileUrl = cleanPath.startsWith('storage/') ? "/" + cleanPath : "/storage/" + cleanPath;
        }

        fetch(fileUrl)
            .then(res => {
                if (!res.ok) throw new Error("File GeoJSON tidak ditemukan");
                return res.json();
            })
            .then(data => {
                
                // Auto-Fix Geometry
                let validFeatures = [];
                function fixGeometry(geom) {
                    if (!geom || !geom.coordinates) return null;
                    if (!geom.type) {
                        let c = geom.coordinates;
                        if (Array.isArray(c[0]) && Array.isArray(c[0][0]) && Array.isArray(c[0][0][0])) geom.type = "MultiPolygon";
                        else if (Array.isArray(c[0]) && Array.isArray(c[0][0])) geom.type = "Polygon";
                        else if (Array.isArray(c[0])) geom.type = "LineString";
                        else geom.type = "Point";
                    }
                    return geom;
                }

                if (data.type === "FeatureCollection" && data.features) {
                    data.features.forEach(f => {
                        f.geometry = fixGeometry(f.geometry);
                        if(f.geometry) validFeatures.push(f);
                    });
                } else if (data.type === "Feature") {
                    data.geometry = fixGeometry(data.geometry);
                    if(data.geometry) validFeatures.push(data);
                }

                var geoLayer = L.geoJSON({type: "FeatureCollection", features: validFeatures}, {
                    style: styleDefault,
                    onEachFeature: function(feature, layer) {
                        var props = feature.properties || {};
                        
                        // 1. Ambil data metadata dari database (hasil with('metadata') di Controller)
                        var meta = item.metadata || {}; 
                        
                        // 2. LOGIKA MAPPING OTOMATIS (Mencari nama kolom Inggris vs Indonesia)
                        let valTitle      = meta.judul || meta.title || item.layer_name || "Data Spasial";
                        let valIdentifier = meta.identifier || meta.identifier_peta || '-';
                        let valAbstrak    = meta.abstract || meta.abstrak || meta.abstrak_deskripsi_peta || 'Tidak ada deskripsi tersedia.';
                        let valInstansi   = meta.organization || meta.organisasi || meta.instansi || '-';
                        let valTipeData   = meta.data_type || meta.tipe_data || '-';
                        let valTahun      = meta.year || meta.tahun || '-';
                        let valPublikasi  = meta.publication_date || meta.waktu_publikasi || '-';
                        let valSumber     = meta.source || meta.sumber_data || '-';
                        let valCRS        = meta.crs || meta.sistem_koordinat || '-';
                        let valSkala      = meta.scale || meta.skala || '-';
                        let valProtokol   = meta.distribution_protocol || meta.protokol_distribusi || '-';
                        let valUrlSvc     = meta.distribution_url || meta.url_distribusi || '-';
                        let valLayerSvc   = meta.service_layer_name || meta.nama_layer_service || '-';

                        var regionName = props.NAMOBJ || props.Name || props.name || "Area Terpilih";

                        // ====================================================
                        // ­ƒÜÇ POPUP KONTEN (TEMA MERAH PUTIH)
                        // ====================================================
                        let popupHTML = `
                            <div class="w-full">
                                <div class="metadata-header">
                                    <div style="font-size: 10px; opacity: 0.9; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">Geoportal Metadata</div>
                                    <div style="font-size: 16px; font-weight: 800; line-height: 1.2; margin-top: 2px;">${valTitle}</div>
                                    <div style="font-size: 11px; margin-top: 5px; opacity: 0.8;"><i class="fas fa-map-marker-alt mr-1"></i> Area: ${regionName}</div>
                                </div>
                                
                                <div class="metadata-body custom-scroll">
                                    <div class="metadata-section-title">Abstrak / Deskripsi</div>
                                    <div class="abstrak-box">${valAbstrak}</div>

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

                                    <div class="metadata-section-title">Distribusi & Service</div>
                                    <table class="metadata-table">
                                        <tr><td class="metadata-label">Protokol</td><td class="metadata-value">${valProtokol}</td></tr>
                                        <tr><td class="metadata-label">Layer Svc</td><td class="metadata-value">${valLayerSvc}</td></tr>
                                        <tr><td class="metadata-label">URL</td><td class="metadata-value" style="word-break: break-all; font-size: 10px;">${valUrlSvc}</td></tr>
                                    </table>
                                </div>
                            </div>
                        `;

                        layer.bindPopup(popupHTML, {
                            className: 'red-white-popup',
                            minWidth: 320,
                            maxWidth: 350
                        });

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
                if (window.allGeoLayers.getLayers().length > 0) {
                    window.map.fitBounds(window.allGeoLayers.getBounds(), { padding: [60,60], maxZoom: 12 });
                }
            })
            .catch(error => {
                console.error("Error loading layer:", error);
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
    L.control.layers({ "­ƒù║´©Å Peta Dasar": osm, "­ƒø░´©Å Citra Satelit": satellite }, null, { position: 'topright' }).addTo(map);
    L.control.locate({ position: 'topright', flyTo: true, keepCurrentZoomLevel: true, locateOptions: { enableHighAccuracy: true }, showCompass: true }).addTo(map);

    var miniLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
    new L.Control.MiniMap(miniLayer, { toggleDisplay: true, position: 'topleft', width: 150, height: 150, zoomLevelOffset: -5 }).addTo(map);

    L.control.scale({ position: 'bottomleft', metric: true, imperial: false }).addTo(map);
    new L.Control.Compass({ position: 'bottomright', autoActive: true, showDigit: true }).addTo(map);

    var info = L.control({ position: 'bottomright' });
    info.onAdd = function () {
        this._div = L.DomUtil.create('div', 'info-control');
        this._div.innerHTML = `<div style="font-size:11px;margin-bottom:4px;">Gerakkan mouse</div><div><b>­ƒòÉ</b> <span id="map-time">-</span><br><b>­ƒôì</b> <span id="map-coords">Lat: -, Lng: -</span></div>`;
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
