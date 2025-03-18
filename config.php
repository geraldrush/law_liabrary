<?php
$host = "localhost"; // Default XAMPP MySQL host";
$dbname = "law_library";
$username = "root"; // Default XAMPP MySQL username
$password = "";     // Default XAMPP MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>