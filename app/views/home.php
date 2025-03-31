<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/User.php';
$pdo = require __DIR__ . '/../../db.php';
$userModel = new User($pdo);

$currentUser = $userModel->getCurrentUser();
$isLoggedIn = $currentUser !== null;
$isAdmin = $userModel->isAdmin($currentUser ?? []);
$loginError = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <title>Before or After</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-neutral-900 text-white min-h-screen flex flex-col items-center">

<!-- Logo -->
<header class="mt-12 mb-8 text-center">
    <h1 class="text-6xl font-extrabold tracking-wide">Before or After</h1>
</header>

<?php if ($isLoggedIn): ?>
    <!-- Navigation -->
    <nav class="flex flex-col items-center gap-4 mb-12 w-full max-w-xs">
        <a href="/game" class="w-full text-center bg-green-600 hover:bg-green-700 py-3 rounded text-lg font-medium">Start spil</a>
        <a href="/leaderboard" class="w-full text-center bg-blue-600 hover:bg-blue-700 py-3 rounded text-lg font-medium">Leaderboard</a>
        <a href="/rules" class="w-full text-center bg-neutral-700 hover:bg-neutral-600 py-3 rounded text-lg font-medium">Regler</a>
        <?php if ($isAdmin): ?>
            <a href="/admin" class="w-full text-center bg-red-600 hover:bg-red-700 py-3 rounded text-lg font-medium">Adminpanel</a>
        <?php endif; ?>
        <a href="/logout" class="w-full text-center bg-neutral-800 hover:bg-neutral-700 py-3 rounded text-lg font-medium">Log ud</a>
    </nav>

<?php else: ?>
    <!-- Login/Register box -->
    <main class="w-full max-w-md bg-neutral-800 p-6 rounded-xl shadow-lg">

        <!-- Toggle buttons -->
        <div class="flex justify-center gap-4 mb-6">
            <button onclick="showForm('login')" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded">Login</button>
            <button onclick="showForm('register')" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded">Registrér</button>
        </div>

        <!-- Login form -->
        <form id="login-form" action="/login" method="post" class="space-y-3 hidden">
            <?php if ($loginError): ?>
                <div class="bg-red-600 text-white px-4 py-2 rounded text-sm text-center">
                    <?= htmlspecialchars($loginError) ?>
                </div>
            <?php endif; ?>
            <input name="email" type="email" placeholder="Email" required class="w-full p-2 rounded bg-neutral-700 border border-neutral-600">
            <input name="password" type="password" placeholder="Kodeord" required class="w-full p-2 rounded bg-neutral-700 border border-neutral-600">
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 transition text-white py-2 rounded">Login</button>
        </form>

        <!-- Register form -->
        <form id="register-form" action="/register" method="post" class="space-y-3 hidden">
            <input name="username" placeholder="Brugernavn" required class="w-full p-2 rounded bg-neutral-700 border border-neutral-600">
            <input name="email" type="email" placeholder="Email" required class="w-full p-2 rounded bg-neutral-700 border border-neutral-600">
            <input name="password" type="password" placeholder="Kodeord" required class="w-full p-2 rounded bg-neutral-700 border border-neutral-600">
            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 transition text-white py-2 rounded">Registrér</button>
        </form>

        <script>
            function showForm(type) {
                document.getElementById('login-form').classList.add('hidden');
                document.getElementById('register-form').classList.add('hidden');
                if (type === 'login') {
                    document.getElementById('login-form').classList.remove('hidden');
                } else {
                    document.getElementById('register-form').classList.remove('hidden');
                }
            }
            showForm('login');
        </script>
    </main>
<?php endif; ?>

</body>
</html>
