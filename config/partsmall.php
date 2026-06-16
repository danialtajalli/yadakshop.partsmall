<?php

return [
    'signup_url' => env('PARTSMALL_SIGNUP_URL', '#'),
    'image_url' => 'https://partsmall.ir/panel/assests/uploads/{model_type}/{image_type}/{model_id}/{image_name}',

    'contact' => [
        'image_url' => env('PARTSMALL_CONTACT_IMAGE_URL', 'https://yadak.plus/wp-content/uploads/2026/05/contact.webp'),
        'phone' => env('PARTSMALL_CONTACT_PHONE', '021-33954074'),
        'mobile' => env('PARTSMALL_CONTACT_MOBILE', '0935-7884727'),
        'email' => env('PARTSMALL_CONTACT_EMAIL', 'info@partsmall.ir'),
        'address' => env('PARTSMALL_CONTACT_ADDRESS', 'تهران - بهارستان'),
        'hours' => env('PARTSMALL_CONTACT_HOURS', '۹ صبح الی ۱۷ عصر (پنج‌شنبه‌ها تا ساعت ۱۴)'),
    ],
];
