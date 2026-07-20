<?php

return [
    'signup_url' => env('PARTSMALL_SIGNUP_URL', '#'),
    'image_url' => 'panel/assets/uploads/{model_type}/{image_type}/{model_id}/{image_name}',

    'contact' => [
        'image_url' => '/img/contact.webp',
        'phone' => env('PARTSMALL_CONTACT_PHONE', '021 77 222 4 99'),
        'mobile' => env('PARTSMALL_CONTACT_MOBILE', '0935-7884727'),
        'email' => env('PARTSMALL_CONTACT_EMAIL', 'info@partsmall.ir'),
        'address' => env('PARTSMALL_CONTACT_ADDRESS', 'تهران - بهارستان'),
        'hours' => env('PARTSMALL_CONTACT_HOURS', '۹ صبح الی ۱۷ عصر (پنج‌شنبه‌ها تا ساعت ۱۴)'),
    ],

    'floating_call' => [
        'display' => env('PARTSMALL_FLOATING_CALL_DISPLAY', '021 77 222 4 99'),
        'tel' => env('PARTSMALL_FLOATING_CALL_TEL', '02177222499'),
    ],

    'theme_color' => '#f27c22',

    /*
     | Shop IDs shown in the home "best shops" showcase banner.
     */
    'home_best_shop_ids' => [1, 2, 3],

    /*
     | Parallelogram category cards shown above the best-shops banner.
     */
    'home_part_categories' => [
        [
            'title' => 'قطعات موتوری',
            'parts' => ['میللنگ', 'واشر کامل', 'اویل پمپ'],
        ],
        [
            'title' => 'قطعات بدنه',
            'parts' => ['سپر جلو', 'سپر عقب', 'چراغ جلو'],
        ],
        [
            'title' => 'قطعات مصرفی',
            'parts' => ['شمع', 'فیلتر هوا', 'فیلتر روغن', 'تسمه دینام'],
        ],
    ],

    /*
     | Social-proof / trust metrics shown on the home page stats strip.
     | Update values here when the live figures change.
     */
    'home_stats' => [
        [
            'value' => 4500,
            'suffix' => '+',
            'label' => 'فروشگاه عضو',
        ],
        [
            'value' => 850000,
            'suffix' => '+',
            'label' => 'قطعه ثبت‌شده',
        ],
        [
            'value' => 1800,
            'suffix' => '',
            'label' => 'میانگین خرید روزانه',
            'live' => true,
        ],
        [
            'value' => 350,
            'suffix' => '+',
            'label' => 'برند خودرو',
        ],
    ],
];
