<?php

namespace App\Http\Controllers;

use App\Services\DirectoryListingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RepairShopController extends Controller
{
    public function __construct(
        private readonly DirectoryListingService $directoryListingService,
    ) {}

    public function index(Request $request): View
    {
        return view('listings.index', $this->directoryListingService->getRepairShopListing($request));
    }
}
