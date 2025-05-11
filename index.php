<?php
$languages = json_decode(file_get_contents('data/languages.json'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bible Crawl - bible.com</title>
    <!-- Link to new Fluent UI CSS -->
    <link rel="stylesheet" href="css/fluent-ui.css">
    <!-- jQuery (still needed by pages for now, can be refactored later) -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Select2 (still needed by pages for now, can be refactored or replaced later) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <h1 class="sidebar-title">Bible Crawl</h1>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="/">Home (Crawl Bible)</a></li>
                    <li><a href="?page=crawl-bible">Crawl Bible</a></li>
                    <li><a href="?page=crawl-verse">Crawl Verse</a></li>
                    <li><a href="?page=manage-bibles">Quản lý Kinh Thánh</a></li>
                    <li><a href="https://github.com/pyhteam/Bible-Craw" target="_blank">Github Project</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <!-- Placeholder for a top bar if needed, e.g., breadcrumbs or user info -->
                <h2><?php 
                    $page = $_GET['page'] ?? 'crawl-bible'; 
                    echo ucwords(str_replace('-', ' ', $page)); 
                ?></h2>
            </header>
            <div class="content-area">
                <?php 
                // $page variable is already defined above
                switch ($page) {
                    case 'crawl-bible':
                        include 'pages/crawl-bible.php';
                        break;
                    case 'crawl-verse':
                        include 'pages/crawl-verse.php';
                        break;
                    case 'manage-bibles':
                        include 'pages/manage-bibles.php';
                        break;
                    default:
                        include 'pages/crawl-bible.php'; // Default to crawl-bible
                        break;
                }
               ?>
            </div>
        </main>
    </div>

    <!-- Link to new Fluent App JS -->
    <script src="js/fluent-app.js"></script>
</body>
</html>