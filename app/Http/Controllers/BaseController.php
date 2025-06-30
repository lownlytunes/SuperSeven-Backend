<?php

namespace App\Http\Controllers;

use App\Helpers\QueryHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class BaseController extends Controller
{
    public const PER_PAGE = 10;

    /**
     * Success response method.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponse($message, $result = null, $opt = [])
    {
        $response = [
            'status' => true,
            'message' => $message,
            'data' => $result,
        ];

        $response += $opt;

        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error = '', $code = 500, $opt = [])
    {
        $error = $error == '' ? 'Unexpected error occurred. Please contact administrator.' : $error;

        $response = [
            'status' => false,
            'message' => $error,
        ];

        $response += $opt;

        return response()->json($response, $code);
    }

    public function sendException(Exception $exception, string $extra = '')
    {
        $response = [
            'status' => false,
            'message' => $exception->getMessage(),
        ];

        Log::error('API EXCEPTION', [
            'message' => $exception->getMessage(),
            'filename' => $exception->getFile(),
            'line' => $exception->getLine(),
            'extra' => $extra,
        ]);
        return response()->json($response, 500);
    }

    /**
     * Callback for search functionality
     *
     * @param  Builder  $query
     * @param  Request  $request
     * @param  array  $targets
     * @return Builder
     */
    public function searchCallback(Builder $query, Request $request, array $targets)
    {
        return QueryHelper::searchCallback($query, $request, $targets);
    }

    /**
     * Callback for filter functionality
     *
     * @param  Builder  $query
     * @param  Request  $request
     * @param  array  $queries
     * @return Builder
     */
    public function filterCallback(Builder $query, Request $request, array $queries)
    {
        return QueryHelper::filterCallback($query, $request, $queries);
    }
}
