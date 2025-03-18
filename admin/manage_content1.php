<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Handle POST requests for adding, editing, and deleting
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add Degree
    if (isset($_POST['add_degree'])) {
        $title = $_POST['degree_title'];
        $stmt = $pdo->prepare("INSERT INTO degree_programs (title) VALUES (?)");
        $stmt->execute([$title]);
    }

    // Edit Degree
    if (isset($_POST['edit_degree'])) {
        $id = $_POST['degree_id'];
        $title = $_POST['degree_title'];
        $stmt = $pdo->prepare("UPDATE degree_programs SET title = ? WHERE id = ?");
        $stmt->execute([$title, $id]);
    }

    // Delete Degree
    if (isset($_POST['delete_degree'])) {
        $id = $_POST['degree_id'];
        $stmt = $pdo->prepare("DELETE FROM degree_programs WHERE id = ?");
        $stmt->execute([$id]);
    }

    // Add Module
    if (isset($_POST['add_module'])) {
        $degree_id = $_POST['degree_id'];
        $title = $_POST['module_title'];
        $stmt = $pdo->prepare("INSERT INTO modules (degree_id, title) VALUES (?, ?)");
        $stmt->execute([$degree_id, $title]);
    }

    // Edit Module
    if (isset($_POST['edit_module'])) {
        $id = $_POST['module_id'];
        $title = $_POST['module_title'];
        $stmt = $pdo->prepare("UPDATE modules SET title = ? WHERE id = ?");
        $stmt->execute([$title, $id]);
    }

    // Delete Module
    if (isset($_POST['delete_module'])) {
        $id = $_POST['module_id'];
        $stmt = $pdo->prepare("DELETE FROM modules WHERE id = ?");
        $stmt->execute([$id]);
    }

    // Add Topic & Content
    if (isset($_POST['add_topic'])) {
        $module_id = $_POST['module_id'];
        $title = $_POST['topic_title'];
        $notes = $_POST['notes'];
        
        $audio_path = null;
        $video_path = null;
        
        if (isset($_FILES['audio']) && $_FILES['audio']['error'] == 0) {
            $audio_path = "../uploads/audio/" . uniqid() . "_" . basename($_FILES['audio']['name']);
            move_uploaded_file($_FILES['audio']['tmp_name'], $audio_path);
        }
        if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
            $video_path = "../uploads/video/" . uniqid() . "_" . basename($_FILES['video']['name']);
            move_uploaded_file($_FILES['video']['tmp_name'], $video_path);
        }

        $stmt = $pdo->prepare("INSERT INTO topics (module_id, title) VALUES (?, ?)");
        $stmt->execute([$module_id, $title]);
        $topic_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO content (topic_id, notes, audio_path, video_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$topic_id, $notes, $audio_path, $video_path]);
    }

    // Edit Topic & Content
    if (isset($_POST['edit_topic'])) {
        $topic_id = $_POST['topic_id'];
        $title = $_POST['topic_title'];
        $notes = $_POST['notes'];
        
        $audio_path = $_POST['existing_audio'] ?? null;
        $video_path = $_POST['existing_video'] ?? null;

        if (isset($_FILES['audio']) && $_FILES['audio']['error'] == 0) {
            $audio_path = "../uploads/audio/" . uniqid() . "_" . basename($_FILES['audio']['name']);
            move_uploaded_file($_FILES['audio']['tmp_name'], $audio_path);
        }
        if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
            $video_path = "../uploads/video/" . uniqid() . "_" . basename($_FILES['video']['name']);
            move_uploaded_file($_FILES['video']['tmp_name'], $video_path);
        }

        $stmt = $pdo->prepare("UPDATE topics SET title = ? WHERE id = ?");
        $stmt->execute([$title, $topic_id]);

        $stmt = $pdo->prepare("UPDATE content SET notes = ?, audio_path = ?, video_path = ? WHERE topic_id = ?");
        $stmt->execute([$notes, $audio_path, $video_path, $topic_id]);
    }

    // Delete Topic
    if (isset($_POST['delete_topic'])) {
        $topic_id = $_POST['topic_id'];
        $stmt = $pdo->prepare("DELETE FROM content WHERE topic_id = ?");
        $stmt->execute([$topic_id]);
        $stmt = $pdo->prepare("DELETE FROM topics WHERE id = ?");
        $stmt->execute([$topic_id]);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Content</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h2>Manage Content</h2>
    <a href="dashboard.php">Back to Dashboard</a>

    <!-- Degree Programs -->
    <h3>Degree Programs</h3>
    <form method="POST">
        <input type="text" name="degree_title" placeholder="Degree Title" required>
        <button type="submit" name="add_degree">Add Degree</button>
    </form>
    <table>
        <tr><th>ID</th><th>Title</th><th>Actions</th></tr>
        <?php
        $stmt = $pdo->query("SELECT * FROM degree_programs");
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['title']}</td>";
            echo "<td>
                <form method='POST' style='display:inline;'>
                    <input type='hidden' name='degree_id' value='{$row['id']}'>
                    <input type='text' name='degree_title' value='{$row['title']}' required>
                    <button type='submit' name='edit_degree'>Edit</button>
                </form>
                <form method='POST' style='display:inline;' onsubmit='return confirm(\"Are you sure?\");'>
                    <input type='hidden' name='degree_id' value='{$row['id']}'>
                    <button type='submit' name='delete_degree'>Delete</button>
                </form>
            </td>";
            echo "</tr>";
        }
        ?>
    </table>

    <!-- Modules -->
    <h3>Modules</h3>
    <form method="POST">
        <select name="degree_id" required>
            <option value="">Select Degree</option>
            <?php
            $stmt = $pdo->query("SELECT * FROM degree_programs");
            while ($row = $stmt->fetch()) {
                echo "<option value='{$row['id']}'>{$row['title']}</option>";
            }
            ?>
        </select>
        <input type="text" name="module_title" placeholder="Module Title" required>
        <button type="submit" name="add_module">Add Module</button>
    </form>
    <table>
        <tr><th>ID</th><th>Degree</th><th>Title</th><th>Actions</th></tr>
        <?php
        $stmt = $pdo->query("SELECT m.id, m.title, d.title AS degree_title FROM modules m JOIN degree_programs d ON m.degree_id = d.id");
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['degree_title']}</td>";
            echo "<td>{$row['title']}</td>";
            echo "<td>
                <form method='POST' style='display:inline;'>
                    <input type='hidden' name='module_id' value='{$row['id']}'>
                    <input type='text' name='module_title' value='{$row['title']}' required>
                    <button type='submit' name='edit_module'>Edit</button>
                </form>
                <form method='POST' style='display:inline;' onsubmit='return confirm(\"Are you sure?\");'>
                    <input type='hidden' name='module_id' value='{$row['id']}'>
                    <button type='submit' name='delete_module'>Delete</button>
                </form>
            </td>";
            echo "</tr>";
        }
        ?>
    </table>

    <!-- Topics & Content -->
    <h3>Topics & Content</h3>
    <form method="POST" enctype="multipart/form-data">
        <select name="module_id" required>
            <option value="">Select Module</option>
            <?php
            $stmt = $pdo->query("SELECT m.id, m.title, d.title AS degree_title FROM modules m JOIN degree_programs d ON m.degree_id = d.id");
            while ($row = $stmt->fetch()) {
                echo "<option value='{$row['id']}'>{$row['degree_title']} - {$row['title']}</option>";
            }
            ?>
        </select>
        <input type="text" name="topic_title" placeholder="Topic Title" required><br>
        <textarea name="notes" placeholder="Detailed Notes" rows="5"></textarea><br>
        <input type="file" name="audio" accept="audio/*"><br>
        <input type="file" name="video" accept="video/*"><br>
        <button type="submit" name="add_topic">Add Topic & Content</button>
    </form>
    <table>
        <tr><th>ID</th><th>Module</th><th>Topic</th><th>Notes</th><th>Audio</th><th>Video</th><th>Actions</th></tr>
        <?php
        $stmt = $pdo->query("SELECT t.id, t.title AS topic_title, m.title AS module_title, c.notes, c.audio_path, c.video_path 
                             FROM topics t 
                             JOIN modules m ON t.module_id = m.id 
                             LEFT JOIN content c ON t.id = c.topic_id");
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['module_title']}</td>";
            echo "<td>{$row['topic_title']}</td>";
            echo "<td>" . (strlen($row['notes']) > 50 ? substr($row['notes'], 0, 50) . "..." : $row['notes']) . "</td>";
            echo "<td>" . ($row['audio_path'] ? basename($row['audio_path']) : "None") . "</td>";
            echo "<td>" . ($row['video_path'] ? basename($row['video_path']) : "None") . "</td>";
            echo "<td>
                <form method='POST' enctype='multipart/form-data' style='display:inline;'>
                    <input type='hidden' name='topic_id' value='{$row['id']}'>
                    <input type='text' name='topic_title' value='{$row['topic_title']}' required>
                    <textarea name='notes'>{$row['notes']}</textarea>
                    <input type='hidden' name='existing_audio' value='{$row['audio_path']}'>
                    <input type='hidden' name='existing_video' value='{$row['video_path']}'>
                    <input type='file' name='audio' accept='audio/*'>
                    <input type='file' name='video' accept='video/*'>
                    <button type='submit' name='edit_topic'>Edit</button>
                </form>
                <form method='POST' style='display:inline;' onsubmit='return confirm(\"Are you sure?\");'>
                    <input type='hidden' name='topic_id' value='{$row['id']}'>
                    <button type='submit' name='delete_topic'>Delete</button>
                </form>
            </td>";
            echo "</tr>";
        }
        ?>
    </table>
</body>
</html>