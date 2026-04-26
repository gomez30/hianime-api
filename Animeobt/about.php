<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Animeobt</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        header {
            background: #333;
            color: #fff;
            padding: 1rem 0;
            text-align: center;
        }
        .container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 1rem;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1, h2 {
            color: #444;
        }
        a {
            color: #0066cc;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        footer {
            text-align: center;
            padding: 1rem 0;
            margin-top: 2rem;
            background: #333;
            color: #fff;
        }
    </style>
</head>
<body>

<?php include 'header.html'; ?>

<main class="container">
    <h2>Website Details</h2>
    <p>Welcome to <strong>Animeobt</strong>, a platform built for anime lovers! This website is designed to let users watch and explore anime series with ease. We fetch content dynamically using APIs and provide features like most popular anime, scheduled releases, and related recommendations.</p>
    <p><strong>Disclaimer:</strong> This website is created for fun and educational purposes. It is not intended for professional or commercial use. The author and developers are not responsible for any misuse of this website or its content. Use it at your own discretion.</p>

    <h2>Author</h2>
    <p><strong>Name:</strong> AnimeObt</p>
    <p><strong>GitHub:</strong> <a href="" target="_blank"></a></p>
    <p>The project was developed as a fun way to explore web development, learn API integration, and create a platform for anime enthusiasts. It's not associated with any professional anime streaming services.</p>

    <h2>About the Website</h2>
    <ul>
        <li><strong>Purpose:</strong> Explore anime details, find schedules, and enjoy seamless browsing of anime series.</li>
        <li><strong>Technology Used:</strong> PHP, HTML, CSS, JavaScript, and dynamic APIs for fetching anime data.</li>
        <li><strong>Features:</strong>
            <ul>
                <li>Explore most popular anime</li>
                <li>View schedules and episodes</li>
                <li>Dynamic anime recommendations</li>
                <li>Promotional videos and detailed info</li>
                <li>Favorites and sharing options</li>
            </ul>
        </li>
    </ul>

    <h2>Note</h2>
    <p>Animebot does not store any files on our server, we only linked to the media which is hosted on 3rd party services.</p>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> Anito | Developed by <a href="https://animesobt.great-site.net/home.php" target="_blank">Animeobt</a></p>
</footer>

</body>
</html>
