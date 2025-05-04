<?php

class Score
{
    private $pdo;

    /**
     * Konstruktør for Score-klassen
     *
     * @param PDO $pdo Database-forbindelse
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Gemmer en ny score for en bruger
     *
     * @param int $userId Bruger-ID
     * @param int $score Opnået score
     * @return bool Success status
     */
    public function saveScore($userId, $score)
    {
        $stmt = $this->pdo->prepare("INSERT INTO scores (user_id, score, created_at) VALUES (?, ?, NOW())");
        return $stmt->execute([$userId, $score]);
    }

    /**
     * Henter de højeste scores
     *
     * @param int $limit Antal scores der skal hentes
     * @return array Liste af top scores med brugernavne
     */
    public function getTopScores($limit = 10)
    {
        // Konverter limit til heltal og brug direkte i forespørgslen
        $limit = (int)$limit;

        $stmt = $this->pdo->prepare("
            SELECT u.username, s.score, s.created_at 
            FROM scores s
            JOIN users u ON s.user_id = u.id
            ORDER BY s.score DESC 
            LIMIT $limit
        ");
        $stmt->execute(); // Ingen parametre nødvendige da limit er direkte i forespørgslen
        return $stmt->fetchAll();
    }

    /**
     * Henter en brugers bedste score
     *
     * @param int $userId Bruger-ID
     * @return int Brugerens bedste score (0 hvis ingen score findes)
     */
    public function getUserBestScore($userId)
    {
        $stmt = $this->pdo->prepare("SELECT MAX(score) as best_score FROM scores WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch()['best_score'] ?? 0;
    }

    /**
     * Tæller det samlede antal spil der er spillet
     *
     * @return int Antal spil
     */
    public function getTotalGames()
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM scores");
        return $stmt->fetch()['count'];
    }

    /**
     * Beregner gennemsnitsscore for alle spil
     *
     * @return float Gennemsnitlig score
     */
    public function getAverageScore()
    {
        $stmt = $this->pdo->query("SELECT AVG(score) as avg FROM scores");
        return $stmt->fetch()['avg'] ?? 0;
    }

    /**
     * Henter den højeste score nogensinde
     *
     * @return int Højeste score
     */
    public function getHighestScore()
    {
        $stmt = $this->pdo->query("SELECT MAX(score) as max FROM scores");
        return $stmt->fetch()['max'] ?? 0;
    }
}
