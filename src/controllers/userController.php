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

        if (!$full_name || !$username || !$password) {
            return json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        }

        // Check if username already exists
        if ($this->authModel->userExists($username)) {
            return json_encode(['status' => 'error', 'message' => 'Username already taken']);
        }

        // Hash the password using bcrypt
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Save user to database (role is optional)
        $result = $this->authModel->createUser($full_name, $username, $hashedPassword);

        if ($result) {
            return json_encode(['status' => 'success', 'message' => 'User registered successfully']);
        }

        return json_encode(['status' => 'error', 'message' => 'Registration failed']);
    }
}