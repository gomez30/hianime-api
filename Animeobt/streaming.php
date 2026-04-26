<?php
require 'api-client.php';

if (!isset($_GET['animeId']) || empty($_GET['animeId'])) {
    die("Anime ID is required.");
}
include 'header.html';

$animeId = htmlspecialchars($_GET['animeId']);

$normalized = animeobt_get_episodes($animeId);
$episodes = $normalized['episodes'];
$totalEpisodes = $normalized['totalEpisodes'];

// Verify the fetched data
if (empty($episodes)) {
    die("No episodes found for this title yet.");
}

// Group episodes into sections of 100
$groupedEpisodes = [];
foreach ($episodes as $episode) {
    $groupIndex = floor(($episode['number'] - 1) / 100);
    $groupedEpisodes[$groupIndex][] = $episode;
}

function getHianimeEpId($episodeIdString) {
    if (is_numeric($episodeIdString)) return $episodeIdString;
    $parsed = parse_url($episodeIdString);
    if (isset($parsed['query'])) {
        parse_str($parsed['query'], $out);
        if (isset($out['ep'])) return $out['ep'];
    }
    if (preg_match('/episode-(\d+)/i', $episodeIdString, $m)) {
        return $m[1];
    }
    if (preg_match('/(\d+)$/', $episodeIdString, $m)) {
        return $m[1];
    }
    return $episodeIdString;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="popstyle.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon" />


    <title><?= htmlspecialchars($animeId) ?></title>


<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-PFTJHZMK04"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-PFTJHZMK04');
</script>



    
    <script type="text/javascript">
    var gdzrw_uyq_zcnEEc={"it":4430040,"key":"a9946"};
</script>
<script src="https://d1qt1z4ccvak33.cloudfront.net/a8ca45a.js"></script>



    <style>
        /* General Variables */
        :root {
            --background-dark: #1b1e28;
            --background-medium: #232635;
            --background-light: #2e3245;
            --highlight-pink: #ffccdd;
            --text-color: #eaeaea;
            --text-muted: #aaa;
            --border-color: #444;
            --transition-duration: 0.3s;
        }

        /* General Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--background-dark);
            color: var(--text-color);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 20px;
            width: 90%;
            margin: 0 auto;
            padding: 20px;
            min-height: 100vh;
        }

        /* Navigation Styles */
        .episode-nav {
            background-color: var(--background-medium);
            padding: 15px;
            border-radius: 8px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--background-light) var(--background-dark);
            max-height: 100vh;
        }

        .episode-nav h2 {
            text-align: center;
            margin-bottom: 15px;
            font-size: 18px;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .episode-section h3 {
            cursor: pointer;
            padding: 8px;
            margin: 0;
            background-color: var(--background-light);
            color: var(--text-color);
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color var(--transition-duration);
        }

        .episode-section h3:hover {
            background-color: var(--highlight-pink);
            color: var(--background-dark);
        }

        .hidden {
            display: none;
        }

        /* Episode Card */
        .episode-card {
            background-color: var(--background-light);
            padding: 10px;
            border-radius: 5px;
            margin: 5px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid transparent;
            transition: background-color var(--transition-duration), transform var(--transition-duration);
        }

        .episode-card:hover {
            background-color: var(--highlight-pink);
            transform: scale(1.02);
            color: var(--background-dark);
        }

        .episode-card.active {
            background-color: var(--highlight-pink);
            color: var(--background-dark);
        }

        .episode-number, .episode-title {
            font-size: 14px;
        }

        .filler-tag {
            background-color: #ff6b6b;
            color: #fff;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 12px;
        }

        /* Player Styles */
        .player-container {
        background-color: var(--background-medium);
        border-radius: 8px;
        padding: 0; /* Removed extra padding */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
        overflow: hidden; /* Ensures no extra space from overflowing content */
        }

        iframe {
        width: 100%;
        height: 500px;
        border: none;
        background-color: none;
        margin: 0; /* Remove default margins */
        border-radius: 0; /* Adjust this if you don't need rounded corners */
        }


        .player-options {
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-top: 10px;
            gap: 10px;
        }

        .player-options select {
            background-color: var(--background-light);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            border-radius: 5px;
            padding: 8px;
            font-size: 14px;
            cursor: pointer;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }

            iframe {
                height: 300px;
            }
        }

        @media (min-width: 1024px) {
            iframe {
                height: 700px;
            }
        }
        a {
    text-decoration: none; /* Remove underline */
    color: var(--text-color); /* Set text color to the defined variable */
}

