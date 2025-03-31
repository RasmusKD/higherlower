<?php
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    public function login()
    {
        $pdo = require __DIR__ . '/../../db.php';
        $userModel = new User($pdo);

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $user = $userModel->findByEmail($email);

        session_start();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
        } else {
            $_SESSION['login_error'] = "Forkert email eller kodeord.";
        }

        header('Location: /');
        exit;
    }

    public function register()
    {
        $pdo = require __DIR__ . '/../../db.php';
        $userModel = new User($pdo);

        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        session_start();

        if (!$username || !$email || !$password) {
            $_SESSION['login_error'] = "Udfyld alle felter.";
        } else {
            $userModel->create($username, $email, $password);
            $_SESSION['user'] = $username;
            $_SESSION['is_admin'] = 0;
        }

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
