<?php
require 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['degree_id']) || !is_numeric($_GET['degree_id'])) {
    echo json_encode([]);
    exit;
}

$degree_id = $_GET['degree_id'];

// Fetch modules and their topics
$stmt = $pdo->prepare("SELECT m.id, m.title FROM modules m WHERE m.degree_id = ?");
$stmt->execute([$degree_id]);
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($modules as &$module) {
    $stmt = $pdo->prepare("SELECT id, title FROM topics WHERE module_id = ?");
    $stmt->execute([$module['id']]);
    $module['topics'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode($modules);
?>