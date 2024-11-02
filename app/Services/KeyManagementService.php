<?php

namespace App\Services;

use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\AES;

class KeyManagementService
{
    public function generateKeyPair()
    {
        $rsa = RSA::createKey(2048);
        return [
            'private_key' => $rsa->toString('PKCS1'),
            'public_key' => $rsa->getPublicKey()->toString('PKCS1')
        ];
    }

    public function generateSymmetricKey()
    {
        return random_bytes(32); // 256-bit key
    }

    public function encryptFile($data, $key)
    {
        $cipher = new AES('gcm');
        $cipher->setKey($key);

        $iv = random_bytes(16);
        $cipher->setNonce($iv);

        $ciphertext = $cipher->encrypt($data);
        $tag = $cipher->getTag();

        // Combine IV, ciphertext, and tag for storage
        return base64_encode($iv . $ciphertext . $tag);
    }

    public function decryptFile($encryptedData, $key)
    {
        $data = base64_decode($encryptedData);

        // Validasi data
        if (strlen($data) < 32) {
            throw new \Exception("Encrypted data is invalid or corrupted.");
        }

        // Extract IV (16 bytes), ciphertext, and tag (16 bytes)
        $iv = substr($data, 0, 16);
        $tag = substr($data, -16);
        $ciphertext = substr($data, 16, -16);

        $cipher = new AES('gcm');
        $cipher->setKey($key);
        $cipher->setNonce($iv);
        $cipher->setTag($tag);

        $decrypted = $cipher->decrypt($ciphertext);

        if ($decrypted === false) {
            throw new \Exception("Decryption failed.");
        }

        return $decrypted;
    }

    public function encryptWithPublicKey($data, $publicKey)
    {
        $rsa = RSA::loadPublicKey($publicKey);
        return $rsa->encrypt($data);
    }

    public function decryptWithPrivateKey($data, $privateKey)
    {
        $rsa = RSA::loadPrivateKey($privateKey);
        return $rsa->decrypt($data);
    }
}
