<?php
require_once 'config.php';
header('Content-Type: application/json');

$topic_id = $_GET['topic_id'];
$stmt = $pdo->prepare("SELECT * FROM content WHERE topic_id = ?");
$stmt->execute([$topic_id]);
echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
?>