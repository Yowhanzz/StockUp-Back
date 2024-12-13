<?php

require_once __DIR__ . '/../models/auth.model.php';

class UserController
{
    private $authModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
    }

    public function register($request)
{
    $full_name = $request['full_name'] ?? null;
    $username = $request['username'] ?? null;
    $password = $request['password'] ?? null;
    $checkPassword = $request['checkpassword'] ?? null;

    if (!$full_name || !$username || !$password || !$checkPassword) {
        return json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    }

    if ($password !== $checkPassword) {
        return json_encode(['status' => 'error', 'message' => 'Passwords do not match']);
    }

    if ($this->authModel->userExists($username)) {
        return json_encode(['status' => 'error', 'message' => 'Username already taken']);
    }

    $passwordCheck = $this->checkPassword($password);
    if ($passwordCheck['status'] === 'error') {
        return json_encode($passwordCheck);
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $result = $this->authModel->createUser($full_name, $username, $hashedPassword);

    if ($result) {
        return json_encode(['status' => 'success', 'message' => 'User registered successfully']);
    }

    return json_encode(['status' => 'error', 'message' => 'Registration failed']);
}
private function checkPassword($password)
{
    if (strlen($password) < 8) {
        return ['status' => 'error', 'message' => 'Password must be at least 8 characters long'];
    }

    if (!preg_match('/[A-Z]/', $password)) {
        return ['status' => 'error', 'message' => 'Password must contain at least one uppercase letter'];
    }

    if (!preg_match('/[a-z]/', $password)) {
        return ['status' => 'error', 'message' => 'Password must contain at least one lowercase letter'];
    }

    if (!preg_match('/[0-9]/', $password)) {
        return ['status' => 'error', 'message' => 'Password must contain at least one number'];
    }

    if (!preg_match('/[\W_]/', $password)) {
        return ['status' => 'error', 'message' => 'Password must contain at least one special character'];
    }

    return ['status' => 'success'];
}
}