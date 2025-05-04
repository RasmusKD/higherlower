<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: /');
    exit;
}

require_once __DIR__ . '/../models/Score.php';
require_once __DIR__ . '/../services/GameService.php';
$pdo = require __DIR__ . '/../../db.php';
$scoreModel = new Score($pdo);
$gameService = new GameService();

// Initialiser spiltilstand hvis ikke til stede
if (!isset($_SESSION['current_event']) || !isset($_SESSION['next_event'])) {
    $_SESSION['score'] = 0;
    $_SESSION['current_event'] = $gameService->getRandomHistoricalEvent();
    $_SESSION['next_event'] = $gameService->getRandomHistoricalEvent($_SESSION['current_event']['year']);
}

$currentEvent = $_SESSION['current_event'] ?? null;
$nextEvent = $_SESSION['next_event'] ?? null;
$currentScore = $_SESSION['score'] ?? 0;
$bestScore = isset($_SESSION['user_id']) ? $scoreModel->getUserBestScore($_SESSION['user_id']) : 0;

// Sikkerhedsforanstaltning mod null-begivenheder
if (!$currentEvent || !$nextEvent) {
    $_SESSION['current_event'] = $gameService->getRandomHistoricalEvent();
    $_SESSION['next_event'] = $gameService->getRandomHistoricalEvent($_SESSION['current_event']['year']);
    $currentEvent = $_SESSION['current_event'];
    $nextEvent = $_SESSION['next_event'];
}
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Before or After - Spil</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-neutral-900 text-white min-h-screen flex flex-col">
<!-- Navigation -->
<nav class="bg-neutral-800 p-4 shadow-md">
    <div class="container mx-auto flex justify-between items-center">
        <a href="/" class="text-2xl font-bold">Before or After</a>
        <div class="flex gap-4">
            <a href="/leaderboard" class="text-blue-400 hover:text-blue-300">Topscorer</a>
            <a href="/logout" class="text-red-400 hover:text-red-300">Log ud</a>
        </div>
    </div>
</nav>

