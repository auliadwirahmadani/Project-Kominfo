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
        // Pastikan nama tabel menggunakan 'geospatial_layers' (dengan S)
        if (Schema::hasTable('geospatial_layers')) {
            
            Schema::table('geospatial_layers', function (Blueprint $table) {
                
                // Tambah file_path jika belum ada
                if (!Schema::hasColumn('geospatial_layers', 'file_path')) {
                    $table->string('file_path')->nullable()->after('description');
                }

                // Tambah file_original_name jika belum ada
                if (!Schema::hasColumn('geospatial_layers', 'file_original_name')) {
                    $table->string('file_original_name')->nullable()->after('file_path');
                }

                // Tambah file_type jika belum ada
                if (!Schema::hasColumn('geospatial_layers', 'file_type')) {
                    $table->string('file_type')->nullable()->after('file_original_name');
                }

                // Tambah file_size jika belum ada
                if (!Schema::hasColumn('geospatial_layers', 'file_size')) {
                    $table->integer('file_size')->nullable()->after('file_type');
                }

                // Tambah file_mime jika belum ada
                if (!Schema::hasColumn('geospatial_layers', 'file_mime')) {
                    $table->string('file_mime')->nullable()->after('file_size');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('geospatial_layers')) {
            Schema::table('geospatial_layers', function (Blueprint $table) {
                $table->dropColumn([
                    'file_path',
                    'file_original_name',
                    'file_type',
                    'file_size',
                    'file_mime'
                ]);
            });
        }
    }
};