<?php
// Include database connection
include 'db_connect.php';

// Initialize error/success messages
$error = "";
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate inputs
    if (
        empty($name) || empty($phone) || empty($address) || 
        empty($username) || empty($email) || 
        empty($password) || empty($confirm_password)
    ) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!preg_match("/^\+?[0-9]{7,15}$/", $phone)) {
        $error = "Invalid phone number format. Use numbers only (with optional +).";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        try {
            // Check if email already exists (case-insensitive)
            $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(:email)");
            $stmt->execute(['email' => $email]);

            if ($stmt->rowCount() > 0) {
                $error = "Email is already registered.";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user into the database
                $insert_stmt = $conn->prepare("
                    INSERT INTO users (name, phone, address, username, email, password, role) 
                    VALUES (:name, :phone, :address, :username, :email, :password, 'user')
                ");
                $insert_stmt->execute([
                    'name' => $name,
                    'phone' => $phone,
                    'address' => $address,
                    'username' => $username,
                    'email' => $email,
                    'password' => $hashed_password,
                ]);

                // Redirect to login with success message
                header("Location: user_login.php?success=1");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="user_login.php">Login</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>User Registration</h1>
        <?php if (!empty($error)): ?>
            <div class="error-messages">
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="success-messages">
                <p><?php echo htmlspecialchars($success_message); ?></p>
            </div>
        <?php endif; ?>

        <form action="user_register.php" method="POST">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>

            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>

            <label for="address">Address:</label>
            <textarea id="address" name="address" required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Register</button>
        </form>
    </main>
	
	<footer>
        <p>&copy; 2025 FreshFarm Market Online. All rights reserved.</p>
    </footer>
	
</body>
</html>
