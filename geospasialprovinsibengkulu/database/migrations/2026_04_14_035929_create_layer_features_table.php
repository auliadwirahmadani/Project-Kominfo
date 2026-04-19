<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('layer_features', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('geospatial_id');
            $table->jsonb('properties')->nullable();
            $table->timestamps();

            $table->foreign('geospatial_id')
                  ->references('geospatial_id')
                  ->on('geospatial_layer')
                  ->onDelete('cascade');
        });

        // Menambahkan kolom geometry menggunakan perintah PostGIS asli
        // SRID 4326 adalah format standar Lat/Long (WGS84) yang dipakai oleh Leaflet & GeoJSON
        DB::statement("SELECT AddGeometryColumn('public', 'layer_features', 'geom', 4326, 'GEOMETRY', 2);");
        
        // Memastikan tipe datanya bisa menampung segala jenis bentukan spasial
        // Membuat GIST (Generalized Search Tree) Index untuk optimasi performa peta (bounding box)
        DB::statement("CREATE INDEX layer_features_geom_gist ON layer_features USING GIST (geom);");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layer_features');
    }
};
