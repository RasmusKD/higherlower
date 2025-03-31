<?php
require_once __DIR__ . '/../models/Event.php';

class AdminController
{
    public function index()
    {
        require_once __DIR__ . '/../views/admin.php';
    }

    public function create()
    {
        $pdo = require __DIR__ . '/../../db.php';
        $eventModel = new Event($pdo);

        $title = $_POST['title'] ?? '';
        $year = $_POST['year'] ?? '';

        if (!empty($_FILES['image']['name'])) {
            $uploadDir = __DIR__ . '/../../public/uploads/';
            $filename = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imageUrl = '/uploads/' . $filename;

                if ($title && $year) {
                    $eventModel->create($title, (int)$year, $imageUrl);
                }
            }
        }

        header('Location: /admin');
        exit;
    }
}
