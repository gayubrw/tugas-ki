<?php

return [
    'algorithms' => [
        'aes' => [
            'cipher' => 'aes-256-cbc',
            'key_length' => 32,
            'iv_length' => 16,
        ],
        'des' => [
            'cipher' => 'des-cbc',
            'key_length' => 8,
            'iv_length' => 8,
        ],
        'rc4' => [
            'cipher' => 'rc4',
            'key_length' => 16,
        ],
    ],
];
