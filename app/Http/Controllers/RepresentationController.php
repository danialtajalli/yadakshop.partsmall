<?php

namespace App\Http\Controllers;

use App\Services\DirectoryListingService;
use App\Services\RepresentationProfileService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RepresentationController extends Controller
{
    public function __construct(
        private readonly DirectoryListingService $directoryListingService,
        private readonly RepresentationProfileService $representationProfileService,
    ) {}

    public function index(Request $request): View
    {
        return view('listings.index', $this->directoryListingService->getRepresentationListing($request));
    }

    public function show(string $representation_slug): View
    {
        return view('representation.show', $this->representationProfileService->getProfilePageData($representation_slug));
    }
}
