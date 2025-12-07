<?php
require_once 'auth.php';
require_login();
$user = current_user();

if ($user['role'] === 'super_admin') {
    // see all URLs from every company
    $stmt = $pdo->query("SELECT urls.*, users.email, users.role, users.company_id FROM urls JOIN users ON urls.user_id = users.id ORDER BY urls.created_at DESC");
} elseif ($user['role'] === 'admin') {
    // URLs for their company
    $stmt = $pdo->prepare("SELECT urls.*, users.email, users.role FROM urls JOIN users ON urls.user_id = users.id WHERE urls.company_id = ? ORDER BY urls.created_at DESC");
    $stmt->execute([$user['company_id']]);
} else {
    // member: only URLs created by themselves
    $stmt = $pdo->prepare("SELECT urls.*, users.email, users.role FROM urls JOIN users ON urls.user_id = users.id WHERE urls.user_id = ? ORDER BY urls.created_at DESC");
    $stmt->execute([$user['id']]);
}

$urls = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
    <title>URLs</title>
</head>
<body>
<h1>Short URLs</h1>
<p><a href="dashboard.php">Back to Dashboard</a></p>

<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Short</th>
        <th>Original</th>
        <th>Owner Email</th>
        <th>Owner Role</th>
        <th>Company ID</th>
        <th>Created At</th>
    </tr>
    <?php foreach ($urls as $u): ?>
        <tr>
            <td><?php echo $u['id']; ?></td>
            <td><a href="redirect.php?c=<?php echo htmlspecialchars($u['short_code']); ?>" target="_blank">
                <?php echo htmlspecialchars($u['short_code']); ?>
            </a></td>
            <td><?php echo htmlspecialchars($u['original_url']); ?></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td><?php echo htmlspecialchars($u['role']); ?></td>
            <td><?php echo htmlspecialchars($u['company_id']); ?></td>
            <td><?php echo htmlspecialchars($u['created_at']); ?></td>
        </tr>
    <?php endforeach; ?>
</table>
</body>
</html>
