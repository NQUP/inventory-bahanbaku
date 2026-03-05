<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; // agar bisa login
use Illuminate\Notifications\Notifiable;

class Supplier extends Authenticatable
{
    use Notifiable;

    protected $table = 'suppliers';

    protected $fillable = ['nama', 'alamat', 'telepon', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    // Relasi ke bahan baku
    public function bahanBakus()
    {
        return $this->hasMany(BahanBaku::class, 'supplier_id');
    }
}
