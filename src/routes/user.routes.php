<?php

require_once __DIR__ . '/../controllers/userController.php';

$userController = new UserController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'register') {
    // Parse JSON request body
    $input = json_decode(file_get_contents('php://input'), true);

}
