<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

require __DIR__ . '/../config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        try {
            $stmt = get_db()->prepare('SELECT id, password_hash FROM admin_users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id']       = $admin['id'];
                $_SESSION['admin_username'] = $username;
                header('Location: dashboard.php');
                exit;
            }
            $error = 'Invalid username or password.';
        } catch (PDOException $e) {
            $error = 'Database error. Please try again.';
        }
    } else {
        $error = 'Please enter your credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — Rabtora</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300..700;1,9..40,300..700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background: #091225;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'DM Sans', system-ui, sans-serif;
      background-image:
        radial-gradient(ellipse 60% 50% at 50% 0%, rgba(208,158,54,0.1) 0%, transparent 70%);
    }

    .wrap {
      width: 100%;
      max-width: 400px;
      padding: 24px;
    }

    .brand {
      text-align: center;
      margin-bottom: 36px;
    }
    .brand-name {
      font-family: 'DM Sans', sans-serif;
      font-size: 22px;
      font-weight: 700;
      letter-spacing: 0.18em;
      color: #D09E36;
      text-transform: uppercase;
    }
    .brand-sub {
      display: block;
      font-size: 10px;
      letter-spacing: 0.3em;
      color: #a0a5b0;
      text-transform: uppercase;
      margin-top: 6px;
    }

    .card {
      background: #0f223b;
      border: 1px solid rgba(208,158,54,0.18);
      border-radius: 10px;
      padding: 36px 32px;
      box-shadow: 0 20px 60px rgba(6,12,24,0.5);
    }

    .card-title {
      font-size: 13px;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: #a0a5b0;
      margin-bottom: 28px;
      text-align: center;
    }

    .alert {
      background: rgba(248,113,113,0.1);
      border: 1px solid rgba(248,113,113,0.3);
      color: #f87171;
      font-size: 13px;
      padding: 10px 14px;
      border-radius: 6px;
      margin-bottom: 20px;
    }

    .field { margin-bottom: 18px; }
    .field label {
      display: block;
      font-size: 11px;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: #a0a5b0;
      margin-bottom: 8px;
    }
    .field input {
      width: 100%;
      background: #081220;
      border: 1px solid rgba(255,255,255,0.1);
      color: #ffffff;
      font-family: 'DM Sans', sans-serif;
      font-size: 14px;
      padding: 11px 14px;
      border-radius: 6px;
      outline: none;
      transition: border-color 0.2s;
    }
    .field input:focus { border-color: #D09E36; }

    .btn-submit {
      width: 100%;
      background: linear-gradient(to right, #b98b32, #dfb45b);
      color: #091225;
      border: none;
      font-family: 'DM Sans', sans-serif;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      padding: 14px;
      border-radius: 6px;
      cursor: pointer;
      margin-top: 8px;
      transition: opacity 0.2s;
    }
    .btn-submit:hover { opacity: 0.88; }
  </style>
</head>
<body>
<div class="wrap">
  <div class="brand">
    <div class="brand-name">Rabtora</div>
    <span class="brand-sub">Admin Portal</span>
  </div>
  <div class="card">
    <p class="card-title">Sign In</p>
    <?php if ($error): ?>
      <div class="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <form method="POST" novalidate>
      <div class="field">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required autocomplete="username"
               value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn-submit">Sign In →</button>
    </form>
  </div>
</div>
</body>
</html>
