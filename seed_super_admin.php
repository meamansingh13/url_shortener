<?php
// seed_super_admin.php
require_once 'config.php';

// CHANGE THESE:
$name = "Main Admin";
$email = "super@admin.com";
$password = "TestPass@123";

$check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$check->execute([$email]);
if ($check->fetch()) {
    echo "Super admin already exists.";
    exit;
}

// Super admin has no company (NULL)
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, company_id) VALUES (?, ?, ?, 'super_admin', NULL)");
$stmt->execute([$name, $email, $hash]);

echo "Super admin created.<br>";
echo "Email: $email<br>Password: $password";
