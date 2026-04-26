<?php
require 'tohost.php';

// Get anime episode ID from URL
if (!isset($_GET['episodeId']) || empty($_GET['episodeId'])) {
    die("Episode ID is required.");
}

$episodeId = htmlspecialchars($_GET['episodeId']);
$server = isset($_GET['server']) ? htmlspecialchars($_GET['server']) : 'hd-1'; // Default server
$category = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : 'sub'; // Default category (sub)

// Endpoint to get episode sources
$sourceEndpoint = "/api/v2/hianime/episode/sources?animeEpisodeId={$episodeId}&server={$server}&category={$category}";
$sourceUrl = BASE_API_URL . $sourceEndpoint;

// Fetch data from API
$response = file_get_contents($sourceUrl);
$data = json_decode($response, true);

if (!$data || !isset($data['status']) || $data['status'] !== 200) {
    die("Failed to fetch episode sources.");
}


// Check if the response is not empty
if ($response !== false) {
    // Decode the JSON response
    $data = json_decode($response, true); // 'true' converts it to an associative array

    // Check if decoding was successful and the tracks exist
    if (isset($data['data']['tracks'])) {
        $tracks = $data['data']['tracks'];

        // Search for the English subtitle file
        foreach ($tracks as $track) {
            if (isset($track['label']) && $track['label'] === 'English') {
                // Get the English subtitle file URL
                $englishSubtitleUrl = $track['file'];

                // Print or return the subtitle URL
                // echo "English Subtitle URL: " . $englishSubtitleUrl;
                break;
            }
        }
    } else {
        echo "No tracks found in the API response.";
    }
} else {
    echo "Error fetching API response.";
}



$videoUrl = $data['data']['sources'][0]['url']; // Use the first available source
$subtitles = $data['data']['tracks'][0]['file']; // Available subtitles
$introStart = $data['data']['intro']['start']; // Intro start time
$introEnd = $data['data']['intro']['end']; // Intro end time
$outroStart = $data['data']['outro']['start']; // Outro start time
$outroEnd = $data['data']['outro']['end']; // Outro end time

//Print the times
            // echo "Intro Start: $introStart, Intro End: $introEnd\n";
            // echo "Outro Start: $outroStart, Outro End: $outroEnd\n";
         
         
            
include 'header.html';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="style.css">
    <meta name="robots" content="noindex, nofollow" />
    <script src="https://code.jquery.com/jquery-3.6.3.min.js"
        integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
</head>
<body>


    <style>
    /* General Body Styles */
body {
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #141414;
    color: #f1f1f1;
}

/* Header styles (for navigation, if any) */
header {
    background-color: #000;
    padding: 15px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
}

/* Video container styling */
.wrap {
    position: relative;
    width: 100%;
    max-width: 1280px;
    margin: 0 auto;
    padding: 20px;
    background-color: #1f1f1f;
    border-radius: 8px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.4);
}

/* Player Styling */
#player {
    width: 100%;
    height: 70vh;
    background-color: #000;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.7);
    position: relative;
}

/* Button Styling */
.wrap .btn {
    position: absolute;
    top: 15%;
    right: 10%;
    background-color: #4CAF50; /* Green */
    color: white;
    font-size: 14px;
    padding: 10px 20px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    transition: background-color 0.3s ease;
    z-index: 10;
}

.wrap .btn:hover {
    background-color: #45a049;
}

/* Media Queries for smaller screens */
@media screen and (max-width: 768px) {
    #player {
        height: 60vh;
        width: 50vh;
    }

    .wrap .btn {
        top: 10%;
        right: 5%;
        font-size: 12px;
    }
}

/* Subtitle Container Styles (Positioning and Styling) */
#skipIntro {
    z-index: 3;
    position: absolute;
    bottom: 20%;
    right: 5%;
    background-color: rgba(0, 0, 0, 0.6);
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
      /*Hidden initially */
}

#skipIntro:hover {
    background-color: rgba(0, 0, 0, 0.8);
}

#skipOutro {
    z-index: 3;
    position: absolute;
    bottom: 20%;
    right: 5%;
    background-color: rgba(0, 0, 0, 0.6);
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
      /*Hidden initially */
}

#skipOutro:hover {
    background-color: rgba(0, 0, 0, 0.8);
}




/* JW Player Controls Styling */
.jwplayer .jw-controlbar {
    background: rgba(98, 64, 46, 0.5) !important;
    border-radius: 0 0 10px 10px;
}

.jwplayer .jw-icon-rewind, .jwplayer .jw-icon-next {
    filter: invert(100%);
}

.jwplayer .jw-playbar {
    background-color: #333 !important;
    border-radius: 5px;
}

/* Add a loading animation for the video player */
/*#player:before {*/
/*    content: "Loading...";*/
/*    position: absolute;*/
/*    top: 50%;*/
/*    left: 50%;*/
/*    transform: translate(-50%, -50%);*/
/*    font-size: 20px;*/
/*    color: #fff;*/
/*    display: block;*/
/*    z-index: 20;*/
/*}*/

    </style>
<div class="wrap">
    <iframe
      src="https://megaplay.buzz/stream/s-2/<?php echo $episodeId; ?>/<?php echo $category; ?>"
      width="100%" height="100%"
      frameborder="0" scrolling="no" allowfullscreen
      style="min-height:60vh;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.7)"></iframe>
</div>




<?php include 'footer.html'; ?>

</body>

</html>


