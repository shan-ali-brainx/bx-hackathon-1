<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateTasksRequest;
use App\Services\ClientBriefParser;
use Illuminate\Http\JsonResponse;
use Throwable;

class GenerateTasksController extends Controller
{
    public function __invoke(GenerateTasksRequest $request, ClientBriefParser $parser): JsonResponse
    {
        try {
            $result = $parser->parse($request->validated('brief'));

            return response()->json([
                'success' => true,
                'message' => 'Tasks generated successfully.',
                'data' => $result->toArray(),
            ]);
        } catch (Throwable $e) {
            report($e);

            $payload = [
                'success' => false,
                'message' => 'Task generation failed. Please try again later.',
            ];

            if (config('app.debug')) {
                $payload['error'] = $e->getMessage();
            }

            return response()->json($payload, 503);
        }
    }
}
