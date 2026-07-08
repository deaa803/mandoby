<?php

return [
    'credentials' => env('FIREBASE_CREDENTIALS')
        ? storage_path('app/' . env('FIREBASE_CREDENTIALS'))
        : null,

    'database_url' => env('FIREBASE_DATABASE_URL'),
];
