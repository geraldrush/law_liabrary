<?php
require 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid topic ID");
}

$topic_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT t.title, c.notes, c.audio_path, c.video_path 
                       FROM topics t 
                       LEFT JOIN content c ON t.id = c.topic_id 
                       WHERE t.id = ?");
$stmt->execute([$topic_id]);
$topic = $stmt->fetch();

if (!$topic) {
    die("Topic not found");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($topic['title']); ?> - Law Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .notes-content {
            white-space: pre-wrap; /* Preserves line breaks and spaces */
            font-size: 14px;
        }
        h1 { font-size: 2.5rem; font-weight: bold; margin-bottom: 1rem; }
        h2 { font-size: 2rem; font-weight: bold; margin-bottom: 0.75rem; }
        p { margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1><?php echo htmlspecialchars($topic['title']); ?></h1>
        <h3>Notes</h3>
        <div class="notes-content"><?php echo $topic['notes'] ?: 'No notes available'; ?></div>

        <h3>Audio</h3>
        <?php if ($topic['audio_path']): ?>
            <audio controls>
                <source src="<?php echo htmlspecialchars($topic['audio_path']); ?>" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
        <?php else: ?>
            <p>No audio available</p>
        <?php endif; ?>

        <h3>Video</h3>
        <?php if ($topic['video_path']): ?>
            <video controls width="600">
                <source src="<?php echo htmlspecialchars($topic['video_path']); ?>" type="video/mp4">
                Your browser does not support the video element.
            </video>
        <?php else: ?>
            <p>No video available</p>
        <?php endif; ?>

        <a href="index.php" class="btn btn-secondary mt-3">Back to Programs</a>
    </div>
</body>
</html>