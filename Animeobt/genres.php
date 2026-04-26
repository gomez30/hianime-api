<?php
require 'tohost.php';

// Fetch genres
$response = file_get_contents(BASE_API_URL . 'api/v2/hianime/home');
    $data = json_decode($response, true);

    if (!$data || !isset($data['status']) || $data['status'] !== 200) {
        die("Failed to fetch genres.");
    }

    $genres = $data['data']['genres'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genres - Anime World</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php include 'header.html'; ?>

<main class="container">
    <section>
        <h2>Anime Genres</h2>
        <div class="genres-list">
            <?php foreach ($genres as $genre): ?>
                <a href="search.php?q=&genres=<?= urlencode($genre) ?>"><?= htmlspecialchars($genre) ?></a>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php include 'footer.html'; ?>

</body>
</html>
