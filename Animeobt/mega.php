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

$videoUrl = $data['data']['sources'][0]['url'];
$subtitles = $data['data']['tracks'][0]['file'];
$introStart = $data['data']['intro']['start'];
$introEnd = $data['data']['intro']['end'];
$outroStart = $data['data']['outro']['start'];
$outroEnd = $data['data']['outro']['end'];

if (!isset($_GET['animeId']) || empty($_GET['animeId'])) {
    die("Anime ID is required.");
}
include 'header.html'; 

$animeId = htmlspecialchars($_GET['animeId']);

// Endpoint for fetching episodes
$endpoint = '/api/v2/hianime/anime/' . $animeId . '/episodes';
$apiUrl = BASE_API_URL . $endpoint;

// Fetch data from API
$response = file_get_contents($apiUrl);
$data = json_decode($response, true);

if (!$data || !isset($data['status']) || $data['status'] !== 200) {
    die("Failed to fetch episodes.");
}

$episodes = $data['data']['episodes'];
$totalEpisodes = $data['data']['totalEpisodes'];

// Group episodes into sections of 100
$groupedEpisodes = [];
foreach ($episodes as $episode) {
    $groupIndex = floor(($episode['number'] - 1) / 100);
    $groupedEpisodes[$groupIndex][] = $episode;
}

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
    </style>
    <div class="wrap">
        <div id="player"></div>
        <button id="skipIntro">Skip Intro</button>
        <button id="skipOutro">Skip Outro</button>

        <!-- Add category switch buttons -->
        <div id="category-switch">
            <button id="subBtn" class="category-btn">Sub</button>
            <button id="dubBtn" class="category-btn">Dub</button>
        </div>
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
                    {
                        file: `<?php echo $englishSubtitleUrl; ?>`,
                        kind: "captions",
                        label: "English",
                        default: true
                    },
                    {
                        file: chaptersVtt,
                        kind: "chapters"
                    }
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

        playerInstance.on("time", function (event) {
            const currentTime = event.position;

            if (currentTime >= introStart && currentTime <= introEnd) {
                skipIntroButton.style.display = "block";
            } else {
                skipIntroButton.style.display = "none";
            }

            if (currentTime >= outroStart && currentTime <= outroEnd) {
                skipOutroButton.style.display = "block";
            } else {
                skipOutroButton.style.display = "none";
            }
        });

        // Function to reload the page with the selected category
        function switchCategory(newCategory) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('category', newCategory);
            window.location.search = urlParams.toString();
        }

        // Event listeners for category buttons
        subBtn.addEventListener("click", () => switchCategory('sub'));
        dubBtn.addEventListener("click", () => switchCategory('dub'));

        playerInstance.on("ready", function () {
            console.log("JW Player is ready!");
        });
    </script>

    <main class="container">
    <h1>Streaming Episodes for <?= htmlspecialchars($animeId) ?></h1>

    <?php foreach ($groupedEpisodes as $groupIndex => $group): ?>
        <section class="episode-section">
            <h2 class="collapsible" data-target="group-<?= $groupIndex ?>">
                Episodes <?= $groupIndex * 100 + 1 ?> - <?= min(($groupIndex + 1) * 100, $totalEpisodes) ?>
            </h2>
            <div id="group-<?= $groupIndex ?>" class="episode-grid hidden">
                <?php foreach ($group as $episode): ?>
                    <a href="player.php?episodeId=<?= $episode['episodeId'] ?>" class="episode-card">
                        <div class="episode-number">Episode <?= $episode['number'] ?></div>
                        <div class="episode-title"><?= htmlspecialchars($episode['title']) ?></div>
                        <?php if ($episode['isFiller']): ?>
                            <span class="filler-tag">Filler</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

</main>

<script>
    // JavaScript to toggle visibility of episode groups
    document.querySelectorAll('.collapsible').forEach(header => {
        header.addEventListener('click', () => {
            const targetId = header.getAttribute('data-target');
            const target = document.getElementById(targetId);
            target.classList.toggle('hidden');
        });
    });
</script>

    <?php include 'footer.html'; ?>
</body>

</html>
