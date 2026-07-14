<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Submission pipeline
    |--------------------------------------------------------------------------
    |
    | Supported values: didar, didar_with_database, database_only
    |
    */

    'pipeline' => env('CONTACT_PIPELINE', 'didar_with_database'),

    'didar' => [
        'base_url' => env('DIDAR_API_BASE', 'https://app.didar.me/api/'),
        'api_key' => env('DIDAR_API_KEY'),
        'owner_username' => env('DIDAR_OWNER_USERNAME'),
        'deal_field_key' => env('DIDAR_DEAL_FIELD_KEY', 'Field_8783_0_1'),
        'verify_ssl' => env('DIDAR_VERIFY_SSL', true),
        'cafile' => env('DIDAR_CURL_CAFILE'),
    ],

    'messages' => [
        'success' => 'پیام شما با موفقیت ثبت شد. به زودی با شما تماس خواهیم گرفت.',
        'failure' => 'ارسال پیام با خطا مواجه شد. لطفاً بعداً دوباره تلاش کنید.',
        'misconfigured' => 'پیکربندی فرم تماس کامل نیست.',
        'session_expired' => 'نشست شما منقضی شده است. لطفاً دوباره تلاش کنید.',
    ],

];
