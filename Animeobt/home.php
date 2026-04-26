<?php

require 'api-client.php';

// Endpoint for the homepage data
$homeResult = api_request('/api/v2/home');
if (!$homeResult['ok']) {
    $safeError = htmlspecialchars((string)$homeResult['error']);
    die("Failed to fetch anime data now. {$safeError}");
}
$data = normalize_home_payload($homeResult['data'] ?? []);

// Set the default date to today
$date = date('Y-m-d'); // Format: yyyy-mm-dd

// Fetch schedules from the API
$scheduleResult = api_request("/api/v2/schedules?date=$date");
$scheduledAnimes = [];
if ($scheduleResult['ok']) {
    $scheduleMap = $scheduleResult['data']['data'] ?? $scheduleResult['data'] ?? [];
    $scheduledAnimes = $scheduleMap[$date] ?? [];
}

// Separate latest sub and dub episodes
$latestSubAnimes = [];
$latestDubAnimes = [];

foreach ($data['latestEpisodeAnimes'] as $anime) {
    if (!empty($anime['episodes']['sub'])) {
        $latestSubAnimes[] = $anime;
    }
    if (!empty($anime['episodes']['dub'])) {
        $latestDubAnimes[] = $anime;
    }
}

include 'header.html';
?>

<!DOCTYPE html>
<html lang="en">
<head>




<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-PFTJHZMK04"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-PFTJHZMK04');
</script>





    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animeobt - Watch Anime Online Without Ads for Free</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
       @charset "utf-8";
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');

/* Base Styles */
body {
    background: #202125;
    font-family: 'Montserrat', Arial, sans-serif;
    color: #fff;
    margin: 0;
    padding: 0;
    -webkit-text-size-adjust: none;
}

h2 {
    font-size: 1.8em;
    font-weight: 600;
    color: #7f00ff;
    margin-bottom: 20px;
    text-align: center;
}

h3 {
    font-size: 1.2em;
    font-weight: 600;
    color: #fff;
    margin-bottom: 10px;
}

/* Main Container */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.section {
    margin-bottom: 50px;
}

/* Anime Grid */
.anime-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    padding: 0;
    margin: 0;
    list-style: none;
}

/* Anime Card */
.anime-card {
    background: #333;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    text-decoration: none;
}

.anime-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
}

.anime-card img {
    width: 100%;
    height: auto;
    display: block;
}

.card-info {
    padding: 15px;
}

.card-info h3 {
    font-size: 1.1em;
    color: #fff;
    margin-bottom: 5px;
}

.card-info p {
    font-size: 0.9em;
    color: #aaa;
}

/* Spotlight Anime Section */
/* General Styles */
body {
    background: #202125;
    font-family: 'Montserrat', Arial, sans-serif;
    color: #fff;
    margin: 0;
    padding: 0;
    -webkit-text-size-adjust: none;
}

/* Spotlight Slideshow Section */
.spotlight-slideshow {
    position: relative;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    overflow: hidden;
}

.slideshow-container {
    display: flex;
    transition: transform 0.5s ease-in-out;
}

.slide {
    min-width: 100%;
    box-sizing: border-box;
    position: relative;
}

.slide-image {
    width: 100%;
    height: auto;
    display: block;
    object-fit: cover;
}

.slide-content {
    position: absolute;
    bottom: 30px;
    left: 20px;
    color: white;
    text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7);
}

.slide-content h3 {
    font-size: 1.5em;
    font-weight: 600;
    margin-bottom: 10px;
}

.watch-now-btn {
    background-color: #7f00ff;
    color: #fff;
    padding: 10px 20px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 600;
    transition: background-color 0.3s ease;
}

.watch-now-btn:hover {
    background-color: #5900b3;
}

/* Navigation Buttons - Box Style */
.navigation {
    position: absolute;
    top: 50%;
    width: 100%;
    display: flex;
    justify-content: space-between;
    transform: translateY(-50%);
}

.prev, .next {
    background-color: rgba(0, 0, 0, 0.3);
    color: #fff;
    padding: 5px 15px;  /* Smaller padding */
    border-radius: 5px;  /* Box-style with sharp corners */
    font-size: 10px;  /* Smaller font size */
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
    border: 2px solid #fff;  /* Optional: Adds a border to make it more boxy */
}

