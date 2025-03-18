<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add
    if (isset($_POST['add_degree'])) {
        $stmt = $pdo->prepare("INSERT INTO degree_programs (title) VALUES (?)");
        $stmt->execute([$_POST['degree_title']]);
    } elseif (isset($_POST['add_module'])) {
        $stmt = $pdo->prepare("INSERT INTO modules (degree_id, title) VALUES (?, ?)");
        $stmt->execute([$_POST['degree_id'], $_POST['module_title']]);
    } elseif (isset($_POST['add_topic'])) {
        $stmt = $pdo->prepare("INSERT INTO topics (module_id, title) VALUES (?, ?)");
        $stmt->execute([$_POST['module_id'], $_POST['topic_title']]);
    } elseif (isset($_POST['add_content'])) {
        $audio_path = handleUpload('audio_file', 'audio');
        $video_path = handleUpload('video_file', 'video');
        $stmt = $pdo->prepare("INSERT INTO content (topic_id, notes, audio_path, video_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['topic_id'], $_POST['notes'], $audio_path, $video_path]);
    }
    // Update
    elseif (isset($_POST['update_degree'])) {
        $stmt = $pdo->prepare("UPDATE degree_programs SET title = ? WHERE id = ?");
        $stmt->execute([$_POST['degree_title'], $_POST['degree_id']]);
    } elseif (isset($_POST['update_module'])) {
        $stmt = $pdo->prepare("UPDATE modules SET title = ? WHERE id = ?");
        $stmt->execute([$_POST['module_title'], $_POST['module_id']]);
    } elseif (isset($_POST['update_topic'])) {
        $stmt = $pdo->prepare("UPDATE topics SET title = ? WHERE id = ?");
        $stmt->execute([$_POST['topic_title'], $_POST['topic_id']]);
    } elseif (isset($_POST['update_content'])) {
        $audio_path = handleUpload('audio_file', 'audio', $_POST['old_audio']);
        $video_path = handleUpload('video_file', 'video', $_POST['old_video']);
        $stmt = $pdo->prepare("UPDATE content SET notes = ?, audio_path = ?, video_path = ? WHERE id = ?");
        $stmt->execute([$_POST['notes'], $audio_path, $video_path, $_POST['content_id']]);
    }
    // Delete
    elseif (isset($_POST['delete_degree'])) {
        $stmt = $pdo->prepare("DELETE FROM degree_programs WHERE id = ?");
        $stmt->execute([$_POST['degree_id']]);
    } elseif (isset($_POST['delete_module'])) {
        $stmt = $pdo->prepare("DELETE FROM modules WHERE id = ?");
        $stmt->execute([$_POST['module_id']]);
    } elseif (isset($_POST['delete_topic'])) {
        $stmt = $pdo->prepare("DELETE FROM topics WHERE id = ?");
        $stmt->execute([$_POST['topic_id']]);
    } elseif (isset($_POST['delete_content'])) {
        $stmt = $pdo->prepare("DELETE FROM content WHERE id = ?");
        $stmt->execute([$_POST['content_id']]);
    }
}

// File upload handler
function handleUpload($field, $type, $old_path = '') {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] == UPLOAD_ERR_NO_FILE) return $old_path;
    $target_dir = "uploads/$type/";
    $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;
    $target_file = $target_dir . $filename;
    if (move_uploaded_file($_FILES[$field]['tmp_name'], $target_file)) return $target_file;
    return $old_path;
}

