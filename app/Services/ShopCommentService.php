<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Shop;

class ShopCommentService
{
    /**
     * @param  array{fullname: string, mobile?: ?string, body: string, rating: int}  $data
     */
    public function store(Shop $shop, array $data): Comment
    {
        return $shop->comments()->create([
            'fullname' => $data['fullname'],
            'mobile' => $data['mobile'] ?? null,
            'body' => $data['body'],
            'rating' => $data['rating'],
            'confirmed' => false,
        ]);
    }
}
