<?php
header('Content-Type: application/json');

// Allow sufficient time for data processing
ini_set('max_execution_time', 300); // 5 minutes
ini_set('memory_limit', '256M');

// Define the actions this API supports
$valid_actions = [
    'list',            // List all downloaded Bibles
    'details',         // Get details of a specific Bible
    'refresh_all',     // Refresh statistics for all Bibles
    'refresh_bible',   // Refresh statistics for a specific Bible
    'delete'           // Delete a Bible and its data
];

// Get the requested action
$action = $_GET['action'] ?? null;
$bible_id = $_GET['bible_id'] ?? null;
$bible_code = $_GET['bible_code'] ?? null;

// Validate action
if (!$action || !in_array($action, $valid_actions)) {
    sendResponse(false, 'Invalid action specified');
}

// Validate required parameters for specific actions
if (in_array($action, ['details', 'refresh_bible', 'delete']) && (!$bible_id || !$bible_code)) {
    sendResponse(false, 'Bible ID and code are required for this action');
}

// Process the request based on action
try {
    switch ($action) {
        case 'list':
            handleListBibles();
            break;
        case 'details':
            handleBibleDetails($bible_id, $bible_code);
            break;
        case 'refresh_all':
            handleRefreshAll();
            break;
        case 'refresh_bible':
            handleRefreshBible($bible_id, $bible_code);
            break;
        case 'delete':
            handleDeleteBible($bible_id, $bible_code);
            break;
    }
} catch (Exception $e) {
    sendResponse(false, 'Error processing request: ' . $e->getMessage());
}

/**
 * Handle listing all downloaded Bibles
 */
function handleListBibles() {
    // Get all Bible data files
    $bibles_data = [];
    $bibles_dir = __DIR__ . '/../data/bibles/';
    
    if (!is_dir($bibles_dir)) {
        sendResponse(false, 'Bible data directory not found');
    }
    
    $bible_files = glob($bibles_dir . '*.json');
    
    foreach ($bible_files as $bible_file) {
        if (file_exists($bible_file)) {
            $bible_data = json_decode(file_get_contents($bible_file));
            if ($bible_data) {
                $enhanced_data = enhanceBibleData($bible_data);
                $bibles_data[] = $enhanced_data;
            }
        }
    }
    
    // Sort by name
    usort($bibles_data, function($a, $b) {
        return strcasecmp($a->name, $b->name);
    });
    
    sendResponse(true, 'Successfully retrieved Bible list', $bibles_data);
}

/**
 * Handle getting details of a specific Bible
 */
function handleBibleDetails($bible_id, $bible_code) {
    // Get Bible data
    $bible_file = __DIR__ . "/../data/bibles/{$bible_id}-{$bible_code}.json";
    if (!file_exists($bible_file)) {
        sendResponse(false, 'Bible data not found');
    }
    
    $bible_data = json_decode(file_get_contents($bible_file));
    if (!$bible_data) {
        sendResponse(false, 'Failed to parse Bible data');
    }
    
    // Get enhanced Bible metadata
    $enhanced_bible = enhanceBibleData($bible_data);
    
    // Get book data
    $books_file = __DIR__ . "/../data/books/{$bible_id}-{$bible_code}.json";
    $books_data = [];
    
    if (file_exists($books_file)) {
        $books_data = json_decode(file_get_contents($books_file));
        
        // Check if books have verses downloaded
        if (is_array($books_data)) {
            foreach ($books_data as &$book) {
                $book->is_downloaded = checkBookVerses($bible_code, $book->code);
                $book->verse_count = countBookVerses($bible_code, $book->code);
            }
        }
    }
    
    $result = [
        'bible' => $enhanced_bible,
        'books' => $books_data
    ];
    
    sendResponse(true, 'Successfully retrieved Bible details', $result);
}

/**
 * Handle refreshing stats for all Bibles
 */
