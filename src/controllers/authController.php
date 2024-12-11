<?php

require_once __DIR__ . '/../models/auth.model.php';
require_once __DIR__ . '/../config/Connection.php';
require_once __DIR__ . '/../../vendor/autoload.php'; // Ensure this is included to autoload classes

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController
{
    private $authModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
    }

    public function login($request)
    {
        $username = $request['username'] ?? null;
        $password = $request['password'] ?? null;

        if (!$username || !$password) {
            return json_encode(['status' => 'error', 'message' => 'Missing username or password']);
        }

        $response = $this->authModel->login($username, $password);
        return json_encode($response);
    }

    public function logout()
    {
        $headers = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? null;
    
        if ($authHeader) {
            $replace = str_replace('Bearer ', '', $authHeader);
    
            try {
                $decoded = JWT::decode($replace, new Key(SECRET_KEY, 'HS256'));
                $user_id = $decoded->data->user_id;
    
                $authModel = new AuthModel();
                if ($authModel->endSession($user_id)) {
    
                    if ($authModel->blacklistToken($replace)) {
                        return json_encode(['status' => 'success', 'message' => 'Logout successful.']);
                    }
                }
            } catch (Exception $e) {
                return json_encode(['status' => 'error', 'message' => 'Invalid token: ' . $e->getMessage()]);
            }
        }
    
        return json_encode(['status' => 'success', 'message' => 'Logged out successfully.']);
    }
}