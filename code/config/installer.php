<?php

use App\Enums\StatusEnum;

return [

    'app_name'    => "Beepost",
    'software_id' => "O6QIE523BF3NBBO6",
    'version'     => 2.6,

    'cacheFile'   => 'YmVlcG9zdA==',

    'core' => [
        'appVersion' => '2.6',
        'minPhpVersion' => '8.2'
    ],

    'requirements' => [

        'php' => [
            'Core',
            'bcmath',
            'openssl',
            'pdo_mysql',
            'mbstring',
            'tokenizer',
            'json',
            'curl',
            'gd',
            'zip',
            'mbstring',


        ],
        'apache' => [
            'mod_rewrite',
        ],

    ],
    'permissions' => [
        '.env'     => '666',
        'storage'     => '775',
        'bootstrap/cache/'       => '775',
    ],

    'demo_config' => [
        'admin' => [
            'username' => 'admin',
            'password' => '123123',
        ],
        'user' => [
            'email' => 'demo@beepost.com',
            'password' => '123123',
        ]
    ]

];
