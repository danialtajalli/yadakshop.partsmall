<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShopCommentRequest;
use App\Models\Shop;
use App\Services\ShopCommentService;
use Illuminate\Http\JsonResponse;

class ShopCommentController extends Controller
{
    public function __construct(
        private readonly ShopCommentService $shopCommentService,
    ) {}

    public function store(StoreShopCommentRequest $request, string $shop_slug): JsonResponse
    {
        $shop = Shop::query()->where('slug', $shop_slug)->firstOrFail();

        $this->shopCommentService->store($shop, $request->validated());

        return response()->json([
            'message' => 'نظر شما با موفقیت ثبت شد و پس از تایید در این صفحه نمایش داده می‌شود.',
        ]);
    }
}
