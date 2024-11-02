<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EncryptedFile extends Model
{
    protected $fillable = [
        'user_id',
        'original_name',
        'stored_name',
        'mime_type',
        'file_size',
        'encryption_algorithm',
        'encryption_key',
        'encryption_iv',
        'encryption_time',
        'decryption_time',
        'file_type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sharedFiles()
    {
        return $this->hasMany(SharedFile::class, 'encrypted_file_id');
    }
}
