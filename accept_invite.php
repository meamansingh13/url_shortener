<?php
require_once 'config.php';

$token = $_GET['token'] ?? '';
if ($token === '') {
    echo "Invalid token.";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM invitations WHERE token = ? AND accepted_at IS NULL");
$stmt->execute([$token]);
$inv = $stmt->fetch();

if (!$inv) {
    echo "Invitation not found or already accepted.";
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $password === '') {
        $error = "Name and password are required.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // check if email already registered
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$inv['email']]);
        if ($check->fetch()) {
            $error = "User with this email already exists.";
        } else {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, company_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $name,
                    $inv['email'],
                    $hash,
                    $inv['role'],
                    $inv['company_id']
                ]);

                $stmt = $pdo->prepare("UPDATE invitations SET accepted_at = NOW() WHERE id = ?");
                $stmt->execute([$inv['id']]);

                $pdo->commit();
                $success = "Account created. You can now login.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
    <title>Accept Invitation</title>
</head>
<body>
<h1>Accept Invitation</h1>
<p>Invited as <?php echo $inv['role']; ?> to company ID: <?php echo $inv['company_id']; ?></p>

<?php if ($error): ?><p style="color:red;"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
<?php if ($success): ?><p><?php echo htmlspecialchars($success); ?></p><?php endif; ?>

<?php if (!$success): ?>
<form method="post">
    <label>Name: <input type="text" name="name" required></label><br><br>
    <label>Password: <input type="password" name="password" required></label><br><br>
    <button type="submit">Create Account</button>
</form>
<?php endif; ?>

<p><a href="login.php">Go to Login</a></p>
</body>
</html>
