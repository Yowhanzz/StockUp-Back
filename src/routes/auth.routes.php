<?php

require_once __DIR__ . '/../controllers/authController.php';
require_once __DIR__ . '/../controllers/userController.php';


use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthRoutes
{
    private $authController;
    private $userController;

    public function __construct()
    {
        $this->authController = new AuthController();
        $this->userController = new UserController();
    }

    public function handleRequest($action)
    {
        header('Content-Type: application/json');

        switch ($_SERVER['REQUEST_METHOD'] . ':' . $action) {
            case 'POST:login':
                $input = json_decode(file_get_contents('php://input'), true);
                echo $this->authController->login($input);
                break;
            
            case 'POST:register':
                $input = json_decode(file_get_contents('php://input'), true);
                echo $this->userController->register($input);
                break;

            case 'GET:logout':
                echo $this->authController->logout();
                break;

            default:
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid request method or action.',
                ]);
                break;
        }
    }
}
$authRoutes = new AuthRoutes();
$action = $action ?? null;
$authRoutes->handleRequest($action);
