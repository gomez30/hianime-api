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
        <div id="player"></div>
        <button id="skipIntro">Skip Intro</button>
        <button id="skipOutro">Skip Outro</button>

      
        
    </div>

    <script src="jw.js"></script>

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

    <script>
        const playerInstance = jwplayer("player").setup({
            controls: true,
            displaytitle: true,
            displaydescription: true,
            abouttext: "Anixtv.in",
            aboutlink: "https://anixtv.in",
            autostart: true,
            skin: {
                name: "netflix"
            },
            logo: {
                file: "",
                link: ""
            },
            playlist: [{
                title: `<?php echo $episodeId; ?>`,
                description: "This Player is made by Siddhartha Tiwari",
                image: "https://anixtv.in/player/anime.jpg",
                sources: [{ file: `<?php echo $videoUrl; ?>` }],
                tracks: [
                <?php
                $trackCount = count($episodeSrcs['tracks']);
                foreach ($episodeSrcs['tracks'] as $index => $track) {
                    echo "{";
                    echo "file: \"" . ($track['file'] ? $track['file'] : '') . "\",";
                    echo "kind: \"" . ($track['kind'] ? $track['kind'] : '') . "\",";
                    echo "label: \"" . ($track['label'] ? $track['label'] : '') . "\",";
                    echo "default: " . ($track['default'] ? 'true' : 'false');
                    echo "}";
                    if ($index < $trackCount - 1) {
                        echo ",";
                    }
                }
               ?>
            ]
            }]
        });

        const skipIntroButton = document.getElementById("skipIntro");
        const skipOutroButton = document.getElementById("skipOutro");
        const subBtn = document.getElementById("subBtn");
        const dubBtn = document.getElementById("dubBtn");

        const introStart = skipData.intro.start;
        const introEnd = skipData.intro.end;
        const outroStart = skipData.outro.start;
        const outroEnd = skipData.outro.end;

        skipIntroButton.addEventListener("click", function () {
            playerInstance.seek(introEnd);
        });

        skipOutroButton.addEventListener("click", function () {
            const videoDuration = playerInstance.getDuration();
            const skipToTime = outroEnd >= videoDuration ? videoDuration - 1 : outroEnd;
            playerInstance.seek(skipToTime);
        });

        playerInstance.on('ready', function () {
    // Add Skip Intro Button
    playerInstance.addButton(
        "https://anito.anixtv.in/images/SkipIntro.png", // URL to the icon image
        "Skip Intro", // Tooltip text
        function () {
            playerInstance.seek(introEnd);
        },
        "skip-intro" // Unique identifier for the button
    );

    // Add Skip Outro Button
    playerInstance.addButton(
        "https://anito.anixtv.in/images/SkipOutro.png", // URL to the icon image
        "Skip Outro", // Tooltip text
        function () {
            const videoDuration = playerInstance.getDuration();
            const skipToTime = outroEnd >= videoDuration ? videoDuration - 1 : outroEnd;
            playerInstance.seek(skipToTime);
        },
        "skip-outro" // Unique identifier for the button
    );

    console.log("Custom buttons added to JW Player!");
});

// Dynamically show/hide buttons based on current time
playerInstance.on("time", function (event) {
    const currentTime = event.position;

    const skipIntroButton = document.querySelector(".jw-button-container .jw-skip-intro");
    const skipOutroButton = document.querySelector(".jw-button-container .jw-skip-outro");

    // Show or hide Skip Intro button
    if (currentTime >= introStart && currentTime <= introEnd) {
        skipIntroButton.style.display = "block";
    } else {
        skipIntroButton.style.display = "none";
    }

    // Show or hide Skip Outro button
    if (currentTime >= outroStart && currentTime <= outroEnd) {
        skipOutroButton.style.display = "block";
    } else {
        skipOutroButton.style.display = "none";
    }
});
    </script>


</body>

</html>
