<?php
session_start();
include 'config/database.php';
$db = (new Database())->getConnection();
$error = '';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'kasir') header('Location: admin/dashboard.php');
    elseif ($_SESSION['role'] === 'kitchen') header('Location: kitchen/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        if ($user['role'] === 'admin' || $user['role'] === 'kasir') header('Location: admin/dashboard.php');
        elseif ($user['role'] === 'kitchen') header('Location: kitchen/index.php');
        exit;
    } else {
        $error = "Username atau password salah.";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Login Staff</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="login-container">
        <form method="post" class="login-form">
            <h2>Login Staff Café</h2>
            <?php if ($error) echo "<p style='color:red;text-align:center;'>$error</p>"; ?>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>

</html>