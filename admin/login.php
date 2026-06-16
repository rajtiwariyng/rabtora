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
  <link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600&family=Cormorant+Garamond:wght@400;500&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background: #080808;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Jost', system-ui, sans-serif;
      background-image:
        radial-gradient(ellipse 60% 50% at 50% 0%, rgba(201,168,76,0.07) 0%, transparent 70%);
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
      font-family: 'Cormorant Garamond', serif;
      font-size: 22px;
      font-weight: 500;
      letter-spacing: 0.18em;
      color: #c9a84c;
      text-transform: uppercase;
    }
    .brand-sub {
      display: block;
      font-size: 10px;
      letter-spacing: 0.3em;
      color: #666;
      text-transform: uppercase;
      margin-top: 6px;
    }

    .card {
      background: #111;
      border: 1px solid rgba(201,168,76,0.15);
      border-radius: 4px;
      padding: 36px 32px;
    }

    .card-title {
      font-size: 13px;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: #888;
      margin-bottom: 28px;
      text-align: center;
    }

    .alert {
      background: rgba(200,70,70,0.12);
      border: 1px solid rgba(200,70,70,0.3);
      color: #e07070;
      font-size: 13px;
      padding: 10px 14px;
      border-radius: 3px;
      margin-bottom: 20px;
    }

    .field { margin-bottom: 18px; }
    .field label {
      display: block;
      font-size: 11px;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: #666;
      margin-bottom: 8px;
    }
    .field input {
      width: 100%;
      background: #0d0d0d;
      border: 1px solid #2a2a2a;
      color: #e2e2e2;
      font-family: 'Jost', sans-serif;
      font-size: 14px;
      padding: 11px 14px;
      border-radius: 3px;
      outline: none;
      transition: border-color 0.2s;
    }
    .field input:focus { border-color: rgba(201,168,76,0.5); }

    .btn-submit {
      width: 100%;
      background: #c9a84c;
      color: #0a0a0a;
      border: none;
      font-family: 'Jost', sans-serif;
      font-size: 13px;
      font-weight: 600;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      padding: 14px;
      border-radius: 3px;
      cursor: pointer;
      margin-top: 8px;
      transition: background 0.2s;
    }
    .btn-submit:hover { background: #e0bc60; }
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
