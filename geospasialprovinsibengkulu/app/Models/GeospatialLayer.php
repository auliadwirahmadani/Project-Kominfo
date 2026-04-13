<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeospatialLayer extends Model
{
    use HasFactory;

    protected $table = 'geospatial_layer';
    
    // Memberitahu Laravel bahwa Primary Key-nya adalah geospatial_id
    protected $primaryKey = 'geospatial_id';
    
    // Disesuaikan persis dengan kolom yang ada di database DBeaver
    protected $fillable = [
        'user_id',
        'layer_name',
        'description',
        'category_id',
        'file_path',
        'status_verifikasi',
        'catatan_verifikator',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi ke Category
    public function category()
    {
        // Parameter ke-3 diubah kembali menjadi category_id
        return $this->belongsTo(Category::class, 'category_id', 'category_id'); 
    }
    
    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Relasi ke MetadataLayer
    public function metadata()
    {
        return $this->hasOne(MetadataLayer::class, 'geospatial_id', 'geospatial_id');
    }
}