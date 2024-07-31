<?php 
include_once "../client.php";
$client = new Client();
// $api = "https://www.bible.com/_next/data/Z6GYQ1vZsA2F95Ssa1GBS/en/bible/1269/GEN.1.HMOWSV.json";
$api = "https://www.bible.com/_next/data/Z6GYQ1vZsA2F95Ssa1GBS/en/bible/";

// get bible id
$files = glob(__DIR__ . '/../data/bibles/*');
$bibles = [];
foreach ($files as $file) {
    $bible = json_decode(file_get_contents($file));
    $bibles[] = $bible;
}
$bibles = array_map('unserialize', array_unique(array_map('serialize', $bibles)));

$bible_id = $_GET['bible_id'];

$bible = array_filter($bibles, function ($item) use ($bible_id) {
    return $item->id == $bible_id;
});
if (count($bible) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Bible not found']);
    exit;
}
$bible = array_values($bible)[0];


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

// get verse
foreach ($books as $book) {
    $book_id = $book->id;
    $usfm = $book->usfm;
    $book_name = $book->name;

    foreach($book->chapters as $chapter) {
        $chapter_id = $chapter->id;
        $chapter_number = $chapter->number;
        // wait 1 second
        sleep(1);
        $verses = getVerse($client, $bible_id, "$chapter->code.$chapter->bible_code");
        break;
        
    }
}

function getVerse($client, $bible_id, $usfm)
{
    $api = "https://www.bible.com/_next/data/tTUWCsWY-8-dtBbuWceVo/en/bible/$bible_id/$usfm.json";
    $response = $client->Get($api); // Fetch API response
    // Check if the response was successful and contains valid JSON
    $jsonObj = json_decode($response);
    if (isset($jsonObj->pageProps->chapterInfo->content)) {
        $content = $jsonObj->pageProps->chapterInfo->content;
        // Extract verse data using regex
        preg_match_all('/<span class="verse v(\d+)"[^>]*><span class="label">\d+<\/span><span class="content">(.*?)<\/span><\/span>/s', $content, $matches, PREG_SET_ORDER);
        // Initialize the array
        $data = [];
        foreach ($matches as $match) {
            $verseNumber = $match[1];
            $content = strip_tags($match[2]);
            $data[$verseNumber] = trim($content);
        }
        return $data;
    }
    return null;
}



