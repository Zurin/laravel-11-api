<?php

namespace App\Helpers;

class Response
{
    public static function success($data = null, $message = 'ok', $status = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public static function error($message = 'Internal Server Error', $status = 500)
    {
        return response()->json([
            'message' => $message
        ], $status);
    }
}
