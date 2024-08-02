<?php 
include_once "../client.php";

$client = new Client();
$api = "https://www.bible.com/api/bible/versions";

$params = [
    "language_tag" => $_GET['language'],
    "type" => "all"
];
header("Content-Type: application/json");
$response = $client->Get($api, $params);
if(isset(($response))) {
    echo $response;
    return;
}
echo json_encode(["message" => "No data found"]);