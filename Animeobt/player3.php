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

include 'header.html';
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="popstyle.css">
    <meta name="robots" content="noindex, nofollow" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <script src="https://code.jquery.com/jquery-3.6.3.min.js"
        integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>


<script type="text/javascript">
    var QSkmx_vyr_NKyUXc={"it":4430040,"key":"a9946"};
</script>
<script src="https://d2jiwo73gmsmk.cloudfront.net/aa697d8.js"></script>


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
    bottom: 25%;
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
    bottom: 25%;
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
            abouttext: "animeobt.xyz",
            aboutlink: "https://animeobt.xyz",
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
                description: "This Player is made by Animeobt",
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

    


    <!-- Popup Overlay -->
  <div class="popup-overlay" id="human-verification-popup" style="display: none;">

    <div class="popup-content">
      <img src="https://animesobt.great-site.net/logo.png" alt="Logo" class="popup-logo">
      <h2>Human Verification Required</h2>
      <p>Honored first-time user, kindly complete a quick verification to start streaming.</p>
      <button class="verify-btn" id="verify-btn" onclick="_ZU()">Verify Now</button>
      <p class="instructions">
        Click <strong>"Verify Now"</strong>, to view your Task offers. Please, Complete one task to start streaming.!
      </p>
      <a class="how-to-btn" href="#" id="how-to-btn" target="_blank">.</a>
      <p>Safe and Secure:</p>
      <!-- Instructions Container -->
      <div id="instructions-container" style="display: none;">
        <!-- Country-specific instructions will be added here dynamically -->
      </div>
      
      <!-- Online User Counter -->
      <div class="online-counter">
        <span class="counter-label">Users Online:</span>
        <span id="onlineCounter">7786</span>
        <span class="live-indicator"></span>
      </div>
      
      <!-- Comment Section -->
      <div class="comments-container">
        <div class="comments-header">
          <b>Comments</b>
          <hr />
        </div>
        <!-- Comment Input (optional) -->
        <div class="comment-input-wrapper">
          <input type="text" id="comment" placeholder="Add a Comment..." />
          <button id="post">Post</button>
        </div>
        <div class="premium-notice">
          <input id="check" type="checkbox" />
          <span>Only premium members can comment</span>
        </div>
        <!-- Comments List -->
        <div class="comments-list" id="commentsList">
          <!-- Group 1: First 10 comments -->
          <div class="comment" data-comment-id="1" data-offset="5">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile/Jayson.jpeg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Johnson</span>
                <span class="comment-timestamp">5 seconds ago</span>
              </div>
              <p class="comment-text">Spent less than 3 minutes to  install Opera GX browser (on Computer), this was my task, and Now I'm enjoying premium streaming—no ads, no hassle. Why look elsewhere?</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">1 Like</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="2" data-offset="37">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile/Philip.jpeg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Philip</span>
                <span class="comment-timestamp">37 seconds ago</span>
              </div>
              <p class="comment-text">Real talk: if you're tired of annoying ads, just give this a shot. It only takes a few minute and it works!</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">3 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="3" data-offset="60">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile/Miguel.jpeg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Miguel</span>
                <span class="comment-timestamp">1 minute ago</span>
              </div>
              <p class="comment-text">I didn’t think it would work at first, but dude, now I’m living that ad‑free, HD life. Get on it!</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">5 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="4" data-offset="420">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile/Derek.jpeg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Derek Lee</span>
                <span class="comment-timestamp">13 minutes ago</span>
              </div>
              <p class="comment-text">Just a minute of effort unlocked days of uninterrupted, premium streaming. It's amazing how such a tiny time investment makes a massive difference!</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">19 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="5" data-offset="1140">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile/Nicholas.jpeg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Nicholas</span>
                <span class="comment-timestamp">19 minutes ago</span>
              </div>
              <p class="comment-text">Honestly, I was like 'meh' until I tried it. Now I'm bingeing with no interruptions.</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">12 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="6" data-offset="1560">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile/FionaKim.jpeg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Fiona Kim</span>
                <span class="comment-timestamp">26 minutes ago</span>
              </div>
              <p class="comment-text">I didn’t believe it would work. Thanks Animeobt.</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">15 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="7" data-offset="1980">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile/Mateo.jpeg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Mateo</span>
                <span class="comment-timestamp">33 minutes ago</span>
              </div>
              <p class="comment-text">Tried scoping out other options, but they were all meh. I went back here, completed the task, and boom—premium mode activated!</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">18 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="8" data-offset="2460">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile/Hannah.jpeg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Hannah Wong</span>
                <span class="comment-timestamp">41 minutes ago</span>
              </div>
              <p class="comment-text">I just skipped the pop-up chaos—spent a few minutes here and got premium streaming without any ads.</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">22 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="9" data-offset="3240">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile/Theodore.jpeg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Theodore</span>
                <span class="comment-timestamp">54 minutes ago</span>
              </div>
              <p class="comment-text">This quick move turned my viewing experience upside down—in a flash, I got ad‑free, HD quality. Game changer!</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">27 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="10" data-offset="3540">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile/Adrian.jpeg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Adrian</span>
                <span class="comment-timestamp">59 minutes ago</span>
              </div>
              <p class="comment-text">I even looked around for something better, but nothing compared. Returned, gave it a shot, and now I'm streaming like a boss—no ads!</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">30 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <!-- Group 2: Next 6 comments (all 1 hour ago) -->
          <div class="comment" data-comment-id="11" data-offset="3600">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile/Arturo.jpeg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Arturo Mendez</span>
                <span class="comment-timestamp">1 hour ago</span>
              </div>
              <p class="comment-text">I grabbed my premium access in moments. No ads, just pure streaming bliss. Seriously, it’s the best choice!</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">80 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="12" data-offset="3600">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile/Caleb.jpeg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Caleb</span>
                <span class="comment-timestamp">1 hour ago</span>
              </div>
              <p class="comment-text">Took barely a minute or two, and now I'm all set with premium content. Animeobt thanks!</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">85 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="13" data-offset="3600">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile/Felea.jpeg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Felea Emanuel</span>
                <span class="comment-timestamp">1 hour ago</span>
              </div>
              <p class="comment-text">I thought I'd find a better option out there, but nothing could top this.</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">90 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="14" data-offset="3600">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile/James.jpeg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">James</span>
                <span class="comment-timestamp">1 hour ago</span>
              </div>
              <p class="comment-text">I spent a couple of minutes and escaped the pop-up nightmare. Now I'm all about that smooth, ad‑free experience.</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">95 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="15" data-offset="3600">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile/Johnson.png" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Jayson Hinrichsen</span>
                <span class="comment-timestamp">1 hour ago</span>
              </div>
              <p class="comment-text">Quick and easy: I got premium, ad‑free streaming in no time. Trust me, you'll regret not doing it.</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">100 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="16" data-offset="3600">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile/Kenneth.jpeg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Kenneth</span>
                <span class="comment-timestamp">1 hour ago</span>
              </div>
              <p class="comment-text">I saved myself from endless pop-ups by taking a quick detour. Now I'm watching premium content with zero ads. love it.</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">88 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <!-- Group 3: Last 4 comments (all 2 hours ago) -->
          <div class="comment" data-comment-id="17" data-offset="7200">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile/Olivia.jpeg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Olivia</span>
                <span class="comment-timestamp">2 hours ago</span>
              </div>
              <p class="comment-text">I gave it a try, completed the task super fast, and now I'm riding the ad‑free, AnimeOBT premium wave!</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">110 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="18" data-offset="7200">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile/Liam.jpeg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Liam Turner</span>
                <span class="comment-timestamp">2 hours ago</span>
              </div>
              <p class="comment-text">I made the smart move—spent less than 2 minutes here, and now I’m streaming premium content without ads.</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">135 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="19" data-offset="7200">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile/Blake.png" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Blake</span>
                <span class="comment-timestamp">2 hours ago</span>
              </div>
              <p class="comment-text">No pop-ups, no interruptions—just a few minutes to unlock ad‑free viewing experience.</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">160 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="20" data-offset="7200">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile/Rachel.jpeg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Rachel</span>
                <span class="comment-timestamp">2 hours ago</span>
              </div>
              <p class="comment-text">All it took was a minute, and now I'm set for days of premium, ad‑free streaming. A tiny effort that pays off big time!</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">187 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
        </div><!-- End comments-list -->
      </div><!-- End comments-container -->
    </div><!-- End popup-content -->
  </div><!-- End popup-overlay -->
  
  
     
     <script src="popscript.js"></script>

<div style="height: 400px;"></div>
<?php include 'footerPlayer.html'; ?>
</body>

</html>
