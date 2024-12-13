<?php

require_once __DIR__ . '/../config/Connection.php';
require_once __DIR__ . '/../helpers/permission.php';
require_once __DIR__ . '/../controllers/authController.php';
require_once __DIR__ . '/../models/session.model.php';

$authToken = $_COOKIE['auth_token'] ?? null;

if ($authToken) {
    $authResult = Permission::authorizeRole($authToken, ['admin']);

    if ($authResult['status'] !== 'success') {
        echo json_encode($authResult); 
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'sessions') {
    $sessionModel = new SessionModel();

    $user_id = $_GET['user_id'] ?? null;
    $full_name = $_GET['full_name'] ?? null;

    if ($user_id) {
        $sessions = $sessionModel->getSessionsByUserId($user_id);
    } elseif ($full_name) {
        $sessions = $sessionModel->getSessionsByFullName($full_name);
    } else {
        $sessions = $sessionModel->getAllSessions();
    }
    if (isset($sessions)) {
        echo json_encode($sessions);
    }
}
