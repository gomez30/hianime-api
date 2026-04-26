<?php
require 'tohost.php';

// Get the date parameter from the URL (default to today)
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); 

define('BASE_API_URL', 'https://aniwatch-api-i02m.onrender.com');
$scheduleEndpoint = "/api/v2/hianime/schedule?date=$date";
$scheduleApiUrl = BASE_API_URL . $scheduleEndpoint;

$scheduleResponse = file_get_contents($scheduleApiUrl);
$scheduleData = json_decode($scheduleResponse, true);

if ($scheduleData && isset($scheduleData['status']) && $scheduleData['status'] === 200) {
    echo json_encode($scheduleData['data']['scheduledAnimes']);
} else {
    echo json_encode([]);
}
?>
