<?php
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    /**
     * Håndterer login-processen
     * Validerer brugerinformation og opretter session
     */
    public function login()
    {
        $pdo = require __DIR__ . '/../../db.php';
        $userModel = new User($pdo);

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $user = $userModel->findByEmail($email);

        error_log("Bruger login forsøg: " . $email);
        error_log("Bruger fundet: " . ($user ? 'Ja' : 'Nej'));
        if ($user) {
            error_log("Bruger is_admin: " . $user['is_admin']);
        }

        session_start();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user['username'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['is_admin'] = $user['is_admin'];

            error_log("Login succesfuldt, session data: " . print_r($_SESSION, true));
        } else {
            $_SESSION['login_error'] = "Forkert email eller kodeord.";
        }

        header('Location: /');
        exit;
    }

    /**
     * Håndterer registrering af ny bruger
     * Opretter brugeren i databasen og logger ind
     */
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
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['is_admin'] = 0;
        }

        header('Location: /');
        exit;
    }

    /**
     * Håndterer logout-processen
     * Afslutter sessionen og omdirigerer til forsiden
     */
    public function logout()
    {
        session_start();
        session_destroy();
        header('Location: /');
        exit;
    }
}
