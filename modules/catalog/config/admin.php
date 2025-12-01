<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Panel Credentials
    |--------------------------------------------------------------------------
    |
    | Configure the credentials for the admin panel authentication.
    | In production, you should set these via environment variables:
    | ADMIN_USERNAME and ADMIN_PASSWORD
    |
    | The password can be a plain text or a bcrypt hash (starting with $2y$)
    | To generate a hash: php artisan tinker -> Hash::make('your-password')
    |
    */

    'username' => env('ADMIN_USERNAME', 'admin'),
    
    'password' => env('ADMIN_PASSWORD', null), // null = use default 'admin123' in non-production
    
    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    */
    
    'session_lifetime' => env('ADMIN_SESSION_LIFETIME', 120), // minutes
];
