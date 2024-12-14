<?php
header("Access-Control-Allow-Origin: http://localhost:8080");  // Adjust if you have other origins
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");  // Allow the methods you need
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");  // Allow necessary headers
header("Access-Control-Allow-Credentials: true"); 

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load required files
require_once __DIR__ . '/routes/auth.routes.php';
require_once __DIR__ . '/routes/user.routes.php';
require_once __DIR__ . '/routes/session.routes.php';
require_once __DIR__ . '/helpers/permission.php';
require_once __DIR__ . '/controllers/userController.php';
require_once __DIR__ . '/controllers/authController.php';

require_once __DIR__ . '/models/inventory.model.php';
require_once __DIR__ . '/models/response.model.php';

$inventoryModel = new InventoryModel();
$response = new ResponseMethods();

$jwt = $_COOKIE['auth_token'] ?? null;

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
            // Remove the JWT from cookies by setting it with an expired time
            setcookie('jwt', '', time() - 3600, '/');  // Expire the JWT cookie
            $response = ['status' => 'success', 'message' => 'Logged out successfully.'];
            echo json_encode($response);
            exit;
        

    default:
        echo json_encode(["status" => "error", "message" => "No action provided."]);
        exit;
}
