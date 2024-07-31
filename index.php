<?php
$languages = json_decode(file_get_contents('data/languages.json'));


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bible Crawl</title>
    <!-- bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- fontawesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <!-- jquery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

</head>

<body>
    <nav>
        <ul class="nav justify-content-center">
            <li class="nav-item">
                <a class="nav-link active" href="/">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="?page=crawl-bible">Crawl Bible</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="?page=crawl-verse">Crawl Verse</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" target="_blank" href="https://github.com/pyhteam/Bible-Craw">Github Project</a>
            </li>
        </ul>
    </nav>
    <main>
        <div class="container">
            <div class="row">
               <?php 
               $page = $_GET['page'] ?? '';
                switch ($page) {
                    case 'crawl-bible':
                        include 'pages/crawl-bible.php';
                        break;
                    case 'crawl-verse':
                        include 'pages/crawl-verse.php';
                        break;
                    default:
                        include 'pages/crawl-bible.php';
                        break;
                }
               ?>
            </div>
        </div>
    </main>
    
</body>

</html>