// Fetch data
$degrees = $pdo->query("SELECT * FROM degree_programs")->fetchAll();
$modules = $pdo->query("SELECT m.*, d.title AS degree_title FROM modules m JOIN degree_programs d ON m.degree_id = d.id")->fetchAll();
$topics = $pdo->query("SELECT t.*, m.title AS module_title FROM topics t JOIN modules m ON t.module_id = m.id")->fetchAll();
$content = $pdo->query("SELECT c.*, t.title AS topic_title FROM content c JOIN topics t ON c.topic_id = t.id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Content</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .accordion-button { padding: 0.5rem; font-size: 0.9rem; }
        .form-control, .btn { margin-bottom: 0.5rem; font-size: 0.9rem; }
        .list-group-item { padding: 0.5rem; }
        @media (max-width: 576px) { .accordion-button, .form-control { font-size: 0.8rem; padding: 0.4rem; } }
    </style>
</head>
<body>
    <div class="container my-2">
        <h1 class="h4">Manage Content</h1>
        <div class="accordion" id="manageAccordion">
            <!-- Degree Programs -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#degreeCollapse">Degrees</button>
                </h2>
                <div id="degreeCollapse" class="accordion-collapse collapse show" data-bs-parent="#manageAccordion">
                    <div class="accordion-body">
                        <form method="POST" class="mb-2">
                            <input type="text" name="degree_title" class="form-control" placeholder="Degree Title" required>
                            <button type="submit" name="add_degree" class="btn btn-sm btn-primary">Add</button>
                        </form>
                        <ul class="list-group">
                            <?php foreach ($degrees as $d): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <form method="POST" class="d-flex flex-grow-1">
                                        <input type="hidden" name="degree_id" value="<?php echo $d['id']; ?>">
                                        <input type="text" name="degree_title" class="form-control me-1" value="<?php echo htmlspecialchars($d['title']); ?>" required>
                                        <button type="submit" name="update_degree" class="btn btn-sm btn-success">Update</button>
                                        <button type="submit" name="delete_degree" class="btn btn-sm btn-danger ms-1">Delete</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Modules -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#moduleCollapse">Modules</button>
                </h2>
                <div id="moduleCollapse" class="accordion-collapse collapse" data-bs-parent="#manageAccordion">
                    <div class="accordion-body">
                        <form method="POST" class="mb-2">
                            <select name="degree_id" class="form-control" required>
                                <option value="">Select Degree</option>
                                <?php foreach ($degrees as $d): ?>
                                    <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="module_title" class="form-control" placeholder="Module Title" required>
                            <button type="submit" name="add_module" class="btn btn-sm btn-primary">Add</button>
                        </form>
                        <ul class="list-group">
                            <?php foreach ($modules as $m): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <form method="POST" class="d-flex flex-grow-1">
                                        <input type="hidden" name="module_id" value="<?php echo $m['id']; ?>">
                                        <input type="text" name="module_title" class="form-control me-1" value="<?php echo htmlspecialchars($m['title']); ?>" required>
                                        <button type="submit" name="update_module" class="btn btn-sm btn-success">Update</button>
                                        <button type="submit" name="delete_module" class="btn btn-sm btn-danger ms-1">Delete</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Topics -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#topicCollapse">Topics</button>
                </h2>
                <div id="topicCollapse" class="accordion-collapse collapse" data-bs-parent="#manageAccordion">
                    <div class="accordion-body">
                        <form method="POST" class="mb-2">
                            <select name="module_id" class="form-control" required>
                                <option value="">Select Module</option>
                                <?php foreach ($modules as $m): ?>
                                    <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['degree_title'] . ' - ' . $m['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="topic_title" class="form-control" placeholder="Topic Title" required>
                            <button type="submit" name="add_topic" class="btn btn-sm btn-primary">Add</button>
                        </form>
                        <ul class="list-group">
                            <?php foreach ($topics as $t): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <form method="POST" class="d-flex flex-grow-1">
                                        <input type="hidden" name="topic_id" value="<?php echo $t['id']; ?>">
                                        <input type="text" name="topic_title" class="form-control me-1" value="<?php echo htmlspecialchars($t['title']); ?>" required>
                                        <button type="submit" name="update_topic" class="btn btn-sm btn-success">Update</button>
                                        <button type="submit" name="delete_topic" class="btn btn-sm btn-danger ms-1">Delete</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#contentCollapse">Content</button>
                </h2>
                <div id="contentCollapse" class="accordion-collapse collapse" data-bs-parent="#manageAccordion">
                    <div class="accordion-body">
                        <form method="POST" enctype="multipart/form-data" class="mb-2">
                            <select name="topic_id" class="form-control" required>
                                <option value="">Select Topic</option>
                                <?php foreach ($topics as $t): ?>
                                    <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['module_title'] . ' - ' . $t['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <textarea name="notes" class="form-control" id="notes" placeholder="Notes"></textarea>
                            <input type="file" name="audio_file" class="form-control" accept="audio/*">
                            <input type="file" name="video_file" class="form-control" accept="video/*">
                            <button type="submit" name="add_content" class="btn btn-sm btn-primary">Add</button>
                        </form>
                        <ul class="list-group">
                            <?php foreach ($content as $c): ?>
                                <li class="list-group-item">
                                    <form method="POST" enctype="multipart/form-data" class="d-flex flex-column">
                                        <input type="hidden" name="content_id" value="<?php echo $c['id']; ?>">
                                        <input type="hidden" name="old_audio" value="<?php echo htmlspecialchars($c['audio_path']); ?>">
                                        <input type="hidden" name="old_video" value="<?php echo htmlspecialchars($c['video_path']); ?>">
                                        <small><?php echo htmlspecialchars($c['topic_title']); ?></small>
                                        <textarea name="notes" class="form-control" id="notes<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['notes']); ?></textarea>
                                        <input type="file" name="audio_file" class="form-control" accept="audio/*">
                                        <small><?php echo $c['audio_path'] ? basename($c['audio_path']) : 'No audio'; ?></small>
                                        <input type="file" name="video_file" class="form-control" accept="video/*">
                                        <small><?php echo $c['video_path'] ? basename($c['video_path']) : 'No video'; ?></small>
                                        <div>
                                            <button type="submit" name="update_content" class="btn btn-sm btn-success">Update</button>
                                            <button type="submit" name="delete_content" class="btn btn-sm btn-danger">Delete</button>
                                        </div>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <a href="dashboard.php" class="btn btn-sm btn-secondary mt-2">Back</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/8a646xyt5xppb15m5c4b83ip6i1wwy8erh9t84gl3jcq1sxn/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
    tinymce.init({
        selector: 'textarea',
        height: 400,
        menubar: true,
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount paste',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        content_style: 'body { font-size: 14px; }',
        media_live_embeds: true,
        forced_root_block: '', // Empty string to preserve plain text without wrapping
        valid_elements: '*[*]',
        extended_valid_elements: 'video[*],audio[*],h1,h2,h3,h4,h5,h6,p[*]',
        paste_as_text: false, // Keeps HTML formatting from clipboard
        paste_webkit_styles: 'all', // Preserves styles from WebKit browsers
        paste_retain_style_properties: 'all' // Retains all inline styles
    });
</script>
</html>