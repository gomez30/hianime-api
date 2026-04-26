<?php
require 'tohost.php';

define('BASE_API_URL', 'https://aniwatch-api-two-topaz.vercel.app');

$endpoint = '/api/v2/hianime/home';
$apiUrl = BASE_API_URL . $endpoint;

$response = file_get_contents($apiUrl);
$data = json_decode($response, true);

if (!$data || !isset($data['status']) || $data['status'] !== 200) {
    die(json_encode(['success' => false, 'message' => 'Failed to fetch spotlight data.']));
}

echo json_encode($data['data']['spotlightAnimes']);
?>
