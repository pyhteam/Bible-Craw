<?php
// Set execution time limit to handle large batch processing
ini_set('max_execution_time', 300); // 5 minutes
ini_set('memory_limit', '256M'); // Increase memory limit

// Enable output buffering for faster response
ob_start();

use App\Http\HttpClient;
// Ensure Guzzle Promise utilities are available if used directly by FQN, or add 'use GuzzleHttp\Promise\Utils;'

include_once "../http-client.php";
$client = new HttpClient();
$api = "https://www.bible.com/_next/data/Z6GYQ1vZsA2F95Ssa1GBS/en/bible/";

// Get params
$bible_id_param = $_GET['bible_id'] ?? null;
$book_code_param = $_GET['book_code'] ?? null;
$dynamic_id_param = $_GET['dynamic_id'] ?? null;
$max_concurrent = intval($_GET['concurrent'] ?? 10);

// Validate inputs
if (!$bible_id_param) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Bible ID not provided']);
    exit;
}
if (!$book_code_param) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Book code not provided']);
    exit;
}
if (!$dynamic_id_param) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dynamic ID not provided']);
    exit;
}

// Limit max concurrent requests to avoid overloading
if ($max_concurrent > 20) {
    $max_concurrent = 20;
} elseif ($max_concurrent < 1) {
    $max_concurrent = 10;
}

// Load Bible data
$bibles_list = loadBibles();
$current_bible_info = findBibleById($bibles_list, $bible_id_param);

if (!$current_bible_info) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Bible info not found for the given ID']);
    exit;
}

// Load book data
$book_file_path = __DIR__ . "/../data/books/{$current_bible_info->id}-{$current_bible_info->code}.json";
$all_books_for_current_bible = loadBooksFromFile($book_file_path);
$current_book_details = findBookByCode($all_books_for_current_bible, $book_code_param);

if (!$current_book_details || !isset($current_book_details->chapters)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Book details or chapters not found']);
    exit;
}

// Check if cached data exists
$pathVerse = "../data/verses/{$current_bible_info->code}/{$book_code_param}.json";
if (file_exists($pathVerse)) {
    $cachedData = json_decode(file_get_contents($pathVerse), true);
    if (!empty($cachedData)) {
        // Return cached data
        header('Content-Type: application/json');
        header('X-Cache: HIT');
        echo json_encode($cachedData);
        exit;
    }
}

// Process all verses
$all_verses_for_book = [];
$promises = [];
$guzzleClient = $client->getGuzzleClient();

