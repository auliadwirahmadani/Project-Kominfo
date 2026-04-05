<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $primaryKey = 'role_id';

    public $timestamps = true;

    protected $fillable = [
        'role_name',
        'status'
    ];

    // relasi ke users
    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }
}