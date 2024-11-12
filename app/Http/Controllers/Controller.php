<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    public static function successResponse($data_name, $data, $message,int $statusCode): JsonResponse
    {
        return response()->json([
            $data_name => $data,
            'message' => $message,
        ], $statusCode);
    }
    public static function errorResponse( $message, $statusCode)
    {
        return response()->json([
            // 'data' => $data,
            'message' => $message,
        ], $statusCode);
    }
}
