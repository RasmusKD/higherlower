<?php

class User
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function create($username, $email, $password)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, 0)");
        return $stmt->execute([$username, $email, $hash]);
    }

    public function isAdmin($user)
    {
        return isset($user['is_admin']) && $user['is_admin'] == 1;
    }

    public function getCurrentUser(): ?array
    {
        if (!isset($_SESSION['user'])) {
            return null;
        }

        return [
            'username' => $_SESSION['user'],
            'is_admin' => $_SESSION['is_admin'] ?? 0
        ];
    }
}
