<?php

$bible_id = $_GET['bible_id'];
if (!isset($bible_id)) {
    echo json_encode(array('success' => false, 'message' => 'Bible not found'));
    exit;
}
// get all books
$filesBook = glob(__DIR__ . '/../data/books/*');
$books = [];
foreach ($filesBook as $file) {
    $book = json_decode(file_get_contents($file));
    $books[] = $book;
}
// merge array in books to one array
$books = array_merge(...$books);

// get all book by bible id
$books = array_filter($books, function ($item) use ($bible_id) {
    return $item->bible_id == $bible_id;
});

// flat array
$books = array_values($books);


header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $books]);