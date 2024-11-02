<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserKey;
use App\Services\KeyManagementService;
use Illuminate\Console\Command;

class GenerateUserKeys extends Command
{
    protected $signature = 'users:generate-keys';
    protected $description = 'Generate keys for users who don\'t have them';

    protected $keyService;

    public function __construct(KeyManagementService $keyService)
    {
        parent::__construct();
        $this->keyService = $keyService;
    }

    public function handle()
    {
        $users = User::whereDoesntHave('userKey')->get();

        foreach ($users as $user) {
            $keyPair = $this->keyService->generateKeyPair();

            UserKey::create([
                'user_id' => $user->id,
                'public_key' => $keyPair['public_key'],
                'private_key' => $keyPair['private_key']
            ]);

            $this->info("Generated keys for user {$user->email}");
        }

        $this->info('All done!');
    }
}
