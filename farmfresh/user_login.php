<?php
// Include database connection
include 'db_connect.php';

// Start session
session_start();

// Initialize error message
$error = "";

// Determine the redirection URL (default is homepage)
$redirect_to = isset($_GET['redirect_to']) ? $_GET['redirect_to'] : 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize inputs
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = "Both email and password are required.";
    } else {
        try {
            // Prepare query to fetch user details
            $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = :email AND role != 'admin'");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password and handle login
            if ($user && password_verify($password, $user['password'])) {
                // Store user ID and username in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // Redirect to the desired page
                header("Location: " . $redirect_to);
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error = "An error occurred while processing your request. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="user_register.php">Register</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>User Login</h1>
        
        <!-- Display error messages -->
        <?php if (!empty($error)): ?>
            <div class="error-messages">
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <!-- Login form -->
        <form action="user_login.php?redirect_to=<?php echo htmlspecialchars($redirect_to); ?>" method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn">Login</button>
        </form>
    </main>

    <footer>
        <p>&copy; 2025 FreshFarm Market Online. All rights reserved.</p>
    </footer>
</body>
</html>
