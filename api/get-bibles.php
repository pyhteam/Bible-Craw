<?php 
$files = glob(__DIR__ . '/../data/bibles/*');
if(!$files){
    echo json_encode(array('success' => false, 'message' => 'Bible not found'));
    exit;
}
$bibles = [];
foreach ($files as $file) {
    $bible = json_decode(file_get_contents($file));
    $bibles[] = $bible;
}
$bibles = array_map('unserialize', array_unique(array_map('serialize', $bibles)));

header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $bibles]);



