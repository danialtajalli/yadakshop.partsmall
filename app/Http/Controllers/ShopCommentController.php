<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShopCommentRequest;
use App\Models\Shop;
use App\Services\ShopCommentService;
use Illuminate\Http\RedirectResponse;

class ShopCommentController extends Controller
{
    public function __construct(
        private readonly ShopCommentService $shopCommentService,
    ) {}

    public function store(StoreShopCommentRequest $request, string $shop_slug): RedirectResponse
    {
        $shop = Shop::query()->where('slug', $shop_slug)->firstOrFail();

        $this->shopCommentService->store($shop, $request->validated());

        return redirect()
            ->route('shop.profile', $shop_slug)
            ->withFragment('comments')
            ->with('comment_submitted', true);
    }
}
