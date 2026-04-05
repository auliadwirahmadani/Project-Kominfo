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
        // Gunakan nama tabel jamak 'metadata_layers' (dengan S)
        if (Schema::hasTable('metadata_layers')) {
            
            Schema::table('metadata_layers', function (Blueprint $table) {
                
                // Tambah kolom 'source' jika belum ada
                if (!Schema::hasColumn('metadata_layers', 'source')) {
                    $table->string('source')->nullable()->after('abstract');
                }

                // Tambah kolom 'year' jika belum ada
                if (!Schema::hasColumn('metadata_layers', 'year')) {
                    $table->year('year')->nullable()->after('source');
                }

                // Tambah kolom 'crs' jika belum ada
                if (!Schema::hasColumn('metadata_layers', 'crs')) {
                    $table->string('crs')->default('WGS 84 (EPSG:4326)')->after('year');
                }

                // Tambah kolom 'scale' jika belum ada
                if (!Schema::hasColumn('metadata_layers', 'scale')) {
                    $table->string('scale')->nullable()->after('crs');
                }
            });

        } else {
            // Backup: Jika ternyata di database kamu namanya 'metadata_layer' (tanpa S)
            if (Schema::hasTable('metadata_layer')) {
                Schema::table('metadata_layer', function (Blueprint $table) {
                    if (!Schema::hasColumn('metadata_layer', 'source')) {
                        $table->string('source')->nullable()->after('abstract');
                    }
                    if (!Schema::hasColumn('metadata_layer', 'year')) {
                        $table->year('year')->nullable()->after('source');
                    }
                    if (!Schema::hasColumn('metadata_layer', 'crs')) {
                        $table->string('crs')->default('WGS 84 (EPSG:4326)')->after('year');
                    }
                    if (!Schema::hasColumn('metadata_layer', 'scale')) {
                        $table->string('scale')->nullable()->after('crs');
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
        $tables = ['metadata_layers', 'metadata_layer'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $columns = ['source', 'year', 'crs', 'scale'];
                    foreach ($columns as $column) {
                        if (Schema::hasColumn($tableName, $column)) {
                            $table->dropColumn($column);
                        }
                    }
                });
            }
        }
    }
};