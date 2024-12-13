<?php

require_once __DIR__ . '/../config/Connection.php';
require_once __DIR__ . '/../interfaces/session.interface.php';

class SessionModel implements SessionInterface
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = (new Connection())->connect();
    }
    public function getSessionsByUserId($user_id)
    {
        $sql = "SELECT us.user_id, u.full_name, us.time_in, us.time_out
                FROM user_sessions us
                JOIN users u ON us.user_id = u.user_id
                WHERE us.user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function getSessionsByFullName($full_name)
    {
        $sql = "SELECT us.user_id, u.full_name, us.time_in, us.time_out
                FROM user_sessions us
                JOIN users u ON us.user_id = u.user_id
                WHERE u.full_name LIKE :full_name";
        $stmt = $this->pdo->prepare($sql);
        $searchTerm = "%" . $full_name . "%";
        $stmt->bindParam(':full_name', $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function getAllSessions()
    {
        $sql = "SELECT us.user_id, u.full_name, us.time_in, us.time_out
                FROM user_sessions us
                JOIN users u ON us.user_id = u.user_id";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
}
