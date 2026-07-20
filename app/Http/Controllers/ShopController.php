<?php

namespace App\Http\Controllers;

use App\Services\DirectoryListingService;
use App\Services\ShopProfileService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function __construct(
        private readonly DirectoryListingService $directoryListingService,
        private readonly ShopProfileService $shopProfileService,
    ) {}

    public function index(Request $request): View
    {   

        return view('listings.index', $this->directoryListingService->getShopListing($request));
    }

    public function show(string $shop_slug): View
    {
        $data = $this->shopProfileService->getProfilePageData($shop_slug);

        $data['isOpen'] = $this->shopProfileService->isOpen($data['shop']);

        return view('shop.show', $data);
        // return view('shop.show', $this->shopProfileService->getProfilePageData($shop_slug));
    }
}
