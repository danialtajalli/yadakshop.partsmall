<?php

namespace App\Http\Controllers;

use App\Services\DirectoryListingService;
use App\Services\RepairShopProfileService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RepairShopController extends Controller
{
    public function __construct(
        private readonly DirectoryListingService $directoryListingService,
        private readonly RepairShopProfileService $repairShopProfileService,
    ) {}

    public function index(Request $request): View
    {
        return view('listings.index', $this->directoryListingService->getRepairShopListing($request));
    }

    public function show(string $repair_shop_slug): View
    {
        return view('repair-shop.show', $this->repairShopProfileService->getProfilePageData($repair_shop_slug));
    }
}
