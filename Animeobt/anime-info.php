<?php
require 'api-client.php';

// Get animeId from the URL
if (!isset($_GET['animeId']) || empty($_GET['animeId'])) {
    die("Anime ID is required.");
}
include 'header.html'; 

$animeId = htmlspecialchars($_GET['animeId']);

$detailResult = api_request('/api/v2/anime/' . urlencode($animeId));
if (!$detailResult['ok']) {
    die("Failed to fetch anime information.");
}

$normalized = normalize_detail_payload($detailResult['data'] ?? []);
$anime = $normalized['info'];
$moreInfo = [];
$recommendedAnimes = $normalized['recommendedAnimes'];
$relatedAnimes = $normalized['relatedAnimes'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($anime['name']) ?> - Anime Info</title>
    <link rel="stylesheet" href="infostyle.css">  <!-- Include your CSS file -->
    <style>
        
    </style>
    
    
    
</head>
<body>

<main class="container">
    <!-- Anime Details -->
    <section class="section anime-details">
        <h2 class="anime-title"><?= htmlspecialchars($anime['name']) ?></h2>
        <div class="anime-info">
            <div class="anime-poster">
                <img src="<?= $anime['poster'] ?>" alt="<?= htmlspecialchars($anime['name']) ?>">
            </div>
            <div class="anime-meta">
                <p><strong>Type:</strong> <?= $anime['stats']['type'] ?></p>
                <p><strong>Rating:</strong> <?= $anime['stats']['rating'] ?></p>
                <p><strong>Quality:</strong> <?= $anime['stats']['quality'] ?></p>
                <p><strong>Duration:</strong> <?= $anime['stats']['duration'] ?></p>
                <p><strong>Episodes:</strong> Sub: <?= $anime['stats']['episodes']['sub'] ?> | Dub: <?= $anime['stats']['episodes']['dub'] ?></p>
                <p><strong>Description:</strong> <?= htmlspecialchars($anime['description']) ?></p> <br> <br>
                <a href="streaming.php?animeId=<?= $animeId ?>" class="watch-btn">Watch Now</a>
            </div>
        </div>
    </section>
    

    
   

    
    
    <!-- Recommended Animes -->
    <section class="section recommended-animes">
        <h2>Recommended Animes</h2>
        <div class="anime-grid">
            <?php foreach ($recommendedAnimes as $anime): ?>
                <a href="anime-info.php?animeId=<?= $anime['id'] ?>" class="anime-card">
                    <img src="<?= $anime['poster'] ?>" alt="<?= htmlspecialchars($anime['name']) ?>">
                    <div class="card-info">
                        <h3><?= htmlspecialchars($anime['name']) ?></h3>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Related Animes -->
    <section class="section related-animes">
        <h2>Related Animes</h2>
        <div class="anime-grid">
            <?php foreach ($relatedAnimes as $anime): ?>
                <a href="anime-info.php?animeId=<?= $anime['id'] ?>" class="anime-card">
                    <img src="<?= $anime['poster'] ?>" alt="<?= htmlspecialchars($anime['name']) ?>">
                    <div class="card-info">
                        <h3><?= htmlspecialchars($anime['name']) ?></h3>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</main>

    
    
    
    
    
<?php include 'footer.html'; ?>
    
    
    



</body>
</html>
