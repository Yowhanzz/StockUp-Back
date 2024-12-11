<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Load required files
require_once __DIR__ . '/routes/auth.routes.php';
require_once __DIR__ . '/routes/user.routes.php';
require_once __DIR__ . '/routes/session.routes.php';
require_once __DIR__ . '/helpers/permission.php';
require_once __DIR__ . '/controllers/userController.php';
require_once __DIR__ . '/controllers/authController.php';

// Inventory-related files
require_once __DIR__ . '/models/inventory.model.php';
require_once __DIR__ . '/models/response.model.php';

$inventoryModel = new InventoryModel();
$response = new ResponseMethods();
$headers = apache_request_headers();
$jwt = $headers['Authorization'] ?? null;

if ($jwt) {
    $jwt = str_replace('Bearer ', '', $jwt);
}

$action = $_GET['action'] ?? null;
$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($action) {
    case 'register':
        $userController = new UserController();
        $input = json_decode(file_get_contents('php://input'), true);
        $response = $userController->register($input);
        echo is_array($response) ? json_encode($response) : $response;
        exit;

    case 'login':
        $authController = new AuthController();
        $input = json_decode(file_get_contents('php://input'), true);
        $response = $authController->login($input);
        echo is_array($response) ? json_encode($response) : $response;
        exit;

    case 'logout':
        $authController = new AuthController();
        $input = json_decode(file_get_contents('php://input'), true);
        $response = $authController->logout($input);
        echo is_array($response) ? json_encode($response) : $response;
        exit;

    default:
        echo json_encode(["status" => "error", "message" => "No action provided."]);
        exit;
}
