<?php

namespace App\Http\Controllers;

use App\Services\ContactPageService;
use App\Services\PageService;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function __construct(
        private readonly PageService $pageService,
        private readonly ContactPageService $contactPageService,
    ) {}

    public function show(): View
    {
        return view('page.contact', [
            ...$this->pageService->getPageData('contact'),
            ...$this->contactPageService->getContactMeta(),
        ]);
    }
}
