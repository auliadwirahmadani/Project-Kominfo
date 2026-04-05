<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetadataLayer extends Model
{
    use HasFactory;

    protected $table = 'metadata_layer';
    protected $primaryKey = 'metadata_id';

    // app/Models/MetadataLayer.php
        protected $fillable = [
            'geospatial_id',
            'title',
            'identifier',
            'abstract',
            'source',        // ✅ TAMBAHKAN
            'year',          // ✅ TAMBAHKAN
            'crs',           // ✅ TAMBAHKAN
            'scale',         // ✅ TAMBAHKAN
            'data_type',
            'organization',
            'publication_date',
            'distribution_protocol',
            'distribution_url',
            'keywords',
            'layer_name_service',
            'preview_image'
        ];

    public function geospatial()
    {
        return $this->belongsTo(GeospatialLayer::class, 'geospatial_id');
    }
}
