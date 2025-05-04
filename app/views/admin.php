<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/Score.php';
require_once __DIR__ . '/../models/User.php';
$pdo = require __DIR__ . '/../../db.php';
$scoreModel = new Score($pdo);
$userModel = new User($pdo);
$topScores = $scoreModel->getTopScores(20);

// Hent statistik
$totalUsers = $userModel->getTotalUsers();
$totalGames = $scoreModel->getTotalGames();
$averageScore = $scoreModel->getAverageScore();
$highestScore = $scoreModel->getHighestScore();
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

<div class="flex gap-4 mb-8">
    <a href="/" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded">Tilbage til forsiden</a>
    <a href="/game" class="px-4 py-2 bg-green-600 hover:bg-green-700 rounded">Spil</a>
</div>

<!-- Dashboard Statistik -->
<section class="w-full max-w-4xl mb-8">
    <h2 class="text-2xl font-semibold mb-4">Dashboard</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-neutral-800 p-4 rounded-lg">
            <p class="text-lg text-neutral-400">Antal brugere</p>
            <p class="text-3xl font-bold"><?= $totalUsers ?></p>
        </div>

        <div class="bg-neutral-800 p-4 rounded-lg">
            <p class="text-lg text-neutral-400">Antal spil</p>
            <p class="text-3xl font-bold"><?= $totalGames ?></p>
        </div>

        <div class="bg-neutral-800 p-4 rounded-lg">
            <p class="text-lg text-neutral-400">Gennemsnitlig score</p>
            <p class="text-3xl font-bold"><?= number_format($averageScore, 1) ?></p>
        </div>

        <div class="bg-neutral-800 p-4 rounded-lg">
            <p class="text-lg text-neutral-400">Højeste score</p>
            <p class="text-3xl font-bold"><?= $highestScore ?></p>
        </div>
    </div>
</section>

<!-- API Konfiguration -->
<section class="w-full max-w-4xl mb-8">
    <h2 class="text-2xl font-semibold mb-4">API Konfiguration</h2>

    <div class="bg-neutral-800 p-6 rounded-lg">
        <p class="mb-4">
            Spilbegivenhederne hentes direkte fra Historical Events API når der er brug for dem.
            Ingen database-lagring nødvendig!
        </p>

        <form method="POST" action="/admin/update-api-key" class="flex gap-2">
            <input type="text" name="api_key" placeholder="API Nøgle" value="<?= htmlspecialchars($_ENV['API_NINJAS_KEY'] ?? '') ?>"
                   class="flex-grow p-2 bg-neutral-700 rounded border border-neutral-600">
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded">Opdater API Nøgle</button>
        </form>

        <div class="mt-4 bg-neutral-700 p-3 rounded text-sm">
            <p>Har du brug for en API-nøgle? Få en fra <a href="https://api-ninjas.com/" target="_blank" class="text-blue-400 hover:underline">API Ninjas</a>.</p>
        </div>
    </div>
</section>

<!-- Brugerstyring -->
<section class="w-full max-w-4xl mb-8">
    <h2 class="text-2xl font-semibold mb-4">Brugerstyring</h2>

    <div class="bg-neutral-800 rounded-lg overflow-hidden">
        <table class="w-full">
            <thead class="bg-neutral-700">
            <tr>
                <th class="py-3 px-6 text-left">ID</th>
                <th class="py-3 px-6 text-left">Brugernavn</th>
                <th class="py-3 px-6 text-left">Email</th>
                <th class="py-3 px-6 text-center">Admin</th>
                <th class="py-3 px-6 text-right">Handlinger</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($userModel->getAllUsers() as $user): ?>
                <tr class="border-b border-neutral-700 hover:bg-neutral-750">
                    <td class="py-3 px-6"><?= $user['id'] ?></td>
                    <td class="py-3 px-6 font-medium"><?= htmlspecialchars($user['username']) ?></td>
                    <td class="py-3 px-6"><?= htmlspecialchars($user['email']) ?></td>
                    <td class="py-3 px-6 text-center">
                        <?php if ($user['is_admin']): ?>
                            <span class="bg-green-600 text-white text-xs px-2 py-1 rounded">Ja</span>
                        <?php else: ?>
                            <span class="bg-neutral-600 text-white text-xs px-2 py-1 rounded">Nej</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3 px-6 text-right">
                        <form method="POST" action="/admin/toggle-admin" class="inline">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <button type="submit" class="text-blue-400 hover:text-blue-300">
                                <?= $user['is_admin'] ? 'Fjern admin' : 'Gør til admin' ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Top Scores -->
<section class="w-full max-w-4xl">
    <h2 class="text-2xl font-semibold mb-4">Topscorer</h2>

    <div class="bg-neutral-800 rounded-lg overflow-hidden shadow-lg">
        <table class="w-full">
            <thead class="bg-neutral-700">
            <tr>
                <th class="py-3 px-6 text-left">#</th>
                <th class="py-3 px-6 text-left">Bruger</th>
                <th class="py-3 px-6 text-right">Score</th>
                <th class="py-3 px-6 text-right">Dato</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($topScores as $index => $score): ?>
                <tr class="<?= $index % 2 === 0 ? 'bg-neutral-800' : 'bg-neutral-750' ?> hover:bg-neutral-700">
                    <td class="py-3 px-6"><?= $index + 1 ?></td>
                    <td class="py-3 px-6 font-medium"><?= htmlspecialchars($score['username']) ?></td>
                    <td class="py-3 px-6 text-right font-bold"><?= htmlspecialchars($score['score']) ?></td>
                    <td class="py-3 px-6 text-right text-neutral-400">
                        <?= date('d/m/Y H:i', strtotime($score['created_at'])) ?>
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
</section>

</body>
</html>
