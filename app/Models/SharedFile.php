<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SharedFile extends Model
{
    protected $fillable = ['request_id', 'encrypted_file_id'];

    public function request()
    {
        return $this->belongsTo(DataAccessRequest::class, 'request_id');
    }

    public function encryptedFile()
    {
        return $this->belongsTo(EncryptedFile::class, 'encrypted_file_id');
    }
}