/* Hover effect for box buttons */
.prev:hover, .next:hover {
    background-color: rgba(0, 0, 0, 0.8);
    transform: scale(1.1);  /* Slightly enlarge the button on hover */
}

/* Focused Button Style */
.prev:focus, .next:focus {
    outline: none;
    box-shadow: 0 0 0 2px #7f00ff; /* Optional: add focus outline for accessibility */
}

/* Additional Styles for Content */
h2 {
    font-size: 1.8em;
    font-weight: 600;
    color: #7f00ff;
    margin-bottom: 20px;
    text-align: center;
}

.anime-grid, .schedule-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    padding: 0;
    margin: 0;
}

/* Responsive Styles */
@media (max-width: 767px) {
    .anime-grid, .schedule-grid {
        grid-template-columns: 1fr 1fr;
    }

    .spotlight-slideshow {
        max-width: 100%;
    }

    .slide-content h3 {
        font-size: 1.5em;
    }
}


/* Schedule Grid */
.schedule-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    padding: 0;
}

.schedule-card {
    background: #333;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-align: center;
}

.schedule-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
}

.schedule-card h3 {
    font-size: 1.2em;
    font-weight: 600;
    color: #fff;
    margin-bottom: 10px;
}

.schedule-card p {
    font-size: 0.9em;
    color: #aaa;
    margin-bottom: 10px;
}

.schedule-card .view-details {
    color: #7f00ff;
    font-weight: 600;
    text-decoration: none;
    padding: 5px 10px;
    border: 1px solid #7f00ff;
    border-radius: 20px;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.schedule-card .view-details:hover {
    background-color: #7f00ff;
    color: #fff;
}

.schedule-card .view-details:active {
    background-color: #5900b3;
}

/* Centered Text in Estimated Schedules Section */
.schedule-grid h2 {
    text-align: center;
    font-size: 1.8em;
    font-weight: 600;
    color: #7f00ff;
    margin-bottom: 30px;
}

/* Animation Keyframes */
@keyframes fadeIn {
    0% { opacity: 0; }
    100% { opacity: 1; }
}

@keyframes fadeInUp {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
}

@keyframes slideInLeft {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(0); }
}

