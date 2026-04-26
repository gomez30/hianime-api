<?php
require 'api-client.php';

// Get the query and optional filters
$query = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$normalized = animeobt_search($query, $page);
$searchResults = $normalized['animes'];
$currentPage = $normalized['currentPage'];
$totalPages = $normalized['totalPages'];
?>

<!DOCTYPE html>
<html lang="en">
<head>




    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Anime World</title>
    <!--<link rel="stylesheet" href="styles.css">-->
    <style>
        /* General Page Styles */
body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
    color: #333;
}

.container {
    width: 90%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Header Styles */
header {
    background-color: #000105;
    padding: 15px 0;
    text-align: center;
    color: #fff;
}

header .logo {
    font-size: 2rem;
    font-weight: bold;
}

nav {
    margin-top: 10px;
}

/* Search Result Section */
.section {
    margin-top: 30px;
}

h2 {
    font-size: 2rem;
    color: #222;
    text-align: center;
    margin-bottom: 20px;
}

/* Anime Grid */
.anime-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    justify-items: center;
}

.anime-card {
    background-color: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 100%;
    transition: transform 0.3s ease;
}

.anime-card:hover {
    transform: translateY(-5px);
}

.anime-card img {
    width: 100%;
    height: 350px;
    object-fit: cover;
}

.card-info {
    padding: 15px;
    text-align: center;
}

.card-info h3 {
    font-size: 1.4rem;
    color: #222;
    margin-bottom: 10px;
}

.card-info p {
    font-size: 1rem;
    color: #666;
    margin: 5px 0;
}

/* Pagination Styles */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 20px;
}

.pagination a {
    color: #45a049;
    text-decoration: none;
    font-weight: bold;
    padding: 10px 15px;
    border-radius: 5px;
    background-color: rgba(0, 0, 0, 0.1);
    margin: 0 10px;
    transition: background-color 0.3s ease;
}

.pagination a:hover {
    background-color: #45a049;
    color: #fff;
}

.pagination span {
    font-size: 1rem;
    color: #333;
}

/* Footer Styles */
footer {
    background-color: #121212;
    padding: 20px 0;
    text-align: center;
    color: #fff;
}

footer p {
    font-size: 1rem;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .anime-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }

    .card-info h3 {
        font-size: 1.2rem;
    }

    .card-info p {
        font-size: 0.9rem;
    }

    .pagination a {
        padding: 8px 12px;
    }
}

    </style>
</head>
<body>

<?php include 'header.html'; ?>

<main class="container">
    <section class="section">
        <h2>Search Results for "<?= htmlspecialchars($query) ?>"</h2>

        <?php if (empty($searchResults)): ?>
            <p>No results found. Try another search query.</p>
        <?php else: ?>
            <div class="anime-grid">
                <?php foreach ($searchResults as $anime): ?>
                    <a href="anime-info.php?animeId=<?= $anime['id'] ?>" class="anime-card">
                        <img src="<?= $anime['poster'] ?>" alt="<?= htmlspecialchars($anime['name']) ?>">
                        <div class="card-info">
                            <h3><?= htmlspecialchars($anime['name']) ?></h3>
                            <p>Type: <?= $anime['type'] ?></p>
                            <p>Rating: <?= $anime['rating'] ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="search.php?q=<?= urlencode($query) ?>&page=<?= $currentPage - 1 ?>">Previous</a>
                <?php endif; ?>
                <span>Page <?= $currentPage ?> of <?= $totalPages ?></span>
                <?php if ($currentPage < $totalPages): ?>
                    <a href="search.php?q=<?= urlencode($query) ?>&page=<?= $currentPage + 1 ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<footer>
    <p>&copy; 2024 BingFlix. Powered by AnimeObt.</p>
</footer>

</body>
</html>
