<?php
require_once __DIR__ . '/../config/Connection.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../interfaces/auth.interface.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthModel extends Connection implements AuthInterface
{
    private $secret_key = SECRET_KEY;
    public function userExists($username)
    {
        $sql = "SELECT COUNT(*) AS count FROM users WHERE username = :username";
        $pdo = $this->connect();
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $result = $stmt->fetch();

        error_log("userExists query result: ". json_encode($result));
        return $result['count'] > 0;
    }

    public function createUser($full_name, $username, $password, $role = null)
    {
    if ($role) {
        $sql = "INSERT INTO users (full_name, username, password, role) VALUES (:full_name, :username, :password, :role)";
    } else {
        $sql = "INSERT INTO users (full_name, username, password) VALUES (:full_name, :username, :password)";
    }

    $pdo = $this->connect();
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);

    if ($role) {
        $stmt->bindParam(':role', $role);
    }
    return $stmt->execute();
    }

    public function login($username, $password)
    {
        $sql = "SELECT * FROM users WHERE username = :username";
        $pdo = $this->connect();
    
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
    
        $user = $stmt->fetch();
    
        if ($user) {
            if (password_verify($password, $user['password'])) {
                if ($this->hasActiveSession($user['user_id'])) {
                    return ['status' => 'error', 'message' => 'User already ewan in'];
                }
    
                $payload = [
                    'iss' => "http://yourdomain.com",
                    'aud' => "http://yourdomain.com",
                    'iat' => time(),
                    'exp' => time() + 3600,
                    'data' => [
                        'user_id' => $user['user_id'],
                        'username' => $user['username'],
                        'full_name' => $user['full_name'],
                        'role' => $user['role'],
                    ],
                ];
    
                $jwt = JWT::encode($payload, $this->secret_key, 'HS256');
    
                $this->createSession($user['user_id'], $user['full_name']);

                setcookie('auth_token', $jwt, [
                    'expires' => time() + 3600,  
                    'path' => '/',              
                    'secure' => true,           
                    'httponly' => true,         
                    'samesite' => 'Strict'      
                ]);
    
                return [
                    'status' => 'success',
                    'token' => $jwt,
                    'data' => [
                        'user_id' => $user['user_id'],
                        'username' => $user['username'],
                        'full_name' => $user['full_name'],
                        'role' => $user['role'],
                    ],
                ];
            }
        }
    
        return ['status' => 'error', 'message' => 'Invalid username or password'];
    }
    


    public function createSession($user_id, $full_name)
    {
        $sql = "INSERT INTO user_sessions (user_id, full_name) VALUES (:user_id, :full_name)";
        $pdo = $this->connect();
    
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':full_name', $full_name);
    
        return $stmt->execute();
    }
    
    public function hasActiveSession($user_id)
    {
        $sql = "SELECT COUNT(*) AS count FROM user_sessions WHERE user_id = :user_id AND is_logged_in = 1";
        $pdo = $this->connect();
    
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    public function blacklistToken($token)
    {
    $sql = "INSERT INTO token_blacklist (token) VALUES (:token)";
    $pdo = $this->connect();

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':token', $token);

    return $stmt->execute();
    }

    public function endSession($user_id)
    {
    $sql = "UPDATE user_sessions SET time_out = CURRENT_TIMESTAMP, is_logged_in = 0 WHERE user_id = :user_id AND time_out IS NULL";
    $pdo = $this->connect();

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);

    return $stmt->execute();
    }
}