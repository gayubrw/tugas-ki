<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserData extends Model
{
    protected $fillable = [
        'user_id',
        'file_name',
        'file_path',
        'file_type',
        'encryption_type',
        'encryption_key',
        'encryption_iv',
        'original_name',
        'file_size',
        'processing_time'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
