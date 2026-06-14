<?php

namespace App\Http\Controllers;

use App\Services\PartPageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartController extends Controller
{
    public function __construct(
        private readonly PartPageService $partPageService,
    ) {}

    public function show(Request $request, string $part): View
    {
        return view('part.show', $this->partPageService->getPartPageData($part, $request));
    }
}
