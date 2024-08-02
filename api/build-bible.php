<?php

use App\Http\HttpClient;

include_once "../http-client.php";

$client = new HttpClient();
$api = "https://www.bible.com/api/bible/version";

$id = $_GET['id'];
if ($id) {
    $api .= "/$id";

    $response = $client->Get($api);
    $jsonObj = json_decode($response);
    if ($jsonObj) {

        $bible = new stdClass();
        $bible->id = $jsonObj->id;
        $bible->language_code = $jsonObj->language->language_tag;
        $bible->code = $jsonObj->abbreviation;
        $bible->name = $jsonObj->local_title;
        $bible->name_en = $jsonObj->title;
        $bible->publisher = $jsonObj->publisher;
        $bible->copyright_long = $jsonObj->copyright_long;
        $bible->copyright_short = $jsonObj->copyright_short;
        $bible->language = $jsonObj->language;
        $bible->audio = $jsonObj->audio;
        $bible->audio_count = $jsonObj->audio_count;

        // save bible to json file
        $fileBible = "../data/bibles/$bible->id-$bible->code.json";
        if (!file_exists(dirname($fileBible))) {
            mkdir(dirname($fileBible), 0777, true);
        }
        file_put_contents($fileBible, json_encode($bible));

        // books
        $books = [];
        $index = 1;
        foreach ($jsonObj->books as $item) {
            $book = new stdClass();
            $book->id = $index;
            $book->bible_id = $jsonObj->id;
            $book->bible_code = $jsonObj->abbreviation;
            $book->bible_code = $jsonObj->abbreviation;
            $book->code = $item->usfm;
            $book->name_short = $item->abbreviation;
            $book->name_long = $item->human_long;
            $book->name = $item->human;

            // chapters
            $chapters = [];
            foreach ($item->chapters as $e) {
                $chapter = new stdClass();
                $chapter->id = intval($e->human);
                $chapter->bible_id = $bible->id;
                $chapter->bible_code = $bible->code;

                $chapter->book_id = $book->id;
                $chapter->book_code = $book->code;
                $chapter->name = $e->usfm;
                $chapter->code = $e->usfm;
                $chapters[] = $chapter;

                // $verses = getVerse($client, $bible->id, $e->usfm, $chapter->id, $book->id);
                // if ($verses) {
                //     $chapter->verses = $verses;
                // }
            }
            // save chapters to json file
            $fileChapter = "../data/chapters/$bible->id-$bible->code/$book->id-$book->code.json";
            // check if directory not exists
            if (!file_exists(dirname($fileChapter))) {
                mkdir(dirname($fileChapter), 0777, true);
            }

            file_put_contents($fileChapter, json_encode($chapters));

            $book->chapters = $chapters;
            $books[] = $book;
            $index++;
        }
        // save books to json file
        $fileBook = "../data/books/$bible->id-$bible->code.json";
        if (!file_exists(dirname($fileBook))) {
            mkdir(dirname($fileBook), 0777, true);
        }
        file_put_contents($fileBook, json_encode($books));


        $bible->books = $books;
        header('Content-Type: application/json');
        echo json_encode(
            [
                'code' => 200,
                'message' => 'Build bible success has been saved to json file'
            ]
        );
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
