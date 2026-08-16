<?php

return [
    'site_key' => env('ARCAPTCHA_SITE_KEY', ''),

    'secret_key' => env('ARCAPTCHA_SECRET_KEY', ''),

    'verify_exception_value' => env('ARCAPTCHA_VERIFY_EXCEPTION_VALUE', false),
];
