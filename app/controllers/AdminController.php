<?php
class AdminController
{
    /**
     * Viser admin-siden
     * Kontrollerer at brugeren er logget ind som administrator
     */
    public function index()
    {
        session_start();

        // Tilføj debug output
        error_log("Session data: " . print_r($_SESSION, true));

        // Tjek for admin (med mere fleksibel betingelse)
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
            error_log("Admin tjek fejlede: is_admin er " .
                (isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : 'ikke sat'));
            header('Location: /');
            exit;
        }

        require_once __DIR__ . '/../views/admin.php';
    }

    /**
     * Skifter admin-status for en bruger
     * Kræver admin-rettigheder
     */
    public function toggleAdminStatus()
    {
        session_start();
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
            header('Location: /');
            exit;
        }

        $userId = $_POST['user_id'] ?? null;
        if (!$userId) {
            header('Location: /admin');
            exit;
        }

        $pdo = require __DIR__ . '/../../db.php';
        $userModel = new User($pdo);
        $userModel->toggleAdmin($userId);

        header('Location: /admin');
        exit;
    }

    /**
     * Opdaterer API-nøglen i .env-filen
     * Kræver admin-rettigheder
     */
    public function updateApiKey()
    {
        session_start();
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
            header('Location: /');
            exit;
        }

        $apiKey = $_POST['api_key'] ?? '';

        // Opdater API-nøglen i .env-filen
        $envFile = __DIR__ . '/../../.env';
        $envContent = file_get_contents($envFile);

        if (strpos($envContent, 'API_NINJAS_KEY=') !== false) {
            // Erstat eksisterende nøgle
            $envContent = preg_replace(
                '/API_NINJAS_KEY=.*/',
                'API_NINJAS_KEY=' . $apiKey,
                $envContent
            );
        } else {
            // Tilføj ny nøgle
            $envContent .= "\nAPI_NINJAS_KEY=" . $apiKey;
        }

        file_put_contents($envFile, $envContent);

        header('Location: /admin');
        exit;
    }
}
