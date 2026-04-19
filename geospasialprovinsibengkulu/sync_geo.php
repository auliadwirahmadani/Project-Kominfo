<?php

use App\Models\GeospatialLayer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

$layers = GeospatialLayer::where('file_path', 'LIKE', '%.json')
                         ->orWhere('file_path', 'LIKE', '%.geojson')
                         ->get();
$count = 0;

foreach ($layers as $layer) {
    try {
        $path = storage_path('app/public/' . str_replace('public/', '', $layer->file_path));
        
        if (!file_exists($path)) {
            continue;
        }
        
        $content = file_get_contents($path);
        if (!$content) continue;
        
        $geoJson = json_decode($content, true);
        if (!$geoJson || json_last_error() !== JSON_ERROR_NONE) {
            continue;
        }

        DB::table('layer_features')->where('geospatial_id', $layer->geospatial_id)->delete();

        if (isset($geoJson['features']) && is_array($geoJson['features'])) {
            foreach ($geoJson['features'] as $f) {
                if (empty($f['geometry'])) continue;
                
                DB::insert("INSERT INTO layer_features (geospatial_id, properties, geom, created_at, updated_at) VALUES (?, ?::jsonb, ST_GeomFromGeoJSON(?), NOW(), NOW())", [
                    $layer->geospatial_id,
                    isset($f['properties']) ? json_encode($f['properties']) : null,
                    json_encode($f['geometry'])
                ]);
                $count++;
            }
        } elseif (isset($geoJson['type']) && $geoJson['type'] === 'Feature' && !empty($geoJson['geometry'])) {
            DB::insert("INSERT INTO layer_features (geospatial_id, properties, geom, created_at, updated_at) VALUES (?, ?::jsonb, ST_GeomFromGeoJSON(?), NOW(), NOW())", [
                $layer->geospatial_id,
                isset($geoJson['properties']) ? json_encode($geoJson['properties']) : null,
                json_encode($geoJson['geometry'])
            ]);
            $count++;
        }
    } catch (\Exception $e) {
        echo "Error on layer ID {$layer->geospatial_id}: " . $e->getMessage() . "\n";
    }
}

echo "BERHASIL mensinkronisasi $count titik spasial ke dalam PostgreSQL!\n";