function handleRefreshAll() {
    // Get all Bible data files
    $bibles_dir = __DIR__ . '/../data/bibles/';
    
    if (!is_dir($bibles_dir)) {
        sendResponse(false, 'Bible data directory not found');
    }
    
    $bible_files = glob($bibles_dir . '*.json');
    $processed_count = 0;
    
    foreach ($bible_files as $bible_file) {
        if (file_exists($bible_file)) {
            $bible_data = json_decode(file_get_contents($bible_file));
            if ($bible_data && isset($bible_data->id) && isset($bible_data->code)) {
                // Update stats for this Bible
                updateBibleStats($bible_data->id, $bible_data->code);
                $processed_count++;
            }
        }
    }
    
    sendResponse(true, "Successfully refreshed stats for {$processed_count} Bibles");
}

/**
 * Handle refreshing stats for a specific Bible
 */
function handleRefreshBible($bible_id, $bible_code) {
    $updated = updateBibleStats($bible_id, $bible_code);
    
    if ($updated) {
        sendResponse(true, 'Successfully refreshed Bible stats');
    } else {
        sendResponse(false, 'Failed to refresh Bible stats');
    }
}

/**
 * Handle deleting a Bible and its data
 */
function handleDeleteBible($bible_id, $bible_code) {
    // Check if Bible exists
    $bible_file = __DIR__ . "/../data/bibles/{$bible_id}-{$bible_code}.json";
    if (!file_exists($bible_file)) {
        sendResponse(false, 'Bible data not found');
    }
    
    // Log which files and directories we're trying to delete
    $log = ["Attempting to delete Bible data: {$bible_id}-{$bible_code}"];
    
    try {
        // Files to delete
        $files_to_delete = [
            __DIR__ . "/../data/bibles/{$bible_id}-{$bible_code}.json",
            __DIR__ . "/../data/books/{$bible_id}-{$bible_code}.json"
        ];
        
        // Directories to delete
        $directories_to_delete = [
            __DIR__ . "/../data/verses/{$bible_code}",
            __DIR__ . "/../data/chapters/{$bible_id}-{$bible_code}"
        ];
        
        // Delete individual files
        foreach ($files_to_delete as $file) {
            if (file_exists($file)) {
                $log[] = "Deleting file: " . basename($file);
                if (!unlink($file)) {
                    $log[] = "Failed to delete file: " . basename($file);
                    throw new Exception("Failed to delete file: " . basename($file));
                }
            } else {
                $log[] = "File does not exist: " . basename($file);
            }
        }
        
        // Delete directories recursively
        foreach ($directories_to_delete as $directory) {
            if (is_dir($directory)) {
                $log[] = "Deleting directory: " . basename($directory);
                if (!deleteDirectory($directory)) {
                    $log[] = "Failed to delete directory: " . basename($directory);
                    throw new Exception("Failed to delete directory: " . basename($directory));
                }
            } else {
                $log[] = "Directory does not exist: " . basename($directory);
            }
        }
        
        // All operations succeeded
        sendResponse(true, 'Successfully deleted Bible data', ['log' => $log]);
    } catch (Exception $e) {
        sendResponse(false, 'Error deleting Bible data: ' . $e->getMessage(), ['log' => $log]);
    }
}

/**
 * Helper function to recursively delete a directory
 */
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    
    // Make sure we have necessary permissions
    @chmod($dir, 0777);
    
    $files = array_diff(scandir($dir), ['.', '..']);
    
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            if (!deleteDirectory($path)) {
                return false;
            }
        } else {
            // Make sure we have necessary permissions
            @chmod($path, 0666);
            
            if (!unlink($path)) {
                return false;
            }
        }
    }
    
    return rmdir($dir);
}

/**
 * Check if a book has verses downloaded
 */
function checkBookVerses($bible_code, $book_code) {
    $verses_dir = __DIR__ . "/../data/verses/{$bible_code}";
    $verses_file = $verses_dir . "/{$book_code}.json";
    
    return file_exists($verses_file);
}

/**
 * Count verses in a book
 */
function countBookVerses($bible_code, $book_code) {
    $verses_dir = __DIR__ . "/../data/verses/{$bible_code}";
    $verses_file = $verses_dir . "/{$book_code}.json";
    
    if (file_exists($verses_file)) {
        $verses = json_decode(file_get_contents($verses_file), true);
        return is_array($verses) ? count($verses) : 0;
    }
    
    return 0;
}

/**
 * Update stats for a Bible
 */
