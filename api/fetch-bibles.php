<?php

use App\Http\HttpClient;

include_once "../http-client.php";

$client = new HttpClient();
$api = "https://www.bible.com/api/bible/versions";

$params = [
    "language_tag" => $_GET['language'],
    "type" => "all"
];
header("Content-Type: application/json");

$api = $api . "?" . http_build_query($params);

$response = $client->get($api);
if(isset(($response))) {
    echo $response;
    return;
}
echo json_encode(["message" => "No data found"]);