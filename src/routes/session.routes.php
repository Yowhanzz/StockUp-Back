<?php

require_once __DIR__ . '/../models/session.model.php';
require_once __DIR__ . '/../helpers/permission.php';
require_once __DIR__ . '/../models/response.model.php';
require_once __DIR__ . '/../config/Connection.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Initialize necessary models
$sessionModel = new SessionModel();
$response = new ResponseMethods();

// Extract JWT from cookies
$jwt = $_COOKIE['auth_token'] ?? null;

if (!$jwt) {
    echo json_encode(['status' => 'error', 'message' => 'Authentication token not found.']);
    exit();
}

function decodeJWT($jwt)
{
    try {
        $decoded = JWT::decode($jwt, new Key(SECRET_KEY, 'HS256'));
        return $decoded->data;
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid token: ' . $e->getMessage()]);
        exit();
    }
}

// Decode user data from the JWT
$userData = decodeJWT($jwt);

// Get action and method
$action = $_GET['action'] ?? null;
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Handle actions
switch ($action) {
    case 'getSessions':
        if ($requestMethod === 'GET') {
            Permission::checkRole($jwt, ['admin']); // Allow only admins

            $user_id = $_GET['user_id'] ?? null;
            $full_name = $_GET['full_name'] ?? null;

            if ($user_id) {
                $sessions = $sessionModel->getSessionsByUserId($user_id);
            } elseif ($full_name) {
                $sessions = $sessionModel->getSessionsByFullName($full_name);
            } else {
                $sessions = $sessionModel->getAllSessions();
            }

            echo json_encode($response->responsePayload($sessions, 'success', 'Sessions retrieved successfully.', 200));
        } else {
            echo json_encode($response->responsePayload(null, 'error', 'Invalid request method.', 405));
        }
        break;

    default:
        echo json_encode($response->responsePayload(null, 'error', 'Invalid action.', 400));
        break;
}
