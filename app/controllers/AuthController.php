<?php

class AuthController
{
    public function login()
    {
        $pdo = require __DIR__ . '/../../db.php';


        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user'] = $user['username'];
            header('Location: /');
            exit;
        } else {
            echo "Forkert login.";
        }
    }

    public function register()
    {
        $pdo = require __DIR__ . '/../../db.php';

        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password]);

        header('Location: /');
        exit;
    }

    public function logout()
    {
        session_start();
        session_destroy();
        header('Location: /');
        exit;
    }
}
