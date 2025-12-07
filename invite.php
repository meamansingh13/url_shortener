<?php
require_once 'auth.php';
require_login();
$user = current_user();

if ($user['role'] !== 'super_admin' && $user['role'] !== 'admin') {
    echo "Not allowed.";
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $role  = $_POST['role'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email";
    } elseif (!in_array($role, ['admin', 'member'])) {
        $message = "Invalid role";
    } else {
        if ($user['role'] === 'super_admin') {
            // SuperAdmin invites admin to a NEW company
            if ($role !== 'admin') {
                $message = "Super admin can only invite admins to new companies.";
            } else {
                $companyName = trim($_POST['company_name'] ?? '');
                if ($companyName === '') {
                    $message = "Company name required.";
                } else {
                    $pdo->beginTransaction();
                    try {
                        $stmt = $pdo->prepare("INSERT INTO companies (name) VALUES (?)");
                        $stmt->execute([$companyName]);
                        $companyId = $pdo->lastInsertId();

                        $token = bin2hex(random_bytes(16));
                        $stmt = $pdo->prepare("INSERT INTO invitations (email, role, company_id, token, created_by) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$email, $role, $companyId, $token, $user['id']]);

                        $pdo->commit();
                        $message = "Invitation created. Send this link: http://localhost/accept_invite.php?token=" . $token;
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $message = "Error: " . $e->getMessage();
                    }
                }
            }
        } else {
            // Admin invites admin/member to THEIR company
            $companyId = $user['company_id'];
            if (!$companyId) {
                $message = "Admin has no company assigned.";
            } else {
                $token = bin2hex(random_bytes(16));
                $stmt = $pdo->prepare("INSERT INTO invitations (email, role, company_id, token, created_by) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$email, $role, $companyId, $token, $user['id']]);

                $message = "Invitation created. Send this link: http://localhost/accept_invite.php?token=" . $token;
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
    <title>Create Invitation</title>
</head>
<body>
<h1>Create Invitation</h1>
<p><a href="dashboard.php">Back to Dashboard</a></p>

<?php if ($message): ?>
<p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<form method="post">
    <label>Email of invitee: <input type="email" name="email" required></label><br><br>
    <label>Role:
        <select name="role">
            <option value="admin">Admin</option>
            <option value="member">Member</option>
        </select>
    </label><br><br>

    <?php if ($user['role'] === 'super_admin'): ?>
        <label>New Company Name: <input type="text" name="company_name" required></label><br><br>
    <?php endif; ?>

    <button type="submit">Create Invitation</button>
</form>
</body>
</html>
