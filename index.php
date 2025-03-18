<?php
require 'config.php';
$stmt = $pdo->query("SELECT * FROM degree_programs");
$programs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Law Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .degree-tile { cursor: pointer; text-align: center; margin-bottom: 1rem; }
        .degree-tile img { width: 100%; height: 150px; object-fit: cover; }
        #modules-container { display: none; }
        .accordion-item { margin-bottom: 0.5rem; }
        .accordion-button { padding: 0.75rem; font-size: 1rem; }
        .accordion-body { padding: 1rem; }
        .topic-link { color: #0d6efd; }
        @media (max-width: 576px) { 
            .degree-tile img { height: 120px; }
            .accordion-button { font-size: 0.9rem; padding: 0.5rem; }
        }
    </style>
</head>
<body>
    <div class="container my-3">
        <h1 class="h3">Law Library</h1>

        <!-- Degree Tiles -->
        <div id="degrees-container" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
            <?php foreach ($programs as $program): ?>
                <div class="col degree-tile" data-degree-id="<?php echo $program['id']; ?>">
                    <div class="card">
                        <img src="uploads/images/<?php echo strtolower(str_replace(' ', '_', $program['title'])); ?>.jpg" 
                             alt="<?php echo htmlspecialchars($program['title']); ?>" 
                             class="card-img-top" 
                             onerror="this.src='https://via.placeholder.com/150?text=No+Image';">
                        <div class="card-body p-2">
                            <h5 class="card-title m-0"><?php echo htmlspecialchars($program['title']); ?></h5>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Modules Accordion -->
        <div id="modules-container">
            <button id="back-to-degrees" class="btn btn-sm btn-secondary mb-2">Back</button>
            <h2 id="degree-title" class="h4"></h2>
            <div id="modules-accordion" class="accordion"></div>
        </div>

        <a href="admin/login.php" class="btn btn-sm btn-primary mt-2">Admin</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const degrees = document.getElementById('degrees-container');
        const modules = document.getElementById('modules-container');
        document.querySelectorAll('.degree-tile').forEach(t => t.onclick = () => {
            const id = t.dataset.degreeId, title = t.querySelector('.card-title').textContent;
            degrees.style.display = 'none';
            modules.style.display = 'block';
            document.getElementById('degree-title').textContent = title;
            fetch(`get_modules.php?degree_id=${id}`)
                .then(r => r.json())
                .then(d => {
                    const acc = document.getElementById('modules-accordion');
                    acc.innerHTML = d.map((m, i) => `
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="h${m.id}">
                                <button class="accordion-button ${i ? 'collapsed' : ''}" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#c${m.id}" 
                                        aria-expanded="${i ? 'false' : 'true'}" aria-controls="c${m.id}">
                                    ${m.title}
                                </button>
                            </h2>
                            <div id="c${m.id}" class="accordion-collapse collapse ${i ? '' : 'show'}" 
                                 aria-labelledby="h${m.id}">
                                <div class="accordion-body">
                                    <ul class="list-unstyled m-0">
                                        ${m.topics.map(t => `<li><a href="topic.php?id=${t.id}" class="topic-link">${t.title}</a></li>`).join('')}
                                    </ul>
                                </div>
                            </div>
                        </div>`).join('');
                });
        });
        document.getElementById('back-to-degrees').onclick = () => {
            degrees.style.display = 'flex';
            modules.style.display = 'none';
        };
    </script>
</body>
</html>