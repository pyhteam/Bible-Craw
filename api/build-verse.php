<?php

use App\Http\HttpClient;
// Ensure Guzzle Promise utilities are available if used directly by FQN, or add 'use GuzzleHttp\Promise\Utils;'

include_once "../http-client.php";
$client = new HttpClient();
// $api = "https://www.bible.com/_next/data/Z6GYQ1vZsA2F95Ssa1GBS/en/bible/1269/GEN.1.HMOWSV.json";
$api = "https://www.bible.com/_next/data/Z6GYQ1vZsA2F95Ssa1GBS/en/bible/";

// get bible id information
$files = glob(__DIR__ . '/../data/bibles/*');
$bibles_list = []; // Renamed to avoid conflict
foreach ($files as $file) {
    $bible_item = json_decode(file_get_contents($file));
    if ($bible_item) $bibles_list[] = $bible_item;
}
$bibles_list = array_map('unserialize', array_unique(array_map('serialize', $bibles_list)));

$bible_id_param = $_GET['bible_id'] ?? null;
$book_code_param = $_GET['book_code'] ?? null;
$dynamic_id_param = $_GET['dynamic_id'] ?? null;

if (!$bible_id_param) {
    echo json_encode(array('status' => 'error', 'message' => 'Bible ID not found'));
    exit;
}
if (!$book_code_param) {
    echo json_encode(array('status' => 'error', 'message' => 'Book code not provided'));
    exit;
}
if (!$dynamic_id_param) {
    echo json_encode(array('status' => 'error', 'message' => 'Dynamic ID not provided'));
    exit;
}

$current_bible_info = null;
foreach ($bibles_list as $b) {
    if ($b->id == $bible_id_param) {
        $current_bible_info = $b;
        break;
    }
}

if (!$current_bible_info) {
    echo json_encode(['status' => 'error', 'message' => 'Bible info not found for the given ID']);
    exit;
}

// Load the specific book file for the given bible_id_param
$book_file_path = __DIR__ . "/../data/books/{$current_bible_info->id}-{$current_bible_info->code}.json";
$all_books_for_current_bible = [];
if (file_exists($book_file_path)) {
    $file_content = file_get_contents($book_file_path);
    $decoded_content = json_decode($file_content);
    if ($decoded_content) {
        $all_books_for_current_bible = $decoded_content;
    }
}

$current_book_details = null;
foreach ($all_books_for_current_bible as $b_detail) {
    if ($b_detail->code == $book_code_param) {
        $current_book_details = $b_detail;
        break;
    }
}

if (!$current_book_details || !isset($current_book_details->chapters)) {
    echo json_encode(['success' => false, 'message' => 'Book details or chapters not found for the given bible ID and book code']);
    exit;
}

$all_verses_for_book = [];
$promises = [];
$guzzleClient = $client->getGuzzleClient();

// Prepare promises for all chapters
foreach ($current_book_details->chapters as $chapter_item) {
    $chapterCode = $chapter_item->code;
    $usfm = $chapterCode . "." . $current_book_details->bible_code; 

    $apiUrl = "https://www.bible.com/_next/data/{$dynamic_id_param}/en/bible/{$bible_id_param}/{$usfm}.json";
    $query_params = [
        "versionId" => intval($bible_id_param),
        "usfm" => $usfm,
    ];
    $fullApiUrl = $apiUrl . "?" . http_build_query($query_params);

    $promises[$chapterCode] = $guzzleClient->requestAsync('GET', $fullApiUrl, [
        'headers' => [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Accept'=> '*/*',
            'Accept-Encoding'=> 'gzip, deflate, br',
        ]
    ]);
}

// Wait for all requests to complete
if (!empty($promises)) {
    $results = \GuzzleHttp\Promise\Utils::settle($promises)->wait();

    foreach ($results as $chapterCodeKey => $result) {
        if ($result['state'] === 'fulfilled') {
            $response = $result['value']; 
            $jsonResponse = $response->getBody()->getContents();
            $jsonObj = json_decode($jsonResponse);

            if (isset($jsonObj->pageProps->chapterInfo->content)) {
                $html = $jsonObj->pageProps->chapterInfo->content;
                $doc = new DOMDocument();
                libxml_use_internal_errors(true);
                // Add encoding to prevent issues with special characters
                $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html); 
                libxml_clear_errors();
                $xpath = new DOMXPath($doc);
                $verseNodes = $xpath->query("//span[contains(@class, 'verse')]");
                
                foreach ($verseNodes as $verseNode) {
                    if (!($verseNode instanceof DOMElement)) {
                        continue;
                    }
                    $labelNode = $xpath->query(".//span[contains(@class, 'label')]", $verseNode)->item(0);
                    $contentNodes = $xpath->query(".//span[contains(@class, 'content')]", $verseNode);
                    $verseDataUsfm = $verseNode->getAttribute('data-usfm');

                    if ($contentNodes->length > 0) {
                        $label = ($labelNode instanceof DOMElement) ? $labelNode->textContent : '';
                        $content = '';
                        foreach ($contentNodes as $contentNode) {
                             if ($contentNode instanceof DOMElement) $content .= $contentNode->textContent . ' ';
                        }
                        $content = trim($content);
                        if (!empty($content)) {
                            $all_verses_for_book[] = [
                                'bible_id' => $bible_id_param,
                                'chapter_code' => $chapterCodeKey, // This is the chapter code from the promise key
                                'verse_code' => $verseDataUsfm,
                                'label' => $label,
                                'content' => $content
                            ];
                        }
                    }
                }
            } else {
                 // error_log("Warning: chapterInfo->content not found for {$chapterCodeKey} in bible {$bible_id_param}");
            }
        } else {
            // error_log("Failed to fetch chapter {$chapterCodeKey} for bible {$bible_id_param}: " . (isset($result['reason']) ? $result['reason'] : 'Unknown error'));
        }
    }
}

header('Content-Type: application/json');

// Reduce/merge verses - this logic might need review based on how verse_code is structured
$final_verses = array_reduce($all_verses_for_book, function ($carry, $item) {
    $key = $item['verse_code']; 
    if (!isset($carry[$key])) {
        $carry[$key] = $item;
    } else {
        // If verse_code can be the same across different chapters (e.g., GEN.1.1, EXO.1.1)
        // this merging logic might be incorrect. 
        // For now, assuming original intent was to merge multi-span content for the *same verse ID*.
        // If verse_code is unique like BOOK.CHAPTER.VERSE (e.g. GEN.1.1), then this is fine.
        $carry[$key]['content'] .= ' ' . $item['content']; 
    }
    return $carry;
}, []);

// Save to file json
$pathVerse = "../data/verses/{$current_bible_info->code}/{$book_code_param}.json";
if (!file_exists(dirname($pathVerse))) {
    mkdir(dirname($pathVerse), 0777, true);
}
file_put_contents($pathVerse, json_encode(array_values($final_verses)));

echo json_encode(array_values($final_verses));

/**
 * Get verse from bible.com
 * @param HttpClient $client
 * @param array $params
 * @return array|null
 */

function getVerse($client, $params, $dynamic_id_from_param)
{
    $bibleId = $params['bible_id'];
    $chapterCode = $params['chapter_code'];
    $bibleCode = $params['bible_code'];
    $usfm = $chapterCode . "." . $bibleCode;

    $api = "https://www.bible.com/_next/data/$dynamic_id_from_param/en/bible/$bibleId/$usfm.json";
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
            if (!($verseNode instanceof DOMElement)) {
                continue; // Skip this iteration if $verseNode is not a DOMElement
            }
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
