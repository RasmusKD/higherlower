<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/Event.php';
$pdo = require __DIR__ . '/../../db.php';
$eventModel = new Event($pdo);
$events = $eventModel->getAll();
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <title>Adminpanel – Before or After</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-neutral-900 text-white min-h-screen flex flex-col items-center px-4 py-10">

<h1 class="text-5xl font-bold mb-8">Adminpanel</h1>

<form method="POST" action="/admin/create" enctype="multipart/form-data" class="...">
    <input name="title" placeholder="Titel" required class="w-full p-3 bg-neutral-700 rounded border border-neutral-600">
    <input name="year" type="number" placeholder="Årstal" required class="w-full p-3 bg-neutral-700 rounded border border-neutral-600">
    <input name="image" type="file" accept="image/*" required class="w-full p-3 bg-neutral-700 rounded border border-neutral-600">
    <button type="submit" class="w-full py-3 bg-green-600 hover:bg-green-700 rounded text-lg">Opret Event</button>
</form>

<section class="w-full max-w-4xl">
    <h2 class="text-2xl font-semibold mb-4">Eksisterende events</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <?php foreach ($events as $event): ?>
            <div class="bg-neutral-800 p-4 rounded-lg">
                <img src="<?= htmlspecialchars($event['image_url']) ?>" alt="Event billede" class="w-full h-40 object-cover mb-2 rounded">
                <h3 class="text-xl font-semibold"><?= htmlspecialchars($event['title']) ?></h3>
                <p class="text-neutral-400">År: <?= htmlspecialchars($event['year']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

</body>
</html>
