<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geospasial_layer', function (Blueprint $table) {
            $table->id('geospatial_id'); // primary key

            $table->string('layer_name'); // nama layer
            $table->text('description')->nullable(); // deskripsi

            $table->unsignedBigInteger('category_id'); // relasi ke categories
            $table->string('file_path')->nullable(); // file shapefile / geojson

            $table->string('status_verifikasi')->default('pending'); 
            $table->boolean('is_published')->default(false);

            $table->timestamps();

            // foreign key
            $table->foreign('category_id')
                  ->references('category_id')
                  ->on('categories')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geospasial_layer');
    }
};