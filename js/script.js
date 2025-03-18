function showModules(degreeId) {
    fetch(`get_modules.php?degree_id=${degreeId}`)
        .then(response => response.json())
        .then(modules => {
            let moduleList = document.getElementById('module-list');
            moduleList.innerHTML = '';
            modules.forEach(module => {
                moduleList.innerHTML += `<div class='tile' onclick='showTopics(${module.id})'>${module.title}</div>`;
            });
            document.getElementById('modules').style.display = 'block';
            document.getElementById('topics').style.display = 'none';
            document.getElementById('content').style.display = 'none';
        });
}

function showTopics(moduleId) {
    fetch(`get_topics.php?module_id=${moduleId}`)
        .then(response => response.json())
        .then(topics => {
            let topicList = document.getElementById('topic-list');
            topicList.innerHTML = '';
            topics.forEach(topic => {
                topicList.innerHTML += `<div class='tile' onclick='showContent(${topic.id})'>${topic.title}</div>`;
            });
            document.getElementById('topics').style.display = 'block';
            document.getElementById('content').style.display = 'none';
        });
}

function showContent(topicId) {
    fetch(`get_content.php?topic_id=${topicId}`)
        .then(response => response.json())
        .then(content => {
            let contentDisplay = document.getElementById('content-display');
            contentDisplay.innerHTML = `
                <p>${content.notes}</p>
                ${content.audio_path ? `<audio controls src="${content.audio_path}"></audio>` : ''}
                ${content.video_path ? `<video controls src="${content.video_path}"></video>` : ''}
            `;
            document.getElementById('content').style.display = 'block';
        });
}