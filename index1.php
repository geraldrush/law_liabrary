<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Law Library</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js"></script>
</head>
<body>
    <h2>Law Degree Programs</h2>
    <div class="degree-tiles">
        <?php
        $stmt = $pdo->query("SELECT * FROM degree_programs");
        while ($degree = $stmt->fetch()) {
            echo "<div class='tile' onclick='showModules({$degree['id']})'>{$degree['title']}</div>";
        }
        ?>
    </div>

    <div id="modules" style="display:none;">
        <h3>Modules</h3>
        <div id="module-list"></div>
    </div>

    <div id="topics" style="display:none;">
        <h3>Topics</h3>
        <div id="topic-list"></div>
    </div>

    <div id="content" style="display:none;">
        <h3>Content</h3>
        <div id="content-display"></div>
    </div>
</body>
</html>