<?php

require_once __DIR__ . '/../controllers/authController.php';

$authController = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'login') {

    $input = json_decode(file_get_contents('php://input'), true);

}