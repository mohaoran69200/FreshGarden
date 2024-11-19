<?php

return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'header' => [
        'path' => './assets/js/header/header.js',
        'entrypoint' => true,
    ],
    'form' => [
        'path' => './assets/js/form/form.js',
        'entrypoint' => true,
    ],
    'password' => [
        'path' => './assets/js/togglePassword.js',
        'entrypoint' => true,
    ],
    'address' => [
        'path' => './assets/js/address/address.js',
        'entrypoint' => true,
    ],
    'cookie' => [
        'path' => './assets/js/cookie.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
];
