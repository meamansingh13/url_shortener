<?php
// seed_super_admin.php
// Usage:
//  - from browser: open http://localhost/yourpath/seed_super_admin.php
//  - from CLI: php seed_super_admin.php

require_once 'config.php';

// CHANGE THESE values before running
$name = "Main Admin";
$email = "super@admin.com";
$password = "TestPass@123";

try {
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
    // Only display password when running from CLI to avoid exposing credentials via web.
    if (PHP_SAPI === 'cli') {
        echo "Email: $email\nPassword: $password\n";
    } else {
        echo "Email: " . htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "<br>";
        echo "Password was set. (Not shown in web mode for security.)";
    }
} catch (PDOException $e) {
    // Don't leak DSN or credentials — show a generic message and log the real error if needed
    error_log('seed_super_admin error: ' . $e->getMessage());
    echo "An error occurred while creating the super admin. Check server logs.";
    exit;
}
