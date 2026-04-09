<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    /**
     * Mass Assignable
     * ⚠️ role_name tetap ada jika Anda menyimpannya secara denormalisasi di tabel users
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'role_name', 
        'status'
    ];

    /**
     * Hidden Fields
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * 🔥 Relasi ke tabel roles
     * Pastikan parameter ke-3 adalah primary key di tabel 'roles'
     */
    public function role()
    {
        // Syntax: belongsTo(Model, foreign_key_di_users, primary_key_di_roles)
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    /**
     * 🔥 Accessor: Agar $user->role_name selalu sinkron
     * Jika role_name NULL di DB, ambil dari relasi
     */
    public function getRoleNameAttribute()
    {
        // Selalu ambil dari relasi tabel roles untuk memastikan akurasi (menghindari kerancuan data denormalisasi)
        return $this->role ? $this->role->role_name : 'Pengunjung';
    }
}