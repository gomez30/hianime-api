<?php
require 'tohost.php';

// Get anime episode ID from URL
if (!isset($_GET['episodeId']) || empty($_GET['episodeId'])) {
    die("Episode ID is required.");
}

$episodeId = htmlspecialchars($_GET['episodeId']);
$server = isset($_GET['server']) ? htmlspecialchars($_GET['server']) : 'hd-2'; // Default server
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
    $data = json_decode($response, true);
    if (isset($data['data']['tracks'])) {
        $tracks = $data['data']['tracks'];
        foreach ($tracks as $track) {
            if (isset($track['label']) && $track['label'] === 'English') {
                $englishSubtitleUrl = $track['file'];
                break;
            }
        }
    } else {
        echo "No tracks found in the API response.";
    }
} else {
    echo "Error fetching API response.";
}
// $proxy = 'https://cors2-5q32r.bunny.run/master.m3u8?src=';

$proxy = 'https://m3u8-proxy-beta-opal.vercel.app/m3u8-proxy?url=';
$episodeSrcs = $data['data'];
$videoUrl = $data['data']['sources'][0]['url'];
$subtitles = $data['data']['tracks'][0]['file'];
$introStart = $data['data']['intro']['start'];
$introEnd = $data['data']['intro']['end'];
$outroStart = $data['data']['outro']['start'];
$outroEnd = $data['data']['outro']['end'];

// include 'header.html';
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #000;
            color: #f1f1f1;
        }

        /* Fullscreen Player Styling */
        #player {
            position: absolute;
            top: 0;
            left: 0;
            width: 100vw; /* Full width of the viewport */
            height: 100vh; /* Full height of the viewport */
            background-color: #000;
            border: none; /* Remove border */
            box-shadow: none; /* Remove box-shadow */
            z-index: 1000; /* Ensure the player is on top */
        }
   
/* Media Queries for smaller screens */
@media screen and (max-width: 768px) {
    #player {
        height: 60vh;
        width: 50vh;
    }

}

/* Subtitle Container Styles (Positioning and Styling) */
#skipIntro {
    z-index: 3;
    position: absolute;
    bottom: 25%;
    right: 5%;
    background-color: rgba(0, 0, 0, 0.6);
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    display: none;  /*Hidden initially */
}

#skipIntro:hover {
    background-color: rgba(0, 0, 0, 0.8);
}

#skipOutro {
    z-index: 3;
    position: absolute;
    bottom: 25%;
    right: 5%;
    background-color: rgba(0, 0, 0, 0.6);
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    display:none;  /*Hidden initially */
}

#skipOutro:hover {
    background-color: rgba(0, 0, 0, 0.8);
}

#category-switch {
    display: flex;
    justify-content: center; /* Center the buttons horizontally */
    gap: 15px; /* Add space between the buttons */
    margin-top: 20px; /* Add some space above the buttons */
}

.category-btn {
    padding: 10px 20px; /* Add padding for a better click area */
    font-size: 16px; /* Set a readable font size */
    cursor: pointer; /* Change cursor to pointer on hover */
    border: 2px solid #007bff; /* Border color matches the button color */
    border-radius: 5px; /* Rounded corners for buttons */
    background-color: #ffffff; /* Default background color */
    color: #007bff; /* Default text color */
    transition: all 0.3s ease; /* Smooth transition for hover effects */
}

.category-btn:hover {
    background-color: #007bff; /* Change background color on hover */
    color: #ffffff; /* Change text color on hover */
}

.category-btn.active {
    background-color: #007bff; /* Active background color */
    color: #ffffff; /* Active text color */
    border-color: #0056b3; /* Active border color */
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



    </style>
    <div class="wrap">
        <iframe
      src="https://megaplay.buzz/stream/s-2/<?php echo $episodeId; ?>/<?php echo $category; ?>"
      width="100%" height="100%"
      frameborder="0" scrolling="no" allowfullscreen
      style="min-height:60vh;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.7)"></iframe>
    </div>

    <?php
    function secondsToWebVTT($seconds) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = round($seconds % 60, 3);
        return sprintf("%02d:%02d:%06.3f", $hours, $minutes, $seconds);
    }

    $data = [
        "intro" => [
            "start" => $introStart,
            "end" => $introEnd
        ],
        "outro" => [
            "start" => $outroStart,
            "end" => $outroEnd
        ]
    ];

    $vttContent = "WEBVTT\n\n";
    foreach ($data as $chapter => $times) {
        $start = secondsToWebVTT($times['start']);
        $end = secondsToWebVTT($times['end']);
        $title = ucfirst($chapter);
        $vttContent .= "{$start} --> {$end}\n";
        $vttContent .= "{$title}\n\n";
    }

    echo "<script>const chaptersVtt = `{$vttContent}`;</script>";
    echo "<script>const skipData = " . json_encode($data) . ";</script>";
    ?>

</body>

</html>