@keyframes scaleUp {
    0% { transform: scale(0.8); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

@media (max-width: 767px) {
    .anime-grid, .schedule-grid {
        grid-template-columns: 1fr 1fr;
    }
}

    </style>
    
    
    

    
    
</head>
<body>

<main class="container">
    <!-- Spotlight Anime Slideshow -->
    <section class="spotlight-slideshow">
        <div class="slideshow-container">
            <?php foreach ($data['spotlightAnimes'] as $anime): ?>
                <div class="slide">
                    <img src="<?= $anime['poster'] ?>" alt="<?= htmlspecialchars($anime['name']) ?>" class="slide-image">
                    <div class="slide-content">
                        <h3><?= htmlspecialchars($anime['name']) ?></h3>
                        <a href="anime-info.php?animeId=<?= $anime['id'] ?>" class="watch-now-btn">Watch Now</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="navigation">
            <span class="prev">&#10094;</span>
            <span class="next">&#10095;</span>
        </div>
    </section>

    
    

   

    
    
    
    <!-- Trending Anime -->
<section class="section">
    <h2>Trending Anime</h2>
    <div class="anime-grid">
        <?php if (!empty($data['trendingAnimes'])): ?>
            <?php foreach ($data['trendingAnimes'] as $anime): ?>
                <a href="anime-info.php?animeId=<?= $anime['id'] ?>" class="anime-card">
                    <img src="<?= $anime['poster'] ?>" alt="<?= htmlspecialchars($anime['name']) ?>">
                    <div class="card-info">
                        <h3><?= htmlspecialchars($anime['name']) ?></h3>
                        <p>Trending Now!</p>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No trending anime available.</p>
        <?php endif; ?>
    </div>
</section>

    
    
    <!-- Latest Anime Sub Episodes -->
    <section class="section">
        <h2>Latest Anime Sub Episodes</h2>
        <div class="anime-grid">
            <?php if (!empty($latestSubAnimes)): ?>
                <?php foreach ($latestSubAnimes as $anime): ?>
                    <a href="anime-info.php?animeId=<?= $anime['id'] ?>" class="anime-card">
                        <img src="<?= $anime['poster'] ?>" alt="<?= htmlspecialchars($anime['name']) ?>">
                        <div class="card-info">
                            <h3><?= htmlspecialchars($anime['name']) ?></h3>
                            <p>Sub: <?= $anime['episodes']['sub'] ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No latest subbed anime available.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Latest Anime Dub Episodes -->
    <section class="section">
        <h2>Latest Anime Dub Episodes</h2>
        <div class="anime-grid">
            <?php if (!empty($latestDubAnimes)): ?>
                <?php foreach ($latestDubAnimes as $anime): ?>
                    <a href="anime-info.php?animeId=<?= $anime['id'] ?>" class="anime-card">
                        <img src="<?= $anime['poster'] ?>" alt="<?= htmlspecialchars($anime['name']) ?>">
                        <div class="card-info">
                            <h3><?= htmlspecialchars($anime['name']) ?></h3>
                            <p>Dub: <?= $anime['episodes']['dub'] ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No latest dubbed anime available.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Estimated Schedules Section -->
    <?php
    $today = date('Y-m-d');
    $nextDay = date('Y-m-d', strtotime('+1 day', strtotime($today)));
?>

    <section class="section">
    <center><h2>Estimated Schedules (<?= htmlspecialchars($date) ?>)</h2></center>
    <div class="schedule-grid">
        <?php if (!empty($scheduledAnimes)): ?>
            <?php foreach ($scheduledAnimes as $anime): ?>
                <div class="schedule-card">
                    <?php
                        $scheduleTitle = trim((string)($anime['title'] ?? $anime['name'] ?? 'Unknown title'));
                        $scheduleAltTitle = trim((string)($anime['alternativeTitle'] ?? $anime['jname'] ?? ''));
                        $airingTime = (string)($anime['time'] ?? '');
                        $secondsUntilAiring = (int)($anime['secondsUntilAiring'] ?? 0);
                    ?>
                    <h3><?= htmlspecialchars($scheduleTitle) ?></h3>
                    <?php if ($scheduleAltTitle !== ''): ?>
                        <p><strong>Japanese Name:</strong> <?= htmlspecialchars($scheduleAltTitle) ?></p>
                    <?php endif; ?>
                    
                    <!-- Convert airing time to 12-hour format -->
                    <?php if ($airingTime !== ''): ?>
                    <?php 
                        $airingTime12Hour = date('g:i A', strtotime($airingTime));
                    ?>
                    <p><strong>Airing Time:</strong> <?= $airingTime12Hour ?></p>
                    <?php endif; ?>
                    
                    <!-- Format the remaining time until airing -->
                    <?php if ($secondsUntilAiring > 0): ?>
                    <?php 
                        $hours = floor($secondsUntilAiring / 3600);
                        $minutes = floor(($secondsUntilAiring % 3600) / 60);
                        $formattedTime = sprintf("%02d:%02d", $hours, $minutes);
                    ?>
                    <p><strong>Time Until Airing:</strong> <?= $formattedTime ?> (HH:MM)</p>
                    <?php endif; ?>
                    
                    <a href="anime-info.php?animeId=<?= $anime['id'] ?>" class="view-details">View Details</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No schedules available for <?= htmlspecialchars($date) ?>.</p>
        <?php endif; ?>
    </div>

    <!-- Button to View Next Day's Schedule -->
    <!--<div class="next-day-button">-->
    <!--    <a href="schedule.php?date=<?= $nextDay ?>" class="button">View Next Day's Schedule</a>-->
    <!--</div>-->
</section>


</main>

<script>
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const totalSlides = slides.length;
const nextBtn = document.querySelector('.next');
const prevBtn = document.querySelector('.prev');
const slideshowContainer = document.querySelector('.slideshow-container');

// Function to move to the next slide
function nextSlide() {
    currentSlide = (currentSlide + 1) % totalSlides;
    updateSlidePosition();
}

// Function to move to the previous slide
function prevSlide() {
    currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
    updateSlidePosition();
}

// Function to update slide position
function updateSlidePosition() {
    const offset = -currentSlide * 100; // Each slide takes up 100% width
    slideshowContainer.style.transform = `translateX(${offset}%)`;
}

// Auto slide every 5 seconds
setInterval(nextSlide, 5000);

// Event listeners for next and previous buttons
nextBtn.addEventListener('click', nextSlide);
prevBtn.addEventListener('click', prevSlide);

// Initialize the slideshow
updateSlidePosition();

</script>












<?php include 'footer.html'; ?>














</body>
</html>
