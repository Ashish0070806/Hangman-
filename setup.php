<?php
/**
 * setup.php — Player Setup Screen
 * 
 * Collects player names and difficulty level.
 * Initializes session variables for a new game.
 */
session_start();

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $player1 = trim($_POST['player1'] ?? '');
    $player2 = trim($_POST['player2'] ?? '');
    $difficulty = $_POST['difficulty'] ?? 'medium';

    // Validation
    if (empty($player1) || empty($player2)) {
        $error = 'Both player names are required!';
    } elseif (strtolower($player1) === strtolower($player2)) {
        $error = 'Player names must be different!';
    } elseif (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
        $error = 'Please select a valid difficulty!';
    } else {
        // Initialize session
        $_SESSION['player1'] = htmlspecialchars($player1);
        $_SESSION['player2'] = htmlspecialchars($player2);
        $_SESSION['difficulty'] = $difficulty;
        $_SESSION['score_p1'] = 0;
        $_SESSION['score_p2'] = 0;
        $_SESSION['current_setter'] = 1; // Player 1 sets the first word
        $_SESSION['round_history'] = [];

        header('Location: word_entry.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Setup — Word Guess</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>👤 Player Setup</h1>
        <p class="subtitle">Enter your names and choose difficulty</p>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="setup.php">
            <!-- Player 1 Name -->
            <div class="form-group">
                <label for="player1">🔴 Player 1 Name</label>
                <input type="text" id="player1" name="player1" placeholder="Enter Player 1 name"
                       value="<?php echo htmlspecialchars($_POST['player1'] ?? ''); ?>" 
                       maxlength="20" required>
            </div>

            <!-- Player 2 Name -->
            <div class="form-group">
                <label for="player2">🔵 Player 2 Name</label>
                <input type="text" id="player2" name="player2" placeholder="Enter Player 2 name"
                       value="<?php echo htmlspecialchars($_POST['player2'] ?? ''); ?>"
                       maxlength="20" required>
            </div>

            <!-- Difficulty -->
            <div class="form-group">
                <label>🎯 Difficulty</label>
                <div class="difficulty-options">
                    <div class="difficulty-option">
                        <input type="radio" id="easy" name="difficulty" value="easy"
                               <?php echo (($_POST['difficulty'] ?? 'medium') === 'easy') ? 'checked' : ''; ?>>
                        <label for="easy">
                            😊 Easy
                            <span class="diff-detail">8 lives · 90s · 2 hints</span>
                        </label>
                    </div>
                    <div class="difficulty-option">
                        <input type="radio" id="medium" name="difficulty" value="medium"
                               <?php echo (($_POST['difficulty'] ?? 'medium') === 'medium') ? 'checked' : ''; ?>>
                        <label for="medium">
                            😐 Medium
                            <span class="diff-detail">6 lives · 60s · 1 hint</span>
                        </label>
                    </div>
                    <div class="difficulty-option">
                        <input type="radio" id="hard" name="difficulty" value="hard"
                               <?php echo (($_POST['difficulty'] ?? 'medium') === 'hard') ? 'checked' : ''; ?>>
                        <label for="hard">
                            😈 Hard
                            <span class="diff-detail">4 lives · 30s · 0 hints</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn btn-primary btn-block">🎮 Start Game</button>
        </form>

        <div class="btn-group" style="margin-top: 15px;">
            <a href="index.php" class="btn btn-secondary">← Back</a>
        </div>
    </div>
</body>
</html>
