<?php

header("Access-Control-Allow-Origin: http://localhost:8080"); // Allow requests from your frontend
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Allow specific HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow headers like Content-Type and Authorization
header("Access-Control-Allow-Credentials: true"); // Allow credentials (if needed, like cookies)

require_once __DIR__ . '/../controllers/userController.php';

$userController = new UserController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'register') {

    $input = json_decode(file_get_contents('php://input'), true);

}
