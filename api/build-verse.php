<?php

use App\Http\HttpClient;

include_once "../http-client.php";
$client = new HttpClient();
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
$book_code = $_GET['book_code'];
if (!isset($bible_id)) {
    echo json_encode(array('status' => 'error', 'message' => 'Bible not found'));
    exit;
}

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
$book = array_filter($books, function ($item) use ($bible_id, $book_code) {
    return $item->bible_id == $bible_id && $item->code == $book_code;
});
if (count($book) == 0) {
    echo json_encode(['success' => false, 'message' => 'Book not found']);
    exit;
}
$book = array_values($book)[0];
// get verse
$index = 0;
$verses = [];

foreach ($book->chapters as $chapter) {

    $params = [
        "bible_id" => $bible_id,
        "bible_code" => $book->bible_code,
        "chapter_code" => $chapter->code
    ];
    $chapterVerses = getVerse($client, $params);
    if ($chapterVerses !== null) {
        $verses = array_merge($verses, $chapterVerses);
    }
}


header('Content-Type: application/json');

$verses = array_reduce($verses, function ($carry, $item) {
    $key = $item['verse_code'];
    if (!isset($carry[$key])) {
        $carry[$key] = $item;
    } else {
        $carry[$key]['content'] .= ' ' . $item['content'];
    }
    return $carry;
}, []);

// save to file json
$pathVerse = "../data/verses/$bible->code/$book_code.json";
if (!file_exists(dirname($pathVerse))) {
    mkdir(dirname($pathVerse), 0777, true);
}
file_put_contents($pathVerse, json_encode(array_values($verses)));

echo json_encode(array_values($verses));

/**
 * Get verse from bible.com
 * @param HttpClient $client
 * @param array $params
 * @return array|null
 */

function getVerse($client, $params)
{
    $bibleId = $params['bible_id'];
    $chapterCode = $params['chapter_code'];
    $bibleCode = $params['bible_code'];
    $usfm = $chapterCode . "." . $bibleCode;

    $api = "https://www.bible.com/_next/data/mlt4CWVl9WY6P4NyqRua-/en/bible/$bibleId/$usfm.json";
    $query = [
        "versionId" => intval($bibleId),
        "usfm" => $usfm,
    ];
    $api = $api . "?" . http_build_query($query);

    $response = $client->Get($api);
    $jsonObj = json_decode($response);
    if (isset($jsonObj->pageProps->chapterInfo->content)) {
        $html = $jsonObj->pageProps->chapterInfo->content;

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($doc);
        $verseNodes = $xpath->query("//span[contains(@class, 'verse')]");

        $verses = [];
        foreach ($verseNodes as $verseNode) {
            $labelNode = $xpath->query(".//span[contains(@class, 'label')]", $verseNode)->item(0);
            $contentNodes = $xpath->query(".//span[contains(@class, 'content')]", $verseNode);
            $verseCode = $verseNode->getAttribute('data-usfm');

            if ($contentNodes->length > 0) {
                $label = $labelNode ? $labelNode->textContent : '';
                $content = '';

                foreach ($contentNodes as $contentNode) {
                    $content .= $contentNode->textContent . ' ';
                }

                $content = trim($content);

                if (!empty($content)) {
                    $verses[] = [
                        'bible_id' => $bibleId,
                        'chapter_code' => $chapterCode,
                        'verse_code' => $verseCode,
                        'label' => $label,
                        'content' => $content
                    ];
                }
            }
        }

        return $verses;
    }

    return null;
}
