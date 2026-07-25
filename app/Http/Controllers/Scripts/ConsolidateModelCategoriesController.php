<?php

namespace App\Http\Controllers\Scripts;

use App\Http\Controllers\Controller;
use App\Support\Scripts\ConsolidateModelCategories;
use Illuminate\Http\JsonResponse;
use Throwable;

class ConsolidateModelCategoriesController extends Controller
{
    public function __invoke(ConsolidateModelCategories $script): JsonResponse
    {
        abort_unless(
            app()->environment(['local', 'staging']) || request()->boolean('force'),
            403,
            'This script is only available in local/staging.',
        );

        try {
            $result = $script->run();
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Model categories consolidated.',
            ...$result,
        ]);
    }
}
