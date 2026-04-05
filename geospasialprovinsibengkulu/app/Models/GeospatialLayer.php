<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeospatialLayer extends Model
{
    use HasFactory;

    protected $table = 'geospatial_layer';
    protected $primaryKey = 'geospatial_id';
    
    protected $fillable = [
        'category_id',
        'layer_name',
        'description',
        'geometry',
        'is_published',
        'status_verifikasi',
        'id',
        'file_path',
        'file_original_name',
        'file_type',
        'file_size',
        'file_mime',
        'user_id',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi ke Category
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // ✅ TAMBAHKAN RELASI INI (sesuai struktur MetadataLayer Anda)
    public function metadata()
    {
        // hasOne karena satu layer punya satu metadata
        return $this->hasOne(MetadataLayer::class, 'geospatial_id', 'geospatial_id');
    }
}