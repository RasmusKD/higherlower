<?php

class AuthController
{
    public function login()
    {
        require_once 'db.php';

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user'] = $user['username'];
            header('Location: /');
        } else {
            echo "Forkert login.";
        }
    }

    public function register()
    {
        require_once 'db.php';

        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password]);

        header('Location: /');
    }

    public function logout()
    {
        session_start();
        session_destroy();
        header('Location: /');
    }
}