function updateBibleStats($bible_id, $bible_code) {
    // Bible file
    $bible_file = __DIR__ . "/../data/bibles/{$bible_id}-{$bible_code}.json";
    
    if (!file_exists($bible_file)) {
        return false;
    }
    
    // Books file
    $books_file = __DIR__ . "/../data/books/{$bible_id}-{$bible_code}.json";
    $books_data = [];
    
    if (file_exists($books_file)) {
        $books_data = json_decode(file_get_contents($books_file), true);
    }
    
    // Get Bible data
    $bible_data = json_decode(file_get_contents($bible_file), true);
    
    // Initialize counters
    $book_count = 0;
    $verse_count = 0;
    $size_bytes = 0;
    
    // Count books with verses
    $verses_dir = __DIR__ . "/../data/verses/{$bible_code}";
    
    if (is_dir($verses_dir)) {
        $verse_files = glob($verses_dir . '/*.json');
        
        foreach ($verse_files as $verse_file) {
            if (file_exists($verse_file)) {
                $book_count++;
                
                // Count verses in this book
                $verses = json_decode(file_get_contents($verse_file), true);
                if (is_array($verses)) {
                    $verse_count += count($verses);
                }
                
                // Add file size
                $size_bytes += filesize($verse_file);
            }
        }
    }
    
    // Update Bible data with stats
    $bible_data['book_count'] = $book_count;
    $bible_data['verse_count'] = $verse_count;
    $bible_data['size_bytes'] = $size_bytes;
    $bible_data['last_updated'] = date('Y-m-d H:i:s');
    
    // Save updated Bible data
    file_put_contents($bible_file, json_encode($bible_data, JSON_PRETTY_PRINT));
    
    return true;
}

/**
 * Enhance Bible data with additional information
 */
function enhanceBibleData($bible_data) {
    // Convert to array if it's an object
    $data = is_object($bible_data) ? (array)$bible_data : $bible_data;
    
    // Add language info if available
    $language_code = $data['language'] ?? null;
    if ($language_code) {
        $language_info = getLanguageInfo($language_code);
        $data['language_name'] = $language_info['name'] ?? null;
    }
    
    // Calculate stats if not present
    if (!isset($data['book_count']) || !isset($data['verse_count']) || !isset($data['size_bytes'])) {
        // Count books with verses
        $book_count = 0;
        $verse_count = 0;
        $size_bytes = 0;
        
        $verses_dir = __DIR__ . "/../data/verses/{$data['code']}";
        
        if (is_dir($verses_dir)) {
            $verse_files = glob($verses_dir . '/*.json');
            
            foreach ($verse_files as $verse_file) {
                if (file_exists($verse_file)) {
                    $book_count++;
                    
                    // Count verses in this book
                    $verses = json_decode(file_get_contents($verse_file), true);
                    if (is_array($verses)) {
                        $verse_count += count($verses);
                    }
                    
                    // Add file size
                    $size_bytes += filesize($verse_file);
                }
            }
        }
        
        $data['book_count'] = $book_count;
        $data['verse_count'] = $verse_count;
        $data['size_bytes'] = $size_bytes;
        
        // Update the file with these stats
        if (isset($data['id'])) {
            updateBibleStats($data['id'], $data['code']);
        }
    }
    
    // Make sure properties exist even if not used
    $data['book_count'] = $data['book_count'] ?? 0;
    $data['verse_count'] = $data['verse_count'] ?? 0;
    $data['size_bytes'] = $data['size_bytes'] ?? 0;
    $data['last_updated'] = $data['last_updated'] ?? date('Y-m-d H:i:s');
    
    // Convert back to object
    return is_object($bible_data) ? (object)$data : $data;
}

/**
 * Get language info from language code
 */
function getLanguageInfo($language_code) {
    $languages_file = __DIR__ . '/../data/languages.json';
    
    if (file_exists($languages_file)) {
        $languages = json_decode(file_get_contents($languages_file), true);
        
        if (is_array($languages)) {
            foreach ($languages as $language) {
                if (isset($language['code']) && $language['code'] === $language_code) {
                    return $language;
                }
            }
        }
    }
    
    return ['code' => $language_code, 'name' => null];
}

/**
 * Send JSON response
 */
function sendResponse($success, $message, $data = null) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
} 