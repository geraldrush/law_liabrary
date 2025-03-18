<?php
require_once 'config.php';
header('Content-Type: application/json');

$degree_id = $_GET['degree_id'];
$stmt = $pdo->prepare("SELECT * FROM modules WHERE degree_id = ?");
$stmt->execute([$degree_id]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>