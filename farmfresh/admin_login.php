<?php
include 'db_connect.php';
session_start();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT id, password FROM admins WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                header("Location: admin_dashboard.php");
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $error = "An unexpected error occurred. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .error-message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="admin_register.php">Admin Register</a></li>
            </ul>
        </nav>
    </header>

    <main class="form-container">
        <h1>Admin Login</h1>
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="admin_login.php" method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </main>

    <footer>
        <p>&copy; 2025 FreshFarm Market Online. All rights reserved.</p>
    </footer>
</body>
</html>