<!-- Spil Container -->
<main class="flex-grow flex flex-col items-center justify-center p-4">
    <!-- Score Visning -->
    <div class="text-center mb-8">
        <p class="text-xl">Score: <span id="score"><?= $currentScore ?></span></p>
        <p class="text-sm text-neutral-400">Din bedste score: <?= $bestScore ?></p>
    </div>

    <!-- Begivenheder kort -->
    <div class="w-full max-w-4xl grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Nuværende begivenhed (med årstal) -->
        <div class="bg-neutral-800 rounded-lg overflow-hidden shadow-lg border border-neutral-700 p-6">
            <div class="flex flex-col h-full">
                <h2 class="text-2xl font-bold mb-4"><?= htmlspecialchars($currentEvent['title'] ?? 'Indlæser...') ?></h2>
                <p class="text-neutral-400 mb-2 flex-grow">
                    <?= htmlspecialchars($currentEvent['description'] ?? '') ?>
                </p>
                <p class="text-4xl font-bold text-yellow-400 text-center mt-4">
                    <?= htmlspecialchars($currentEvent['year'] ?? '') ?>
                </p>
            </div>
        </div>

        <!-- Næste begivenhed (uden årstal) -->
        <div id="next-event" class="bg-neutral-800 rounded-lg overflow-hidden shadow-lg border border-neutral-700 p-6">
            <div class="flex flex-col h-full">
                <h2 class="text-2xl font-bold mb-4"><?= htmlspecialchars($nextEvent['title'] ?? 'Indlæser...') ?></h2>
                <p class="text-neutral-400 mb-2 flex-grow">
                    <?= htmlspecialchars(str_replace($nextEvent['year'] ?? '', '???', $nextEvent['description'] ?? '')) ?>
                </p>
                <div class="mt-4">
                    <p class="text-center mb-4 text-lg">Skete dette før eller efter?</p>
                    <!-- Gæt knapper -->
                    <div class="grid grid-cols-2 gap-4">
                        <button id="guess-before" class="py-3 bg-blue-600 hover:bg-blue-700 rounded font-bold">FØR</button>
                        <button id="guess-after" class="py-3 bg-green-600 hover:bg-green-700 rounded font-bold">EFTER</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Spil slut modal (skjult som standard) -->
    <div id="game-over-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="bg-neutral-800 p-6 rounded-lg max-w-md w-full">
            <h2 class="text-2xl font-bold mb-4">Spillet er slut!</h2>
            <p class="mb-4">Din score: <span id="final-score">0</span></p>
            <p class="mb-4">Det rigtige årstal var: <span id="correct-year">0</span></p>
            <div class="flex gap-4">
                <a href="/game/start" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 rounded text-center font-bold">Spil igen</a>
                <a href="/leaderboard" class="flex-1 py-3 bg-green-600 hover:bg-green-700 rounded text-center font-bold">Topscorer</a>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Cache DOM elements
        const elements = {
            beforeBtn: document.getElementById('guess-before'),
            afterBtn: document.getElementById('guess-after'),
            scoreEl: document.getElementById('score'),
            gameOverModal: document.getElementById('game-over-modal'),
            finalScoreEl: document.getElementById('final-score'),
            correctYearEl: document.getElementById('correct-year'),
            nextEventEl: document.getElementById('next-event'),
            currentTitle: document.querySelector('.grid-cols-1.md\\:grid-cols-2 > div:first-child h2'),
            currentDesc: document.querySelector('.grid-cols-1.md\\:grid-cols-2 > div:first-child .text-neutral-400'),
            currentYear: document.querySelector('.text-4xl.font-bold.text-yellow-400'),
            nextTitle: document.querySelector('#next-event h2'),
            nextDesc: document.querySelector('#next-event .text-neutral-400')
        };

        // Check if we have valid event data
        function checkEvents() {
            if (elements.currentTitle.textContent.includes('Indlæser...') ||
                elements.nextTitle.textContent.includes('Indlæser...')) {
                window.location.href = '/game/start';
            }
        }

        // Run the check when page loads
        setTimeout(checkEvents, 1000);

        function setLoading(isLoading) {
            elements.nextEventEl.classList.toggle('opacity-50', isLoading);
            elements.beforeBtn.disabled = isLoading;
            elements.afterBtn.disabled = isLoading;
            elements.beforeBtn.textContent = isLoading ? "Indlæser..." : "FØR";
            elements.afterBtn.textContent = isLoading ? "Indlæser..." : "EFTER";
        }

        function makeGuess(guess) {
            setLoading(true);

            const formData = new FormData();
            formData.append('guess', guess);

            fetch('/game/guess', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.error || 'Network response was not ok');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    setLoading(false);
                    if (data.correct) {
                        // Update score
                        elements.scoreEl.textContent = data.score;
                        // Update events
                        updateEvents(data.currentEvent, data.nextEvent);
                    } else {
                        // Game over
                        elements.finalScoreEl.textContent = data.score;
                        elements.correctYearEl.textContent = data.nextEvent.year;
                        elements.gameOverModal.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    setLoading(false);

                    // Show friendly error message
                    alert("Der opstod en fejl med spillet. Prøv igen om lidt.");

                    // Auto-restart after error
                    setTimeout(() => {
                        window.location.href = '/game/start';
                    }, 2000);
                });
        }

        function updateEvents(currentEvent, nextEvent) {
            // Update current event (left card)
            elements.currentTitle.textContent = currentEvent.title;
            elements.currentDesc.textContent = currentEvent.description || '';
            elements.currentYear.textContent = currentEvent.year;

            // Update next event (right card)
            elements.nextTitle.textContent = nextEvent.title;
            elements.nextDesc.textContent = (nextEvent.description || '')
                .replace(nextEvent.year, '???');
        }

        // Event listeners
        elements.beforeBtn.addEventListener('click', () => makeGuess('before'));
        elements.afterBtn.addEventListener('click', () => makeGuess('after'));
    });
</script>
</body>
</html>
