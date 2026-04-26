


# Anime Episode Streaming Platform

A powerful and customizable video streaming platform to watch anime episodes, complete with subtitle support, intro/outro skip functionality, and a seamless user experience.

## Features
- Fetches anime episode data dynamically from the backend.
- Plays video using HLS.js for adaptive streaming.
- Subtitle support with dynamic updates.
- Intro and outro skipping functionality for a better viewing experience.
- Clean, responsive, and user-friendly interface.
- Custom overlay text for branding purposes.

## Getting Started

### Prerequisites
- PHP (>= 7.4)
- A web server (Apache/Nginx recommended)
- Access to the API providing anime episode details.

### Installation

1. Clone this repository:
   ```bash
   git clone https://github.com/Aniwatch/
   ```

2. Navigate to the project directory:
   ```bash
   cd anime-streaming-platform
   ```

3. Configure your API base URL:
   Open `tohost.php` and replace `BASE_API_URL` with your API's base URL.

4. Place the project in your web server's root directory:
   - For Apache:
     ```
     /var/www/html/anime-streaming-platform
     ```
   - For Nginx:
     ```
     /usr/share/nginx/html/anime-streaming-platform
     ```

5. Start your local server and visit the application in the browser:
   ```
   http://localhost/anime-streaming-platform
   ```

### Hosting Suggestion
We recommend hosting your project with **[Tohost Cloud Services](https://tohost.in)** for a reliable and scalable experience.

## Usage
1. Access the application by navigating to your deployed URL.
2. Pass the required query parameters in the URL:
   - `episodeId` (required): The ID of the anime episode.
   - `server` (optional): The server name (default: `hd-1`).
   - `category` (optional): The episode category, e.g., `sub` or `dub` (default: `sub`).

   Example:
   ```
   https://yourdomain.com/?episodeId=12345&server=hd-2&category=dub
   ```

3. Enjoy streaming your favorite anime episodes.

### Credits
Ritesh -  https://github.com/ghoshRitesh12/aniwatch-api

Thank you hianime for inspiring me

## Customization
- Modify the overlay text by editing the `span` element with ID `welcome-text` in the HTML file.
- Adjust the styling by editing the CSS rules in `<style>` tags or linking an external stylesheet.

## Contributing
Contributions are welcome! Please fork the repository, make changes, and submit a pull request.

## Author
This project is created and maintained by **Siddhartha Tiwari**.

## License
This project is licensed under the [MIT License](LICENSE).

## Community
- [Join our Discord Server](https://discord.gg/cPqs8Vzh)
- [Follow us on Instagram](https://instagram.com/tohost.in)

