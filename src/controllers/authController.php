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
    
        if ($response['status'] === 'success' && isset($response['data'])) {

            $user_id = $response['data']['user_id'] ?? null;
            $full_name = $response['data']['full_name'] ?? null;
    
            if ($user_id && $full_name) {
                return json_encode([
                    'status' => 'success',
                    'message' => 'Login successful.',
                    'data' => $response['data'],
                ]);
            }
    
            return json_encode(['status' => 'error', 'message' => 'Missing user details.']);
        }
    
        return json_encode($response);
    }    

    public function logout()
    {

        $authToken = $_COOKIE['auth_token'] ?? null;
    
        if ($authToken) {
            try {

                $decoded = JWT::decode($authToken, new Key(SECRET_KEY, 'HS256'));
                $user_id = $decoded->data->user_id;
    

                if ($this->authModel->endSession($user_id)) {

                    if ($this->authModel->blacklistToken($authToken)) {

                        setcookie('auth_token', '', [
                            'expires' => time() - 3600,
                            'path' => '/',
                            'secure' => true,
                            'httponly' => true,
                            'samesite' => 'Strict'
                        ]);
    
                        return json_encode([
                            'status' => 'success',
                            'message' => 'Logout successful. Session ended and token blacklisted.',
                        ]);
                    }
                }
    
                return json_encode([
                    'status' => 'error',
                    'message' => 'Failed to end the session. Logout incomplete.',
                ]);
            } catch (Exception $e) {
                return json_encode([
                    'status' => 'error',
                    'message' => 'Invalid token: ' . $e->getMessage(),
                ]);
            }
        }
    
        return json_encode([
            'status' => 'error',
            'message' => 'Logout failed. Authentication token is missing.',
        ]);
    }
}
