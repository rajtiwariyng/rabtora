<?php
/**
 * ONE-TIME SETUP — run once in browser, then delete this file.
 * http://localhost/rabtora.ae/admin/setup.php
 */

$host   = 'localhost';
$dbname = 'rabtora';
$user   = 'root';
$pass   = '';

try {
    $pdo = new PDO("mysql:host={$host};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}`
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$dbname}`");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS leads (
            id           INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
            form_source  VARCHAR(20)   NOT NULL DEFAULT '',
            full_name    VARCHAR(255)  NOT NULL,
            phone        VARCHAR(50)   NOT NULL,
            email        VARCHAR(255)  DEFAULT NULL,
            company_name VARCHAR(255)  DEFAULT NULL,
            budget       VARCHAR(100)  DEFAULT NULL,
            services     VARCHAR(500)  DEFAULT NULL,
            ip_address   VARCHAR(45)   DEFAULT NULL,
            created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_created (created_at),
            INDEX idx_source  (form_source)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin_users (
            id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username      VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $hash = password_hash('Admin@1234', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT IGNORE INTO admin_users (username, password_hash) VALUES ('admin', ?)")
        ->execute([$hash]);

    echo '<pre style="font-family:monospace;font-size:15px;padding:24px">';
    echo "✅  Database  '{$dbname}' ready.\n";
    echo "✅  Table     'leads' created (or already exists).\n";
    echo "✅  Table     'admin_users' created (or already exists).\n";
    echo "✅  Default admin: admin / Admin\@1234\n\n";
    echo "⚠️   DELETE this file after setup is complete!\n\n";
    echo "→   Admin panel : /rabtora.ae/admin/login.php\n";
    echo '</pre>';

} catch (PDOException $e) {
    echo '<pre style="color:red">';
    echo 'Setup failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    echo '</pre>';
}
