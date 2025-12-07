<?php
require_once 'auth.php';
require_login();
$user = current_user();
?>
<!doctype html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
<h1>Welcome, <?php echo htmlspecialchars($user['name']); ?> (<?php echo $user['role']; ?>)</h1>
<p><a href="logout.php">Logout</a></p>

<?php if ($user['role'] === 'super_admin'): ?>
    <h2>Super Admin Actions</h2>
    <ul>
        <li><a href="invite.php">Invite Admin (create company)</a></li>
        <li><a href="urls.php">View all URLs (all companies)</a></li>
    </ul>
<?php else: ?>
    <h2>Company Actions</h2>
    <ul>
        <?php if ($user['role'] === 'admin'): ?>
            <li><a href="invite.php">Invite Admin/Member to my company</a></li>
        <?php endif; ?>
        <li><a href="urls.php">View URLs</a></li>
        <?php if (can_create_url()): ?>
            <li><a href="create_url.php">Create Short URL</a></li>
        <?php endif; ?>
    </ul>
<?php endif; ?>

</body>
</html>