a:hover {
    text-decoration: none; /* Ensure no underline on hover */
    color: var(--text-color); /* Keep text color the same on hover */
}
.episode-number, .episode-title {
    font-size: 14px;
    color: var(--text-color) !important; /* Ensures the color is white */
    text-decoration: none !important; /* Ensures no underline */
}

    </style>
    
   

    
    
</head>
<body>
    <main class="container">
        <!-- Episode Navigation -->
        <nav class="episode-nav">
            <h2>Episodes</h2>
            <?php foreach ($groupedEpisodes as $groupIndex => $group): ?>
                <section class="episode-section">
                    <h3 class="collapsible" data-target="group-<?= $groupIndex ?>">
                        Episodes <?= $groupIndex * 100 + 1 ?> - <?= min(($groupIndex + 1) * 100, $totalEpisodes) ?>
                        <span>+</span>
                    </h3>
                    <div id="group-<?= $groupIndex ?>" class="episode-grid hidden">
                        <?php foreach ($group as $episode): ?>
                            <a href="#"
                               class="episode-card"
                               data-episode-id="<?= htmlspecialchars(getHianimeEpId($episode['episodeId'])) ?>"
                               onclick="loadEpisode(event, '<?= htmlspecialchars(getHianimeEpId($episode['episodeId'])) ?>')">
                                <div class="episode-number">Ep <?= $episode['number'] ?></div>
                                <div class="episode-title"><?= htmlspecialchars($episode['title']) ?></div>
                                <?php if ($episode['isFiller']): ?>
                                    <span class="filler-tag">Filler</span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </nav>

        <!-- Player -->
        <section class="player-container">
    <iframe id="episode-player"
        src="<?= !empty($episodes[0]['episodeId']) ? 'https://megaplay.buzz/stream/s-2/' . getHianimeEpId($episodes[0]['episodeId']) . '/sub' : '' ?>"
        width="100%" height="500px" frameborder="0" scrolling="no" allowfullscreen></iframe>
    <div class="player-options">
        <div>
            <label for="language-select">Language:</label>
            <select id="language-select" onchange="updatePlayer()">
                <option value="sub">Sub</option>
                <option value="dub">Dub</option>
            </select>
        </div>
    </div>

</section>
    <?php if (file_exists(__DIR__ . '/detailstream.php')) include 'detailstream.php'; ?>
    </main>

    <script>
        // Toggle episode group visibility
        document.querySelectorAll('.collapsible').forEach(header => {
            header.addEventListener('click', () => {
                const targetId = header.getAttribute('data-target');
                const target = document.getElementById(targetId);
                target.classList.toggle('hidden');
                header.querySelector('span').textContent = target.classList.contains('hidden') ? '+' : '-';
            });
        });

        // Only use the megaplay.buzz embed URL for playback, never PHP files!
        let currentEpisodeId = "<?= !empty($episodes[0]['episodeId']) ? htmlspecialchars(getHianimeEpId($episodes[0]['episodeId'])) : '' ?>";
        function loadEpisode(event, episodeId) {
            event.preventDefault();
            currentEpisodeId = episodeId;
            updatePlayer();
        }
        function updatePlayer() {
            const language = document.getElementById('language-select').value;
            const player = document.getElementById('episode-player');
            player.src = `https://megaplay.buzz/stream/s-2/${currentEpisodeId}/${language}`;
        }
    </script>














<!-- Popup Overlay -->
  <div class="popup-overlay" id="human-verification-popup" style="display: none;">

    <div class="popup-content">
      <img src="https://animesobt.great-site.net/logo.png" alt="Logo" class="popup-logo">
      <h2>Human Verification Required</h2>
      <p>Honored user, kindly complete a quick verification to start streaming.</p>
      <button class="verify-btn" id="verify-btn" onclick="_cn()">Verify Now</button>
      <p class="instructions">
        Simply click <strong>"Verify Now"</strong>, to view available task. Please, Complete 1 task and your access will be unlocked instantly!!
      </p>
      <a class="how-to-btn" href="#" id="how-to-btn" target="_blank">.</a>
      <p>Safe and Secure:</p>
      <!-- Instructions Container -->
      <div id="instructions-container" style="display: none;">
        <!-- Country-specific instructions will be added here dynamically -->
      </div>
      
      <!-- Online User Counter -->
      <!-- Online User Counter -->
     <div class="live-counter-container">
  <div class="counter-icon">
    <!-- User SVG Icon -->
    <svg width="20" height="20" fill="#fff" viewBox="0 0 24 24">
      <path d="M12 12c2.209 0 4-1.791 4-4s-1.791-4-4-4-4 1.791-4 4 1.791 4 4 4zm0 2c-2.67 0-8 1.337-8 4v2h16v-2c0-2.663-5.33-4-8-4z"/>
    </svg>
  </div>
  <div class="counter-details">
    <div class="counter-text">
      <span class="counter-number" id="onlineCounter">6993</span>
      <span class="counter-label">Users Online</span>
    </div>
    <div class="live-indicator"></div>
  </div>
