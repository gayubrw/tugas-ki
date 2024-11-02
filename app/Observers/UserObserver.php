<?php

namespace App\Observers;

use App\Models\User;
use App\Models\UserKey;
use App\Services\KeyManagementService;

class UserObserver
{
    protected $keyService;

    public function __construct(KeyManagementService $keyService)
    {
        $this->keyService = $keyService;
    }

    public function created(User $user)
    {
        $keys = $this->keyService->generateKeyPair();

        UserKey::create([
            'user_id' => $user->id,
            'public_key' => $keys['public_key'],
            'private_key' => $keys['private_key']
        ]);
    }
}
