<?php

namespace App\Http\Controllers;

use App\Services\PageService;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(
        private readonly PageService $pageService,
    ) {}

    public function show(string $slug): View
    {
        return view('page.show', $this->pageService->getPageData($slug));
    }
}
