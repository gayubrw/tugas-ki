<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserKey extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'public_key',
        'private_key'
    ];

    protected $hidden = [
        'private_key', // Menyembunyikan private key dari serialisasi
    ];

    protected $casts = [
        'private_key' => 'encrypted:string', // Menggunakan Laravel's encryption
        'public_key' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor untuk mendapatkan format yang readable
    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at?->format('d F Y H:i:s');
    }

    // Scope untuk mendapatkan key pair aktif user
    public function scopeActive($query, $userId)
    {
        return $query->where('user_id', $userId)
                    ->whereNull('deleted_at')
                    ->latest();
    }
}
