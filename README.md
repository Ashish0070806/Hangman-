# 🎮 2-Player Word Guess Game

A responsive, dark-themed **2-Player Hangman Game** built with **PHP** and clean **CSS**. The application uses native PHP sessions to securely handle game state, scores, round history, and role rotations without needing a database.

## ✨ Features
* **Dynamic Role Swapping:** Player 1 sets a word, Player 2 guesses it, and roles reverse automatically each round.
* **3 Difficulty Levels:** Affects starting lives, countdown timer length, and hint availability.
* **Interactive UI:** Built-in ASCII Hangman art updates in real-time with wrong answers.
* **No Database Needed:** Uses PHP `$_SESSION` to track active match history and scoreboards.
* **Safe Form Redirection:** Implements Post/Redirect/Get patterns to avoid annoying page resubmission popups on browser refreshes.

## 🛠️ File Structure
* `index.php` — Welcome landing screen with instructions.
* `setup.php` — Player name creation and difficulty selector.
* `word_entry.php` — Password-hidden form for entering the secret word and category.
* `play.php` — Core interactive gameplay engine and keyboard handlers.
* `result.php` — Final match scorecard and historical summary.
* `reset.php` — State cleanup mechanics for round intervals.
* `styles.css` — Responsive grid styling optimized for modern desktop and mobile browsers.

## 🚀 How to Run Locally
1. Clone this repository into your local server environment (e.g., **XAMPP**, **MAMP**, or **Laragon**):
   ```bash
   git clone https://github.com
   ```
2. Move the project folder into your root directory (like `/htdocs` or `/www`).
3. Boot up your Apache server.
4. Launch your browser and navigate to `http://localhost/YOUR-PROJECT-FOLDER/index.php`.
