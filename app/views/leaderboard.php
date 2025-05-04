<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: /');
    exit;
}

require_once __DIR__ . '/../models/Score.php';
$pdo = require __DIR__ . '/../../db.php';
$scoreModel = new Score($pdo);
$topScores = $scoreModel->getTopScores();
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Before or After - Topscorer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-neutral-900 text-white min-h-screen flex flex-col">
<!-- Navigation -->
<nav class="bg-neutral-800 p-4 shadow-md">
    <div class="container mx-auto flex justify-between items-center">
        <a href="/" class="text-2xl font-bold">Before or After</a>
        <div class="flex gap-4">
            <a href="/game" class="text-blue-400 hover:text-blue-300">Spil igen</a>
            <a href="/logout" class="text-red-400 hover:text-red-300">Log ud</a>
        </div>
    </div>
</nav>

<!-- Topscorer Container -->
<main class="container mx-auto flex-grow flex flex-col items-center justify-center p-4">
    <h1 class="text-4xl font-bold mb-8">Topscorer</h1>

    <div class="w-full max-w-2xl bg-neutral-800 rounded-lg overflow-hidden shadow-lg">
        <table class="w-full">
            <thead class="bg-neutral-700">
            <tr>
                <th class="py-4 px-6 text-left">#</th>
                <th class="py-4 px-6 text-left">Bruger</th>
                <th class="py-4 px-6 text-right">Score</th>
                <th class="py-4 px-6 text-right">Dato</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($topScores as $index => $score): ?>
                <tr class="<?= $score['username'] === $_SESSION['user'] ? 'bg-blue-900 bg-opacity-30' : '' ?>
                                 <?= $index % 2 === 0 ? 'bg-neutral-800' : 'bg-neutral-750' ?>
                                 hover:bg-neutral-700">
                    <td class="py-4 px-6"><?= $index + 1 ?></td>
                    <td class="py-4 px-6 font-medium"><?= htmlspecialchars($score['username']) ?></td>
                    <td class="py-4 px-6 text-right font-bold"><?= htmlspecialchars($score['score']) ?></td>
                    <td class="py-4 px-6 text-right text-neutral-400">
                        <?= date('d/m/Y', strtotime($score['created_at'])) ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($topScores)): ?>
                <tr>
                    <td colspan="4" class="py-8 text-center text-neutral-400">Ingen scores endnu!</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a href="/game/start" class="mt-8 px-8 py-3 bg-green-600 hover:bg-green-700 rounded text-center font-bold">
        Spil igen
    </a>
</main>
</body>
</html>
