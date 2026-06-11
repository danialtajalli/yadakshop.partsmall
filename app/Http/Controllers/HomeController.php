<?php

namespace App\Http\Controllers;

use App\Services\HomePageService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly HomePageService $homePageService,
    ) {}

    public function index(): View
    {
        return view('home.index', $this->homePageService->getHomePageData());
    }
}
