<?php

require_once __DIR__ . '/../interfaces/response.interface.php';

class ResponseMethods implements ResponseInterface
{
    public function responsePayload($payload, $remarks, $message, $code)
    {
        $status = array(
            "remarks" => $remarks,
            "message" => $message
        );

        http_response_code($code);

        return array(
            "status" => $status,
            "payload" => $payload,
            "timestamp" => date('Y-m-d H:i:s'),
            "prepared_by" => "Code Benders"
        );
    }
    public function notFound()
    {
        http_response_code(403);
        echo json_encode([
            "status" => [
                "remarks" => "error",
                "message" => "Your endpoint does not exist"
            ],
            "timestamp" => date('Y-m-d H:i:s'),
            "prepared_by" => "Code Benders"
        ]);
        exit();
    }
}
