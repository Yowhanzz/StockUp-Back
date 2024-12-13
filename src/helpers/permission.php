<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/Connection.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Permission
{
    public static function authorizeRole($jwt, array $allowedRoles = [])
    {
        $authToken = $_COOKIE['auth_token'] ?? null;

        if (!$authToken) {
            return [
                'status' => 'error',
                'message' => 'Authorization token is missing or invalid.',
            ];
        }

        try {
            // Decode the token
            $decoded = JWT::decode($authToken, new Key(SECRET_KEY, 'HS256'));

            // Extract user role from the token payload
            $userRole = $decoded->data->role;

            // Check if the role is blacklisted
            if (self::isTokenBlacklisted($authToken)) {
                return [
                    'status' => 'error',
                    'message' => 'Token is blacklisted',
                ];
            }

            // Check if the role matches the allowed roles
            if (in_array($userRole, $allowedRoles)) {
                return [
                    'status' => 'success',
                    'message' => 'Role authorized',
                    'role' => $userRole,
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Unauthorized role',
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Invalid token: ' . $e->getMessage(),
            ];
        }
    }

    public static function isTokenBlacklisted($token)
    {
        $pdo = (new Connection())->connect();

        $sql = "SELECT COUNT(*) AS count FROM token_blacklist WHERE token = :token";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    public static function checkRole($jwt, array $allowedRoles = [])
    {
        $authToken = $_COOKIE['auth_token'] ?? null;

        if (!$authToken) {
            http_response_code(401); // Unauthorized
            echo json_encode(['status' => 'error', 'message' => 'Authorization token is missing or invalid.']);
            exit();
        }

        try {
            // Decode the JWT
            $decoded = JWT::decode($authToken, new Key(SECRET_KEY, 'HS256'));
            $role = $decoded->data->role ?? null;

            // Check if the role exists and is allowed
            if (!$role || !in_array($role, $allowedRoles)) {
                http_response_code(403); // Forbidden
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized role.']);
                exit();
            }

            // Role is authorized
            return true;
        } catch (Exception $e) {
            http_response_code(401); // Unauthorized
            echo json_encode(['status' => 'error', 'message' => 'Invalid token: ' . $e->getMessage()]);
            exit();
        }
    }
}
