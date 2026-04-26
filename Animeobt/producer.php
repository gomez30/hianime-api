<?php
include 'tohost.php';

$producerName = isset($_GET['name']) ? $_GET['name'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if (empty($producerName)) {
    echo "<p class='error-message'>Producer name is required.</p>";
    exit;
}

// Fetch producer anime list
$apiUrl = TOHOST_API_URL . "/api/v2/hianime/producer/" . urlencode($producerName) . "?page=" . $page;
$response = file_get_contents($apiUrl);
$data = json_decode($response, true);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($producerName); ?> Animes - Anime World</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.html'; ?>

    <main class="producer-animes-page">
        <div class="container">
            <?php if ($data['success']): ?>
                <h1><?php echo htmlspecialchars($data['data']['producerName']); ?></h1>

                <?php if (!empty($data['data']['animes'])): ?>
                    <div class="animes-grid">
                        <?php foreach ($data['data']['animes'] as $anime): ?>
                            <div class="anime-card">
                                <a href="anime-info.php?animeId=<?php echo urlencode($anime['id']); ?>">
    <img src="<?php echo $anime['poster']; ?>" alt="<?php echo htmlspecialchars($anime['name']); ?>">
    <h3><?php echo htmlspecialchars($anime['name']); ?></h3>
</a>

                                <p><?php echo htmlspecialchars($anime['type']); ?></p>
                                <p>Rating: <?php echo htmlspecialchars($anime['rating']); ?></p>
                                <p>Episodes: Sub: <?php echo $anime['episodes']['sub']; ?>, Dub: <?php echo $anime['episodes']['dub']; ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination">
                        <?php if ($data['data']['currentPage'] > 1): ?>
                            <a href="producer.php?name=<?php echo urlencode($producerName); ?>&page=<?php echo $page - 1; ?>" class="prev">Previous</a>
                        <?php endif; ?>

                        <?php if ($data['data']['hasNextPage']): ?>
                            <a href="producer.php?name=<?php echo urlencode($producerName); ?>&page=<?php echo $page + 1; ?>" class="next">Next</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="no-results">No animes found for "<?php echo htmlspecialchars($producerName); ?>".</p>
                <?php endif; ?>
            <?php else: ?>
                <p class="error-message">Failed to fetch animes for "<?php echo htmlspecialchars($producerName); ?>". Please try again later.</p>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'footer.html'; ?>
</body>
</html>
