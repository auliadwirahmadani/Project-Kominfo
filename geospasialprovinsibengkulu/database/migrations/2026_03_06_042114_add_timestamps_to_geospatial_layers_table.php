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
        // Gunakan nama tabel 'geospatial_layers' (dengan S)
        if (Schema::hasTable('geospatial_layers')) {
            
            Schema::table('geospatial_layers', function (Blueprint $table) {
                
                // Tambah created_at jika belum ada
                if (!Schema::hasColumn('geospatial_layers', 'created_at')) {
                    $table->timestamp('created_at')->nullable()->after('file_mime');
                }
                
                // Tambah updated_at jika belum ada
                if (!Schema::hasColumn('geospatial_layers', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable()->after('created_at');
                }
            });

        } else {
            // Backup: Jika ternyata di DB kamu namanya tidak pakai S (geospatial_layer)
            if (Schema::hasTable('geospatial_layer')) {
                Schema::table('geospatial_layer', function (Blueprint $table) {
                    if (!Schema::hasColumn('geospatial_layer', 'created_at')) {
                        $table->timestamp('created_at')->nullable()->after('file_mime');
                    }
                    if (!Schema::hasColumn('geospatial_layer', 'updated_at')) {
                        $table->timestamp('updated_at')->nullable()->after('created_at');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cek di kedua kemungkinan nama tabel saat rollback
        $tables = ['geospatial_layers', 'geospatial_layer'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'created_at')) {
                        $table->dropColumn('created_at');
                    }
                    if (Schema::hasColumn($tableName, 'updated_at')) {
                        $table->dropColumn('updated_at');
                    }
                });
            }
        }
    }
};