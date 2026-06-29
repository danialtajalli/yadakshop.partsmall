<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(
        private readonly SearchService $searchService,
    ) {}

    public function __invoke(Request $request): View
    {
        $query = $request->string('q')->trim()->toString();

        return view('search.index', [
            'query' => $query,
            'parts' => $this->searchService->searchParts($query),
            'title' => $query === '' ? 'جستجو' : 'جستجوی '.$query,
        ]);
    }
}
