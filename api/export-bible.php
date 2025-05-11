<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow sufficient time for data processing
ini_set('max_execution_time', 300); // 5 minutes
ini_set('memory_limit', '256M');

// Get parameters
$bible_id = $_GET['bible_id'] ?? null;
$bible_code = $_GET['bible_code'] ?? null;

// Validate required parameters
if (!$bible_id || !$bible_code) {
    outputJson(false, 'Bible ID and code are required');
    exit;
}

// Check if Bible data exists
$bible_file = __DIR__ . "/../data/bibles/{$bible_id}-{$bible_code}.json";
if (!file_exists($bible_file)) {
    outputJson(false, 'Bible data not found');
    exit;
}

try {
    // Prepare the ZIP file name
    $bible_data = json_decode(file_get_contents($bible_file), true);
    $bible_name = sanitizeFilename($bible_data['name'] ?? $bible_code);
    $archive_name = "bible_{$bible_code}_" . time();
    
    // Path to the output ZIP file
    $zip_file = __DIR__ . "/../data/temp/{$archive_name}.zip";
    
    // Create ZIP file directly using ZipArchive
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new Exception("Unable to create ZIP file");
        }
        
        // Add top-level directories first (exact structure)
        $zip->addEmptyDir('bibles');
        $zip->addEmptyDir('books');
        $zip->addEmptyDir('chapters');
        $zip->addEmptyDir('verses');
        
        // Add Bible file to bibles/
        $zip->addFile($bible_file, 'bibles/' . basename($bible_file));
        
        // Add Books file to books/
        $books_file = __DIR__ . "/../data/books/{$bible_id}-{$bible_code}.json";
        if (file_exists($books_file)) {
            $zip->addFile($books_file, 'books/' . basename($books_file));
        }
        
        // Add Chapters - preserving the directory structure
        $bible_chapters_dir = "{$bible_id}-{$bible_code}";
        $chapters_path = __DIR__ . "/../data/chapters/{$bible_chapters_dir}";
        
        if (is_dir($chapters_path)) {
            // Add chapters/ directory with Bible code subdirectory
            $zip->addEmptyDir("chapters/{$bible_chapters_dir}");
            
            // Add all chapter files
            $chapter_files = glob($chapters_path . '/*.json');
            foreach ($chapter_files as $file) {
                $zip->addFile($file, 'chapters/' . $bible_chapters_dir . '/' . basename($file));
            }
        }
        
        // Add Verses - preserving the directory structure
        $verses_path = __DIR__ . "/../data/verses/{$bible_code}";
        if (is_dir($verses_path)) {
            // Add verses/ directory with Bible code subdirectory
            $zip->addEmptyDir("verses/{$bible_code}");
            
            // Add all verse files
            $verse_files = glob($verses_path . '/*.json');
            foreach ($verse_files as $file) {
                $zip->addFile($file, 'verses/' . $bible_code . '/' . basename($file));
            }
        }
        
        // Create README file
        $readme = "# Bible Export: {$bible_data['name']}\n\n";
        $readme .= "Export Date: " . date('Y-m-d H:i:s') . "\n\n";
        $readme .= "## Contents\n\n";
        $readme .= "- `/bibles/` - Bible metadata\n";
        $readme .= "- `/books/` - Book information\n";
        $readme .= "- `/chapters/` - Chapter data\n";
        $readme .= "- `/verses/` - Verse data\n\n";
        $readme .= "## Statistics\n\n";
        $readme .= "- ID: {$bible_data['id']}\n";
        $readme .= "- Code: {$bible_data['code']}\n";
        $readme .= "- Name: {$bible_data['name']}\n";
        $readme .= "- Language: {$bible_data['language']}\n";
        $readme .= "- Books: " . (isset($bible_data['book_count']) ? $bible_data['book_count'] : 'N/A') . "\n";
        $readme .= "- Verses: " . (isset($bible_data['verse_count']) ? $bible_data['verse_count'] : 'N/A') . "\n";
        
        // Create temporary README file and add to ZIP
        $temp_readme = tempnam(sys_get_temp_dir(), 'readme');
        file_put_contents($temp_readme, $readme);
        $zip->addFile($temp_readme, 'README.md');
        
        // Close the ZIP file
        if (!$zip->close()) {
            throw new Exception("Error closing ZIP file: " . $zip->getStatusString());
        }
        
        // Remove temporary README file
        if (file_exists($temp_readme)) {
            unlink($temp_readme);
        }
        
        // Verify file was created
        if (!file_exists($zip_file) || filesize($zip_file) < 100) {
            throw new Exception("ZIP file was not created properly");
        }
    } else {
        throw new Exception("ZipArchive class is not available");
    }
    
    // Set appropriate headers for download
    header('Content-Description: File Transfer');
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $bible_name . '.zip"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($zip_file));
    
    // Clear output buffer and send file
    if (ob_get_level()) {
        ob_end_clean();
    }
    flush();
    
    // Output the file
    readfile($zip_file);
    
    // Clean up
    unlink($zip_file);
    
} catch (Exception $e) {
    // Clean up temporary files
    if (isset($zip_file) && file_exists($zip_file)) {
        unlink($zip_file);
    }
    if (isset($temp_readme) && file_exists($temp_readme)) {
        unlink($temp_readme);
    }
    
    // Return error message
    outputJson(false, 'Error creating export: ' . $e->getMessage());
}

/**
 * Outputs JSON response
 */
function outputJson($success, $message, $data = null) {
    header('Content-Type: application/json');
    $response = [
        'success' => $success,
        'message' => $message
    ];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
}

/**
 * Sanitize a filename
 */
function sanitizeFilename($filename) {
    // Remove any character that is not alphanumeric, a space, or a dash
    $filename = preg_replace('/[^\w\s-]/', '', $filename);
    // Replace spaces with underscores
    $filename = str_replace(' ', '_', $filename);
    // Remove multiple dashes or underscores
    $filename = preg_replace('/-+/', '-', $filename);
    
    return $filename;
} 