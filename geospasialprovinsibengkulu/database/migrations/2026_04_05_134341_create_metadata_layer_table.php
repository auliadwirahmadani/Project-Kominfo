<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('metadata_layer', function (Blueprint $table) {
            // Primary Key
            $table->id('metadata_id'); 
            
            // Foreign Key
            $table->unsignedBigInteger('geospatial_id');

            // Data Kolom Metadata
            $table->string('title')->nullable();
            $table->string('identifier')->nullable();
            $table->text('abstract')->nullable();
            $table->string('source')->nullable();
            $table->year('year')->nullable();
            $table->string('crs')->nullable();
            $table->string('scale')->nullable();
            $table->string('data_type')->nullable();
            $table->string('organization')->nullable();
            $table->date('publication_date')->nullable();
            $table->string('distribution_protocol')->nullable();
            $table->text('distribution_url')->nullable(); 
            $table->string('keywords')->nullable();
            $table->string('layer_name_service')->nullable();
            $table->string('preview_image')->nullable();
            
            $table->timestamps();

            // Constraint Foreign Key
            $table->foreign('geospatial_id')
                  ->references('geospatial_id')
                  ->on('geospatial_layer')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metadata_layer');
    }
};