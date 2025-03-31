<?php

class Event
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($title, $year, $imageUrl)
    {
        $stmt = $this->pdo->prepare("INSERT INTO events (title, year, image_url) VALUES (?, ?, ?)");
        return $stmt->execute([$title, $year, $imageUrl]);
    }

    public function getAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM events ORDER BY year ASC");
        return $stmt->fetchAll();
    }
}
