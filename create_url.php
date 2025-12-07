<?php
require_once 'auth.php';
require_login();
$user = current_user();

if (!can_create_url()) {
    echo "You are not allowed to create short URLs (SuperAdmin cannot create).";
    exit;
}

$error = '';
$message = '';

function generate_code($length = 6) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $code;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $original = trim($_POST['original_url'] ?? '');
    if ($original === '') {
        $error = "URL is required.";
    } else {
        if (!preg_match('~^https?://~i', $original)) {
            $original = 'http://' . $original;
        }

        // generate unique code
        do {
            $code = generate_code();
            $stmt = $pdo->prepare("SELECT id FROM urls WHERE short_code = ?");
            $stmt->execute([$code]);
            $exists = $stmt->fetch();
        } while ($exists);

        $stmt = $pdo->prepare("INSERT INTO urls (short_code, original_url, user_id, company_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $code,
            $original,
            $user['id'],
            $user['company_id']
        ]);

        $message = "Short URL created: http://localhost/redirect.php?c=" . $code;
    }
}
?>
<!doctype html>
<html>
<head>
    <title>Create Short URL</title>
</head>
<body>
<h1>Create Short URL</h1>
<p><a href="dashboard.php">Back to Dashboard</a></p>

<?php if ($error): ?><p style="color:red;"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
<?php if ($message): ?><p><?php echo htmlspecialchars($message); ?></p><?php endif; ?>

<form method="post">
    <label>Original URL: <input type="text" name="original_url" required></label><br><br>
    <button type="submit">Create</button>
</form>
</body>
</html>