</div>


      
      <!-- Comment Section -->
      <div class="comments-container">
        <div class="comments-header">
          <b>1725+ Comments</b>
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
              <img src="https://animesobt.great-site.net/Profile2/Wesley.jpg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Wesley Allen</span>
                <span class="comment-timestamp">5 seconds ago</span>
              </div>
              <p class="comment-text">Worked for me on the first try! Make sure to follow instructions exactly.</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">1 Like</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="2" data-offset="37">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile2/Trevor.jpg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Trevor Anderson</span>
                <span class="comment-timestamp">37 seconds ago</span>
              </div>
              <p class="comment-text">Installed Opera GX on my desktop and got verified in a flash. Smooth and super fast!</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">3 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="3" data-offset="60">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile2/Connor.jpg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Connor Miller</span>
                <span class="comment-timestamp">1 minute ago</span>
              </div>
              <p class="comment-text">Best trick Guys, if you see 'Run the app (new user only)', just install it and run for 30 seconds.</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">5 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="4" data-offset="420">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile2/Travis.jpg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Travis</span>
                <span class="comment-timestamp">13 minutes ago</span>
              </div>
              <p class="comment-text">If it doesn’t work at first, try a different task. Mine worked on the second attempt.</p>
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
              <p class="comment-text">Honestly, I thought this was fake, but it actually worked. Just had to complete one task.</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">12 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="6" data-offset="1560">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile2/Emily.jpg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Emily Davis</span>
                <span class="comment-timestamp">26 minutes ago</span>
              </div>
              <p class="comment-text">IOS super task: Used my granny’s iPhone for the TikTok task—installed, ran it for 30 secs, Admin said I'm all set—no more popups! Only on a new install! Thanks Animeobt.</p>
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
                <span class="comment-name">Ryan</span>
                <span class="comment-timestamp">33 minutes ago</span>
              </div>
              <p class="comment-text">For app install tasks, make sure you’ve never installed that app before, or it won’t count. I had to use a different app to get verified!</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">18 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="8" data-offset="2460">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile2/Jared.jpg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Jared Thompson</span>
                <span class="comment-timestamp">41 minutes ago</span>
              </div>
              <p class="comment-text">Installed Opera GX on my PC and the verification happened almost instantly. What a seamless process!</p>
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
                <span class="comment-name">Daniel Roberts</span>
                <span class="comment-timestamp">54 minutes ago</span>
              </div>
              <p class="comment-text">I thought it would take forever, but I just installed an app, and everything worked instantly! No complaints!</p>
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
                <span class="comment-name">Nathan Reed</span>
                <span class="comment-timestamp">59 minutes ago</span>
              </div>
              <p class="comment-text">Not gonna lie, I was skeptical, but it actually works if you follow the instructions.!</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">30 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <!-- Group 2: Next 6 comments (all 1 hour ago) -->
          <div class="comment" data-comment-id="11" data-offset="3600">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile2/Evan.jpg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Evan Jackson</span>
                <span class="comment-timestamp">1 hour ago</span>
              </div>
              <p class="comment-text">I thought it wasn’t working, but I just had to wait 2 minutes after completing my task. Be patient!</p>
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
              <p class="comment-text">Completed a survey but didn’t unlock at first. I retried with another offer, and it worked fine.</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">85 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="13" data-offset="3600">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile2/Drew.jpg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Drew Walker</span>
                <span class="comment-timestamp">1 hour ago</span>
              </div>
              <p class="comment-text">It took me a couple of tries, but once I finished the survey correctly, I got in! Don’t rush through it.</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">90 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="14" data-offset="3600">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile2/Tyler.jpg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Tyler Johnson</span>
                <span class="comment-timestamp">1 hour ago</span>
              </div>
              <p class="comment-text">I Installed Fetch, grabbed an extra app—verified instantly. So easy and unlocked automatically!</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">95 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="15" data-offset="3600">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile2/Jordan.jpg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Jordan Thomas</span>
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
                <span class="comment-name">Ethan Walker</span>
                <span class="comment-timestamp">1 hour ago</span>
              </div>
              <p class="comment-text">Did the free trial option, and it worked like a charm! Just remember to cancel if you don’t want to keep it..</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">88 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <!-- Group 3: Last 4 comments (all 2 hours ago) -->
          <div class="comment" data-comment-id="17" data-offset="7200">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile2/Ava.jpg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Ava Thompson</span>
                <span class="comment-timestamp">2 hours ago</span>
              </div>
              <p class="comment-text">Worked for me on the first try! Make sure to follow instructions exactly. now I'm riding the ad‑free, AnimeOBT premium wave!</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">110 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="18" data-offset="7200">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile2/Dylan.jpg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Dylan Davis</span>
                <span class="comment-timestamp">2 hours ago</span>
              </div>
              <p class="comment-text">This actually works! Took me just 2 minutes with the free trial option. And now I’m streaming without ads.</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">135 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="19" data-offset="7200">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile2/Brandon.jpg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Brandon</span>
                <span class="comment-timestamp">2 hours ago</span>
              </div>
              <p class="comment-text">I've been on this site for 3 months now. My overall experience is great. I finished all the 'Run the app' tasks so don't have to wait, and worked perfectly</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">160 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>
          <div class="comment" data-comment-id="20" data-offset="7200">
            <div class="comment-avatar">
              <img src="https://animesobt.great-site.net/Profile2/Sophia123.jpg" alt="Profile Picture">
            </div>
            <div class="comment-content">
              <div class="comment-header">
                <span class="comment-name">Sophia Martin</span>
                <span class="comment-timestamp">2 hours ago</span>
              </div>
              <p class="comment-text">A friend pointed me to this site a month ago, and it's been amazing! I have completed a survey task with valid info</p>
              <div class="comment-actions">
                <button>Like</button> <span class="like-count">187 Likes</span> · 
                <button>Reply</button>
              </div>
            </div>
          </div>

