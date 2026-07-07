<?php

return [
    // Static service token for the admin API (shared with website_root/admin).
    // Unset = admin API disabled (middleware returns 503, fail-closed).
    'api_token' => env('ADMIN_API_TOKEN'),
];
