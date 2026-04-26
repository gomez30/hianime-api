<?php
// genre.php
include('tohost.php');  // Including the API URL configuration

// Get the genre from the query parameter
$genreName = isset($_GET['name']) ? $_GET['name'] : 'action';  // Default to 'action' if no genre is specified
$page = isset($_GET['page']) ? $_GET['page'] : 1;  // Default to page 1 if no page is specified

// Construct the API URL for fetching anime based on genre
$apiUrl = "https://aniwatch-api-i02m.onrender.com/api/v2/hianime/genre/{$genreName}?page={$page}";
$response = file_get_contents($apiUrl);
$data = json_decode($response, true);
if (!$data || !isset($data['status']) || $data['status'] !== 200) {
    die("Failed to fetch genre data.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genre: <?php echo ucfirst($genreName); ?> Anime</title>
    <link rel="stylesheet" href="genre-page.css">
</head>
<body>
    <?php include('header.html'); // Include header ?>

    <div class="genre-page">
        <h1>Genre: <?php echo ucfirst($data['data']['genreName']); ?></h1>

        <!-- Anime Grid Section -->
        <div class="animes-grid">
            <?php foreach ($data['data']['animes'] as $anime): ?>
                <div class="anime-card">
                    <a href="anime-info.php?animeId=<?php echo urlencode($anime['id']); ?>">
                        <img src="<?php echo $anime['poster']; ?>" alt="<?php echo htmlspecialchars($anime['name']); ?>">
                        <h3><?php echo htmlspecialchars($anime['name']); ?></h3>
                    </a>
                    <p>Type: <?php echo htmlspecialchars($anime['type']); ?></p>
                    <p>Rating: <?php echo htmlspecialchars($anime['rating']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($data['data']['currentPage'] > 1): ?>
                <a href="genre.php?name=<?php echo urlencode($genreName); ?>&page=<?php echo $page - 1; ?>" class="prev">Previous</a>
            <?php endif; ?>

            <?php if ($data['data']['hasNextPage']): ?>
                <a href="genre.php?name=<?php echo urlencode($genreName); ?>&page=<?php echo $page + 1; ?>" class="next">Next</a>
            <?php endif; ?>
        </div>
    </div>

    <?php include('footer.html'); // Include footer ?>
</body>
</html>