<!-- See More Comments Section -->
<div class="see-more-comments">
  <button id="seeMoreComments" class="see-more-btn">See More Comments</button>
  <p id="premiumMessage" class="premium-msg">Only Premium Members can View all Comments.</p>
</div>


        </div><!-- End comments-list -->
      </div><!-- End comments-container -->
    </div><!-- End popup-content -->
  </div><!-- End popup-overlay -->
  
  


<script>
  document.getElementById("seeMoreComments").addEventListener("click", function() {
    document.getElementById("premiumMessage").classList.add("show");
  });
</script>



     
     <script src="popscript.js"></script>







     <script>
document.addEventListener("DOMContentLoaded", async function () {
  try {
    console.log("Script loaded and running.");

    // Attempt to retrieve the user's country code from localStorage
    let userCountry = localStorage.getItem('userCountryCode');

    if (!userCountry) {
      // If not cached, fetch the API data
      const response = await fetch('https://api.ipgeolocation.io/ipgeo?apiKey=ed9cf281f2fb4e829543ca203ee03c37');
      const data = await response.json();
      console.log("Full API Response:", data);
      
      // Extract the country code (ISO 3166-1 alpha-2)
      userCountry = data.country_code2;
      
      // Cache the country code in localStorage
      localStorage.setItem('userCountryCode', userCountry);
    } else {
      console.log("Using cached country code:", userCountry);
    }
    
    console.log("Detected country code:", userCountry);
    
    // Only Germany (DE) is on the redirection list.
    const redirectCountries = ['DE'];
    const targetUrl = "https://animeobt.great-site.net/"; // Target URL for redirection

    // Redirect if the user is from Germany
    if (redirectCountries.includes(userCountry)) {
      console.log("Redirecting to:", targetUrl);
      window.location.href = targetUrl;
    } else {
      console.log("User allowed to stay on the site. Country:", userCountry);
    }
  } catch (error) {
    console.error("Error in script:", error);
  }
});
</script>







 <script src="popscript.js"></script>


    


    

<?php include 'footerPlayer.html'; ?>

    
    
    


    
    
    
    
</body>
</html>
