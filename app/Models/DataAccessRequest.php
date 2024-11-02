<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataAccessRequest extends Model
{
    protected $fillable = [
        'requester_id',
        'owner_id',
        'status',
        'encrypted_key',
        'message',
        'approved_at',
        'expires_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function sharedFiles()
    {
        return $this->hasMany(SharedFile::class, 'request_id');
    }
}
