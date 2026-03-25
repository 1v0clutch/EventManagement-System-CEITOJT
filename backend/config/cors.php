<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Cross-Origin Resource Sharing (CORS) Configuration
     |--------------------------------------------------------------------------
     |
     | Here you may configure your settings for cross-origin resource sharing
     | or "CORS". This determines what cross-origin operations may execute
     | in web browsers. You are free to adjust these settings as needed.
     |
     | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
     |
     */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

<<<<<<< HEAD
    'allowed_origins' => array_filter(array_merge(
    // Local development origins
    [
=======
    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:5173'),
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
        'http://localhost:5173',
        'http://localhost:5174',
        'http://localhost:3000',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:5174',
        'http://127.0.0.1:3000',
    ],
<<<<<<< HEAD
    // Production origin from env (e.g. https://your-app.vercel.app)
    env('FRONTEND_URL') ? [env('FRONTEND_URL')] : []
)),
=======
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
