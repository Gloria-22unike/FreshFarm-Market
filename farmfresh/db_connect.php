<?php
// db_connect.php
$host = 'localhost';      // Database host
$dbname = 'farm_store';   // Database name
$username = 'root';       // Database username (default for XAMPP)
$password = '';           // Database password (default for XAMPP)

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Database Connection Failed: " . $e->getMessage();
    exit;
}
?>
