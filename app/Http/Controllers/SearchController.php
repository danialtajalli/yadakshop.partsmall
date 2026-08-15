<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use App\Support\MetaDescription;
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
        $results = $this->searchService->search($query);

        return view('search.index', [
            'query' => $query,
            'groups' => $results['groups'],
            'total' => $results['total'],
            'title' => $query === '' ? 'جستجو' : 'جستجوی '.$query,
            'metaDescription' => MetaDescription::search($query),
        ]);
    }
}
