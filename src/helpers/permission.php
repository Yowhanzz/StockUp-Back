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
        $allowedRoles = ['admin', 'staff']; 
        $authToken = $_COOKIE['auth_token'] ?? null;

        if (!$authToken) {
            return [
                'status' => 'error',
                'message' => 'Authorization token is missing or invalid.',
            ];
        }

        try {
            $decoded = JWT::decode($authToken, new Key(SECRET_KEY, 'HS256'));

            $userRole = $decoded->data->role;

            if (self::isTokenBlacklisted($authToken)) {
                return [
                    'status' => 'error',
                    'message' => 'Token is blacklisted',
                ];
            }

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
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Authorization token is missing or invalid.']);
            exit();
        }

        try {
            $decoded = JWT::decode($authToken, new Key(SECRET_KEY, 'HS256'));
            $role = $decoded->data->role ?? null;

            if (!$role || !in_array($role, $allowedRoles)) {
                http_response_code(403); 
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized role.']);
                exit();
            }

            return true;
        } catch (Exception $e) {
            http_response_code(401); 
            echo json_encode(['status' => 'error', 'message' => 'Invalid token: ' . $e->getMessage()]);
            exit();
        }
    }
}
