<?php
require_once 'config.php';
header('Content-Type: application/json');

$module_id = $_GET['module_id'];
$stmt = $pdo->prepare("SELECT * FROM topics WHERE module_id = ?");
$stmt->execute([$module_id]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>