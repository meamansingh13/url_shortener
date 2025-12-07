<?php
require_once 'config.php';

$code = $_GET['c'] ?? '';
if ($code === '') {
    echo "No code provided.";
    exit;
}

$stmt = $pdo->prepare("SELECT original_url FROM urls WHERE short_code = ?");
$stmt->execute([$code]);
$row = $stmt->fetch();

if (!$row) {
    echo "Short URL not found.";
    exit;
}

header("Location: " . $row['original_url'], true, 302);
exit;
