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
   STYLING CUSTOM POPUP MERAH PUTIH
========================= */
.red-white-popup .leaflet-popup-content-wrapper {
    padding: 0;
    overflow: hidden;
    border-radius: 0.5rem;
    border: 2px solid #dc2626; /* Merah Tailwind */
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
}
.red-white-popup .leaflet-popup-content {
    margin: 0;
    width: auto !important;
}
.red-white-popup .leaflet-popup-tip {
    background: #dc2626; /* Segitiga bawah warna merah */
}
.red-white-popup a.leaflet-popup-close-button {
    color: white !important; /* Tombol X warna putih */
    top: 10px !important;
    right: 10px !important;
    font-size: 16px !important;
    font-weight: bold;
    z-index: 10;
}
.red-white-popup a.leaflet-popup-close-button:hover {
    color: #fca5a5 !important;
}
/* Kustomisasi Scrollbar untuk tabel metadata */
.custom-scroll::-webkit-scrollbar {
    width: 6px;
}
.custom-scroll::-webkit-scrollbar-track {
    background: #f1f1f1; 
}
.custom-scroll::-webkit-scrollbar-thumb {
    background: #fca5a5; 
    border-radius: 4px;
}
.custom-scroll::-webkit-scrollbar-thumb:hover {
    background: #ef4444; 
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

    // Paksa map untuk me-refresh ukurannya (mencegah peta nge-bug jadi abu-abu)
    window.map.invalidateSize();
    window.allGeoLayers.clearLayers();

    if (!layersData || layersData.length === 0) return;

    layersData.forEach(function(item) {
        if (!item.file_path && !item.url) return;

        // ====================================================
        // 🚀 SUPER ROBOT AUTO-FIX UNTUK URL PETA
        // ====================================================
        var fileUrl;
        
        // 1. Jika data dikirim dari Filter/Search (sudah punya properti 'url' matang)
        if (item.url) {
            fileUrl = item.url;
        } 
        // 2. Jika data dikirim dari Loading Awal ($layers mentah)
        else {
            // Kita harus menghapus kata 'public/' jika secara tidak sengaja tersimpan di database
            var cleanPath = item.file_path.replace('public/', '');
            
            // Cek apakah di database sudah ada awalan 'storage/' atau belum
            if (cleanPath.startsWith('storage/')) {
                fileUrl = "/" + cleanPath;
            } else {
                fileUrl = "/storage/" + cleanPath;
            }
        }
        // ====================================================

        fetch(fileUrl)
            .then(res => {
                if (!res.ok) throw new Error("File GeoJSON tidak ditemukan di alamat: " + fileUrl);
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
                        var name = props.NAMOBJ || props.Name || props.name || props.KABUPATEN || props.KECAMATAN || item.layer_name || "Wilayah Terpilih";

                        // ====================================================
                        // 🚀 PEMBUATAN KONTEN POPUP METADATA MERAH PUTIH
                        // ====================================================
                        let tableRows = '';
                        // Looping seluruh atribut/properti dari file GeoJSON
                        for (const [key, value] of Object.entries(props)) {
                            // Hanya tampilkan yang ada isinya (bukan null/kosong)
                            if (value !== null && value !== undefined && value !== '') {
                                tableRows += `
                                    <tr class="border-b border-gray-100 hover:bg-red-50 transition-colors">
                                        <td class="py-2 pr-4 text-xs font-bold text-red-700 uppercase tracking-wider w-1/3 align-top">${key}</td>
                                        <td class="py-2 text-sm text-gray-700 font-medium break-words">${value}</td>
                                    </tr>
                                `;
                            }
                        }

                        let popupHTML = `
                            <div class="w-full min-w-[280px] max-w-[350px]">
                                <!-- Header Merah -->
                                <div class="bg-red-600 text-white p-4 pb-3 rounded-t-lg relative">
                                    <h3 class="font-bold text-base pr-4 leading-tight"><i class="fas fa-map-marker-alt mr-2"></i>${name}</h3>
                                    <p class="text-red-100 text-xs mt-1">Sumber: ${item.layer_name}</p>
                                </div>
                                
                                <!-- Body Putih (Tabel Metadata) -->
                                <div class="bg-white p-4 max-h-[250px] overflow-y-auto custom-scroll">
                                    ${tableRows ? `
                                        <table class="w-full text-left border-collapse">
                                            <tbody>${tableRows}</tbody>
                                        </table>
                                    ` : `
                                        <div class="text-center py-4 text-gray-400">
                                            <i class="fas fa-database text-2xl mb-2"></i>
                                            <p class="text-sm italic">Tidak ada atribut data spasial</p>
                                        </div>
                                    `}
                                </div>
                            </div>
                        `;

                        // Bind popup dengan custom CSS Class
                        layer.bindPopup(popupHTML, {
                            className: 'red-white-popup',
                            minWidth: 280,
                            maxWidth: 350,
                            closeButton: true
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

                // Zoom otomatis ke titik koordinat file
                if (window.allGeoLayers.getLayers().length > 0) {
                    window.map.fitBounds(window.allGeoLayers.getBounds(), { padding: [60,60], maxZoom: 12 });
                }
            })
            .catch(error => {
                alert("⚠️ GAGAL MENGUNDUH PETA: \nTidak dapat membaca file untuk '" + item.layer_name + "'.\nDetail: " + error.message);
                console.error("Path Error:", fileUrl);
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