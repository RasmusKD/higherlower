<?php

class User
{
    private $pdo;

    /**
     * Konstruktør for User-klassen
     *
     * @param PDO $pdo Database-forbindelse
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Finder en bruger baseret på email
     *
     * @param string $email Brugerens email
     * @return array|false Brugerdata eller false hvis ikke fundet
     */
    public function findByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Opretter en ny bruger
     *
     * @param string $username Brugernavn
     * @param string $email Email
     * @param string $password Kodeord (ukrypteret)
     * @return bool Success status
     */
    public function create($username, $email, $password)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, 0)");
        return $stmt->execute([$username, $email, $hash]);
    }

    /**
     * Tjekker om en bruger er administrator
     *
     * @param array $user Brugerdata
     * @return bool True hvis brugeren er admin
     */
    public function isAdmin($user)
    {
        return isset($user['is_admin']) && $user['is_admin'] == 1;
    }

    /**
     * Henter information om den nuværende bruger fra sessionen
     *
     * @return array|null Brugerdata eller null hvis ikke logget ind
     */
    public function getCurrentUser(): ?array
    {
        if (!isset($_SESSION['user'])) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['user'],
            'is_admin' => $_SESSION['is_admin'] ?? 0
        ];
    }

    /**
     * Henter alle brugere
     *
     * @return array Liste af alle brugere
     */
    public function getAllUsers()
    {
        $stmt = $this->pdo->query("SELECT id, username, email, is_admin FROM users ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    /**
     * Tæller det samlede antal brugere
     *
     * @return int Antal brugere
     */
    public function getTotalUsers()
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM users");
        return $stmt->fetch()['count'];
    }

    /**
     * Skifter admin-status for en bruger
     *
     * @param int $userId Bruger-ID
     * @return bool Success status
     */
    public function toggleAdmin($userId)
    {
        $stmt = $this->pdo->prepare("UPDATE users SET is_admin = NOT is_admin WHERE id = ?");
        return $stmt->execute([$userId]);
    }
}
