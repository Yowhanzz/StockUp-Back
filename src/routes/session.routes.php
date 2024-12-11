<?php

require_once __DIR__ . '/../config/Connection.php';
require_once __DIR__ . '/../helpers/permission.php';
require_once __DIR__ . '/../controllers/authController.php';
require_once __DIR__ . '/../models/session.model.php';

$headers = apache_request_headers();
$jwt = $headers['Authorization'] ?? null;

if ($jwt) {
    $jwt = str_replace('Bearer ', '', $jwt);
}

// Check if the JWT is valid and the role is 'admin'
if ($jwt) {
    $authResult = Permission::authorizeRole($jwt, ['admin']);

    if ($authResult['status'] !== 'success') {
        echo json_encode($authResult);  // Return permission error if the user is not an admin
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'sessions') {
    $sessionModel = new SessionModel();

    $user_id = $_GET['user_id'] ?? null;
    $full_name = $_GET['full_name'] ?? null;

    if ($user_id) {
        // Fetch sessions by user ID
        $sessions = $sessionModel->getSessionsByUserId($user_id);
    } elseif ($full_name) {
        // Fetch sessions by full name
        $sessions = $sessionModel->getSessionsByFullName($full_name);
    } else {
        // Fetch all sessions
        $sessions = $sessionModel->getAllSessions();
    }

    if (isset($sessions)) {
        echo json_encode($sessions);
    }
}
