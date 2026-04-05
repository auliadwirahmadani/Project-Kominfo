<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';
    
    // Ini yang menyelamatkan dari error "categories.id does not exist"
    protected $primaryKey = 'category_id'; 
    
    protected $fillable = [
        'category_name', // Pastikan di database DBeaver nama kolomnya benar-benar 'category_name' (bukan 'name')
        'description',
    ];

    public function geospatialLayers()
    {
        return $this->hasMany(GeospatialLayer::class, 'category_id', 'category_id');
    }
}