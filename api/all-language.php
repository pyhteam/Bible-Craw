<?php
include_once "../client.php";

$client = new Client();
$api = "https://www.bible.com/api/bible/configuration";

$response = $client->Get($api);
$jsonObj = json_decode($response);
if ($jsonObj->response->code ==200) {
    $languages = [];
    foreach ($jsonObj->response->data->default_versions as $item) {
        $language = new stdClass();
        $language->id = $item->id;
        $language->code = $item->language_tag;
        $language->iso = $item->iso_639_3;
        $language->name = $item->name;
        $language->name_local = $item->local_name;
        $language->total_versions = $item->total_versions;
        $languages[] = $language;
    }
    // save to json file
    $fileLanguage = "../data/languages.json";
    if (!file_exists(dirname($fileLanguage))) {
        mkdir(dirname($fileLanguage), 0777, true);
    }
    file_put_contents($fileLanguage, json_encode($languages));
    
    echo json_encode($languages);
}