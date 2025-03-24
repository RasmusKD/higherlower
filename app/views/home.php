<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <title>Higher Lower – Before or After</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-neutral-900 text-white min-h-screen flex items-center justify-center">

<div class="w-full max-w-md bg-neutral-800 p-6 rounded-xl shadow-lg">
    <h1 class="text-2xl font-bold mb-4 text-center">Higher Lower – Before or After</h1>

    <?php if (!isset($_SESSION['user'])): ?>
        <div class="space-y-6">

            <!-- Login -->
            <form action="/login" method="post" class="space-y-3">
                <h2 class="text-xl font-semibold">Login</h2>
                <input name="email" type="email" placeholder="Email" required class="w-full p-2 rounded bg-neutral-700 border border-neutral-600">
                <input name="password" type="password" placeholder="Kodeord" required class="w-full p-2 rounded bg-neutral-700 border border-neutral-600">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 transition text-white py-2 rounded">Login</button>
            </form>

            <hr class="border-neutral-700">

            <!-- Register -->
            <form action="/register" method="post" class="space-y-3">
                <h2 class="text-xl font-semibold">Registrér</h2>
                <input name="username" placeholder="Brugernavn" required class="w-full p-2 rounded bg-neutral-700 border border-neutral-600">
                <input name="email" type="email" placeholder="Email" required class="w-full p-2 rounded bg-neutral-700 border border-neutral-600">
                <input name="password" type="password" placeholder="Kodeord" required class="w-full p-2 rounded bg-neutral-700 border border-neutral-600">
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 transition text-white py-2 rounded">Registrér</button>
            </form>

        </div>
    <?php else: ?>
        <div class="text-center space-y-4">
            <p class="text-lg">✅ Logget ind som <strong><?= htmlspecialchars($_SESSION['user']) ?></strong></p>
            <a href="/logout" class="inline-block bg-red-600 hover:bg-red-700 transition text-white px-4 py-2 rounded">Log ud</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
