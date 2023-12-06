<?php

return [
    'GRANT_TYPE' => 'password',
    'CLIENT_ID' => env('KINGUIN_CLIENT_ID'),
    'CLIENT_SECRET' => env('KINGUIN_CLIENT_SECRET'),
    'USERNAME' => env('KINGUIN_USERNAME'),
    'PASSWORD' => env('KINGUIN_PASSWORD'),
    'X_AUTH_TOKEN' => env('KINGUIN_X_AUTH_TOKEN', 'b24fe5147e3b23fc1d22fcbf78d8f403'),
    'KINGUIN_HEADRE_KEY' => env('KINGUIN_HEADRE_KEY', 'X-Auth-Token'),
];
