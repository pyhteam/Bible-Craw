<?php
include_once "../client.php";

$client = new Client();
$api = "https://www.bible.com/api/bible/version";

$id = $_GET['id'];
if ($id) {
    $api .= "/$id";

    $response = $client->Get($api);
    $jsonObj = json_decode($response);
    if ($jsonObj) {

        $bible = new stdClass();
        $bible->id = $jsonObj->id;
        $bible->code = $jsonObj->abbreviation;
        $bible->name = $jsonObj->local_title;
        $bible->name_en = $jsonObj->title;
        $bible->publisher = $jsonObj->publisher;
        $bible->copyright_long = $jsonObj->copyright_long;
        $bible->copyright_short = $jsonObj->copyright_short;
        $bible->language = $jsonObj->language;
        $bible->audio = $jsonObj->audio;
        $bible->audio_count = $jsonObj->audio_count;

        // books
        $books = [];
        $index = 1;
        foreach ($jsonObj->books as $item) {
            $book = new stdClass();
            $book->id = $index;
            $book->bible_id = $jsonObj->id;
            $book->code = $item->usfm;
            $book->name_short = $item->abbreviation;
            $book->name_long = $item->human_long;

            // chapters
            $book->chapters = [];
            foreach ($item->chapters as $e) {
                $chapter = new stdClass();
                $chapter->id = intval($e->human);
                $chapter->book_id = $book->id;
                $chapter->name = $e->usfm;
                $chapter->code = $e->usfm;
                $book->chapters[] = $chapter;

                // $verses = getVerse($client, $bible->id, $e->usfm, $chapter->id, $book->id);
                // if ($verses) {
                //     $chapter->verses = $verses;
                // }
            }

            $books[] = $book;
            $index++;
        }
        echo json_encode($books);
    }
}

function getVerse($client, $bible_id, $usfm, $chapter_id, $book_id)
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