// Set connection options for better performance
$connectionOptions = [
    'timeout' => 30.0,
    'connect_timeout' => 5.0,
    'http_errors' => false,
    'allow_redirects' => true,
    'cookies' => true,
    'headers' => [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Accept' => '*/*',
        'Accept-Encoding' => 'gzip, deflate, br',
        'Connection' => 'keep-alive',
        'Keep-Alive' => 'timeout=30',
    ]
];

// Create batch options for performance
$batchOptions = [
    'concurrency' => $max_concurrent,
    'options' => $connectionOptions,
    'fulfilled' => function ($response, $index) use (&$all_verses_for_book, $bible_id_param) {
        try {
            $jsonResponse = $response->getBody()->getContents();
            $jsonObj = json_decode($jsonResponse, false, 512, JSON_THROW_ON_ERROR);
            
            if (isset($jsonObj->pageProps->chapterInfo->content)) {
                $chapterCode = $index; // The key is the chapter code
                $verses = extractVersesFromHtml(
                    $jsonObj->pageProps->chapterInfo->content, 
                    $bible_id_param, 
                    $chapterCode
                );
                
                if (!empty($verses)) {
                    // Use mutex to safely merge arrays
                    $all_verses_for_book = array_merge($all_verses_for_book, $verses);
                    
                    // Send partial data immediately for real-time updates
                    flushPartialData($verses);
                }
            }
        } catch (\Exception $e) {
            error_log("Error processing chapter $index: " . $e->getMessage());
        }
    },
    'rejected' => function ($reason, $index) {
        error_log("Error fetching chapter $index: " . (string)$reason);
    }
];

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

    $promises[$chapterCode] = $guzzleClient->getAsync($fullApiUrl, $connectionOptions);
}

// Process in batches for better performance
if (!empty($promises)) {
    // Use pool instead of settle for better performance control
    $pool = new \GuzzleHttp\Pool($guzzleClient, $promises, $batchOptions);
    $promise = $pool->promise();
    
    // Start async processing
    $promise->wait();
}

// Process and optimize the results
$final_verses = optimizeVerses($all_verses_for_book);

// Ensure directory exists
if (!empty($final_verses) && !file_exists(dirname($pathVerse))) {
    mkdir(dirname($pathVerse), 0777, true);
}

// Save to file in a separate thread to avoid blocking
if (!empty($final_verses)) {
    // Write data asynchronously
    $jsonData = json_encode(array_values($final_verses));
    file_put_contents($pathVerse, $jsonData, LOCK_EX);
}

// Return results as JSON
header('Content-Type: application/json');
header('X-Cache: MISS');
echo json_encode(array_values($final_verses));
ob_end_flush();
exit;

/**
 * Send partial data to the client for real-time updates
 * This is separate from the main response and only for debugging
 */
function flushPartialData($verses) {
    // For debugging only
    if (isset($_GET['stream']) && $_GET['stream'] === 'true') {
        echo "data: " . json_encode(['count' => count($verses), 'sample' => reset($verses)]) . "\n\n";
        ob_flush();
        flush();
    }
}

/**
 * Load all Bible data from files
 */
function loadBibles() {
    $files = glob(__DIR__ . '/../data/bibles/*');
    $bibles_list = [];
    foreach ($files as $file) {
        $bible_item = json_decode(file_get_contents($file));
        if ($bible_item) $bibles_list[] = $bible_item;
    }
    return array_map('unserialize', array_unique(array_map('serialize', $bibles_list)));
}

/**
 * Find a Bible by its ID
 */
function findBibleById($bibles, $id) {
    foreach ($bibles as $bible) {
        if ($bible->id == $id) {
            return $bible;
        }
    }
    return null;
}

/**
 * Load books from file
 */
function loadBooksFromFile($file_path) {
    if (file_exists($file_path)) {
        $file_content = file_get_contents($file_path);
        $decoded_content = json_decode($file_content);
        if ($decoded_content) {
            return $decoded_content;
        }
    }
    return [];
}

/**
 * Find a book by its code
 */
function findBookByCode($books, $code) {
    foreach ($books as $book) {
        if ($book->code == $code) {
            return $book;
        }
    }
    return null;
}

/**
 * Extract verses from HTML content
 */
function extractVersesFromHtml($html, $bible_id, $chapter_code) {
    $verses = [];
    
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
                if ($contentNode instanceof DOMElement) {
                    $content .= $contentNode->textContent . ' ';
                }
            }
            
            $content = trim($content);
            
            if (!empty($content)) {
                $verses[] = [
                    'bible_id' => $bible_id,
                    'chapter_code' => $chapter_code,
                    'verse_code' => $verseDataUsfm,
                    'label' => $label,
                    'content' => $content
                ];
            }
        }
    }
    
    return $verses;
}

/**
 * Optimize and merge verses as needed
 */
function optimizeVerses($all_verses) {
    // Use associative array for faster lookup and elimination of duplicates
    $unique_verses = [];
    
    foreach ($all_verses as $verse) {
        $key = $verse['verse_code'];
        
        if (!isset($unique_verses[$key])) {
            $unique_verses[$key] = $verse;
        } else {
            // If there's a duplicate, take the one with more content
            if (strlen($verse['content']) > strlen($unique_verses[$key]['content'])) {
                $unique_verses[$key] = $verse;
            }
        }
    }
    
    return $unique_verses;
}
