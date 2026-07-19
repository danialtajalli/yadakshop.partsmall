<?php

namespace App\Http\Controllers;

use App\Models\Company;
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

    public function byCompany(Request $request, Company $company): View
    {
        return view('listings.index', $this->directoryListingService->getShopListing($request, $company));
    }

    public function show(string $shop_slug): View
    {
        return view('shop.show', $this->shopProfileService->getProfilePageData($shop_slug));
    }
}
