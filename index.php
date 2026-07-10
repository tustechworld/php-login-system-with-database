<?php
// ----- simple pure PHP router -----
// session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// include User class (database version)
require_once 'User.php';

// handle actions
$action = $_GET['action'] ?? 'login';
$message = '';
$messageType = '';

// LOGOUT
if ($action === 'logout') {
    session_destroy();
    header('Location: index.php?action=login');
    exit;
}

// REGISTER
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $message = 'All fields are required.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } elseif ($password !== $confirm) {
        $message = 'Passwords do not match.';
        $messageType = 'error';
    } elseif (strlen($password) < 8) {
        $message = 'Password must be at least 8 characters.';
        $messageType = 'error';
    } else {
        try {
            $user = new User();
            if ($user->exists($username, $email)) {
                $message = 'Username or email already taken.';
                $messageType = 'error';
            } else {
                if ($user->create($username, $email, $password)) {
                    $message = 'Registration successful! Please login.';
                    $messageType = 'success';
                    header('Refresh: 1; URL=index.php?action=login');
                } else {
                    $message = 'Registration failed. Please try again.';
                    $messageType = 'error';
                }
            }
        } catch (PDOException $e) {
            $message = 'Database error: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// LOGIN
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $message = 'Please fill in all fields.';
        $messageType = 'error';
    } else {
        try {
            $user = new User();
            $userData = $user->verify($username, $password);
            if ($userData) {
                $_SESSION['user'] = $userData['username'];
                $_SESSION['user_id'] = $userData['id'];
                $_SESSION['user_email'] = $userData['email'];
                header('Location: index.php?action=dashboard');
                exit;
            } else {
                $message = 'Invalid credentials.';
                $messageType = 'error';
            }
        } catch (PDOException $e) {
            $message = 'Database error: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// default: if user is logged in and tries to access login/register -> redirect to dashboard
if (isset($_SESSION['user']) && ($action === 'login' || $action === 'register')) {
    header('Location: index.php?action=dashboard');
    exit;
}

// if not logged in and tries dashboard -> redirect login
if (!isset($_SESSION['user']) && $action === 'dashboard') {
    header('Location: index.php?action=login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP login system · MySQL · TwinCSS</title>
    <!-- TwinCSS (lite) via CDN – pure CSS utility framework -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/twincss@1.1.0/dist/twincss.min.css">
    <style>
        /* minimal custom overrides for demo readability */
        body { background: #f8fafc; }
        .card { background: white; border-radius: 1.5rem; box-shadow: 0 8px 20px rgba(0,0,0,0.04); }
        .input { border: 1px solid #e2e8f0; transition: 0.2s; }
        .input:focus { border-color: #94a3b8; outline: none; box-shadow: 0 0 0 3px rgba(148,163,184,0.2); }
        .btn-primary { background: #1e293b; color: white; }
        .btn-primary:hover { background: #0f172a; }
        .link-muted { color: #475569; }
        .link-muted:hover { color: #1e293b; }
        .flash { border-left: 4px solid #e2e8f0; padding: 0.75rem 1rem; background: #f1f5f9; border-radius: 0.5rem; }
        .flash-error { border-left-color: #b91c1c; background: #fee2e2; }
        .flash-success { border-left-color: #15803d; background: #dcfce7; }
        hr { border: 0; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

<div class="w-full max-w-md card p-6 md:p-8">

    <?php
    // ---- render view ----
    if ($action === 'register' && !isset($_SESSION['user'])) {
        // REGISTER FORM
        ?>
        <h1 class="text-2xl font-semibold mb-2">Create account</h1>
        <p class="text-sm text-gray-500 mb-6">Join the system with a username and password.</p>

        <?php if ($message): ?>
            <div class="flash <?= $messageType === 'error' ? 'flash-error' : 'flash-success' ?> mb-4 text-sm">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="?action=register" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" name="username" class="input w-full rounded-lg px-4 py-2.5 text-sm" placeholder="e.g. johndoe" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" class="input w-full rounded-lg px-4 py-2.5 text-sm" placeholder="john@example.com" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" class="input w-full rounded-lg px-4 py-2.5 text-sm" placeholder="min 8 chars" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm password</label>
                <input type="password" name="confirm_password" class="input w-full rounded-lg px-4 py-2.5 text-sm" placeholder="repeat password" required>
            </div>
            <button type="submit" class="btn-primary w-full rounded-lg py-2.5 text-sm font-medium transition">Register</button>
        </form>

        <hr class="my-6">
        <p class="text-sm text-center text-gray-600">Already have an account? <a href="?action=login" class="link-muted font-medium underline">Login</a></p>
        <?php
    } elseif ($action === 'login' && !isset($_SESSION['user'])) {
        // LOGIN FORM
        ?>
        <h1 class="text-2xl font-semibold mb-2">Welcome back</h1>
        <p class="text-sm text-gray-500 mb-6">Log in to your account.</p>

        <?php if ($message): ?>
            <div class="flash <?= $messageType === 'error' ? 'flash-error' : 'flash-success' ?> mb-4 text-sm">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="?action=login" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username or Email</label>
                <input type="text" name="username" class="input w-full rounded-lg px-4 py-2.5 text-sm" placeholder="your username or email" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" class="input w-full rounded-lg px-4 py-2.5 text-sm" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-primary w-full rounded-lg py-2.5 text-sm font-medium transition">Log in</button>
        </form>

        <hr class="my-6">
        <p class="text-sm text-center text-gray-600">Don't have an account? <a href="?action=register" class="link-muted font-medium underline">Register</a></p>
        <?php
    } elseif ($action === 'dashboard' && isset($_SESSION['user'])) {
        // DASHBOARD (protected)
        $username = htmlspecialchars($_SESSION['user']);
        $email = htmlspecialchars($_SESSION['user_email'] ?? '');
        ?>
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-semibold">Dashboard</h1>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                <?= $username ?>
            </span>
        </div>
        <p class="text-gray-600 text-sm mb-2">You are successfully logged in.</p>
        <div class="bg-gray-50 rounded-xl p-4 text-sm text-gray-700 border border-gray-100 mb-6">
            <strong>User ID:</strong> <?= $_SESSION['user_id'] ?? 'N/A' ?><br>
            <strong>Email:</strong> <?= $email ?><br>
            <strong>Session:</strong> <?= session_id() ?><br>
            <span class="text-xs text-gray-400">MySQL database · PDO</span>
        </div>
        <a href="?action=logout" class="inline-block w-full text-center btn-primary rounded-lg py-2.5 text-sm font-medium transition">Log out</a>
        <?php
    } else {
        // fallback: if something weird happens, redirect to login
        header('Location: index.php?action=login');
        exit;
    }
    ?>

    <div class="mt-6 text-center text-[10px] uppercase tracking-wider text-gray-300 border-t border-gray-100 pt-4">
        <span class="font-mono">php-login-system</span> · MySQL · twinCSS
    </div>
</div>

</body>
</html>
