<?php

namespace App\Traits;

trait BaseApiResponse
{
    /**
     * Create a general response format.
     * 
     * The payload key name will automatically be set to `data` or `errors` depending on the status provided.
     * @param bool $status Should be `true` if the response is a success, `false` otherwise. Affects the key name of the response payload.
     * @param string $message A message to be sent with the response.
     * @param mixed $payload The data or errors to be sent with the response, if any.
     * @param int $code The HTTP status code to be sent with the response.
     * @return \Illuminate\Http\JsonResponse
     */
    private function generalResponse(bool $status, ?string $message, mixed $payload = null, int $code = 200) {
        $response = [
            'status' => $status,
            'message' => $message
        ];
        if ($payload) {
            if ($status) {
                $response['data'] = $payload;
            } else {
                $response['errors'] = $payload;
            }
        }
        return response()->json($response, $code);
    }
}
