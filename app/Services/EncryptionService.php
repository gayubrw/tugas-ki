<?php

namespace App\Services;

use Exception;

class EncryptionService
{
    private const ALGORITHMS = [
        'aes' => [
            'method' => 'aes-256-cbc',
            'key_length' => 32,
            'iv_length' => 16,
        ],
        'des' => [
            'method' => 'des-cbc',
            'key_length' => 8,
            'iv_length' => 8,
        ],
        'rc4' => [
            'method' => 'rc4',
            'key_length' => 16,
            'iv_length' => 0,
        ],
    ];

    public function encrypt($data, $algorithm)
    {
        if (!isset(self::ALGORITHMS[$algorithm])) {
            throw new Exception("Unsupported algorithm: {$algorithm}");
        }

        $start = microtime(true);

        $algo = self::ALGORITHMS[$algorithm];
        $key = random_bytes($algo['key_length']);
        $iv = $algo['iv_length'] > 0 ? random_bytes($algo['iv_length']) : null;

        $encrypted = openssl_encrypt(
            $data,
            $algo['method'],
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($encrypted === false) {
            throw new Exception("Encryption failed: " . openssl_error_string());
        }

        $time = microtime(true) - $start;

        return [
            'data' => $encrypted,
            'key' => $key,
            'iv' => $iv,
            'time' => $time
        ];
    }

    public function decrypt($data, $algorithm, $key, $iv = null)
    {
        if (!isset(self::ALGORITHMS[$algorithm])) {
            throw new Exception("Unsupported algorithm: {$algorithm}");
        }

        $start = microtime(true);

        $algo = self::ALGORITHMS[$algorithm];

        $decrypted = openssl_decrypt(
            $data,
            $algo['method'],
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($decrypted === false) {
            $error = openssl_error_string();
            throw new Exception("Decryption failed: {$error}");
        }

        $time = microtime(true) - $start;

        return [
            'data' => $decrypted,
            'time' => $time
        ];
    }
}
