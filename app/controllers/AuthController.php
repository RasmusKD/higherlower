<?php

class AuthController
{
    public function login()
    {
        $pdo = require_once __DIR__ . '/../../db.php';

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            header('Location: /');
            exit;
        } else {
            session_start();
            $_SESSION['login_error'] = "Forkert email eller kodeord.";
            header('Location: /');
            exit;
        }
    }

    public function register()
    {
        $pdo = require_once __DIR__ . '/../../db.php';

        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $passwordRaw = $_POST['password'] ?? '';

        // Simpelt tjek
        if (!$username || !$email || !$passwordRaw) {
            header('Location: /');
            exit;
        }

        $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, 0)");
        $stmt->execute([$username, $email, $password]);

        session_start();
        $_SESSION['user'] = $username;
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
