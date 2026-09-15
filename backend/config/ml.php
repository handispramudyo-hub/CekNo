<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ML Service URL
    |--------------------------------------------------------------------------
    |
    | URL internal ML Service (FastAPI). Tidak boleh di-expose ke internet.
    |
    */
    'url' => env('ML_SERVICE_URL', 'http://127.0.0.1:8001'),
];