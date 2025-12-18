<?php

return [
    'secret' => env('JWT_SECRET'),
    'algorithm' => env('JWT_ALGO', 'HS256'),
    'ttl' => env('JWT_TTL', 3600),
    'issuer' => env('JWT_ISSUER'),
    'audience' => env('JWT_AUDIENCE'),
    'leeway' => env('JWT_LEEWAY', 0),
];

