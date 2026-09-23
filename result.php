<?php
/**
 * result.php — Result Screen
 * 
 * Shows win/lose/timeout message, reveals the word,
 * displays scores, round history, and play-again options.
 */
session_start();

// Redirect if no result available
if (!isset($_SESSION['game_result']) || !isset($_SESSION['word'])) {
    header('Location: index.php');
    exit;
}

$result = $_SESSION['game_result']; // 'win', 'lose', 'timeout'
$word = strtoupper($_SESSION['word']);
$time_taken = $_SESSION['time_taken'] ?? 0;
$setter = $_SESSION['current_setter'];
$setter_name = ($setter === 1) ? $_SESSION['player1'] : $_SESSION['player2'];
$guesser_name = ($setter === 1) ? $_SESSION['player2'] : $_SESSION['player1'];
$category = $_SESSION['category'] ?? 'Custom';
$difficulty = $_SESSION['difficulty'] ?? 'medium';
$score_p1 = $_SESSION['score_p1'] ?? 0;
$score_p2 = $_SESSION['score_p2'] ?? 0;
$round_history = $_SESSION['round_history'] ?? [];

// Determine winner message
if ($result === 'win') {
    $message_icon = '🎉';
    $message_text = $guesser_name . ' guessed the word!';
    $message_class = 'win';
    $time_info = 'Solved in ' . $time_taken . ' seconds';
} elseif ($result === 'timeout') {
    $message_icon = '⏱️';
    $message_text = 'Time\'s up! ' . $setter_name . ' wins!';
    $message_class = 'lose';
    $time_info = 'Ran out of time';
} else {
    $message_icon = '💀';
    $message_text = $guesser_name . ' ran out of lives! ' . $setter_name . ' wins!';
    $message_class = 'lose';
    $time_info = 'Failed after ' . $time_taken . ' seconds';
}

// Next round: who sets the word next
$next_setter = ($setter === 1) ? 2 : 1;
$next_setter_name = ($next_setter === 1) ? $_SESSION['player1'] : $_SESSION['player2'];

// Clear the game result so refreshing doesn't re-trigger
// (but keep it for this page load)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result — Word Guess</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <!-- Result Message -->
        <div class="result-message <?php echo $message_class; ?>">
            <?php echo $message_icon; ?> <?php echo $message_text; ?>
        </div>

        <!-- Revealed Word -->
        <p style="text-align: center; color: #888; margin-bottom: 5px;">The word was:</p>
        <div class="revealed-word"><?php echo $word; ?></div>

        <!-- Category & Time -->
        <div style="text-align: center; margin-bottom: 5px;">
            <span class="category-badge" style="display: inline-block;"><?php echo $category; ?></span>
            <span class="difficulty-badge <?php echo $difficulty; ?>" style="display: inline-block; margin-left: 8px;">
                <?php echo ucfirst($difficulty); ?>
            </span>
        </div>
        <div class="time-taken"><?php echo $time_info; ?></div>

        <!-- Scoreboard -->
        <h3 style="text-align: center;">📊 Scoreboard</h3>
        <div class="scoreboard">
            <div class="score-card">
                <div class="player-name-label">🔴 <?php echo $_SESSION['player1']; ?></div>
                <div class="score-value"><?php echo $score_p1; ?></div>
            </div>
            <div class="score-card">
                <div class="player-name-label">🔵 <?php echo $_SESSION['player2']; ?></div>
                <div class="score-value"><?php echo $score_p2; ?></div>
            </div>
        </div>

        <!-- Round History -->
        <?php if (!empty($round_history)): ?>
            <h3 style="text-align: center; margin-top: 25px;">📜 Round History</h3>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Winner</th>
                        <th>Word</th>
                        <th>Category</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($round_history as $i => $round): ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($round['winner']); ?></td>
                            <td style="font-family: monospace; letter-spacing: 2px;">
                                <?php echo strtoupper(htmlspecialchars($round['word'])); ?>
                            </td>
                            <td><?php echo htmlspecialchars($round['category']); ?></td>
                            <td><?php echo htmlspecialchars($round['time']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="btn-group" style="margin-top: 30px;">
            <a href="reset.php?type=round" class="btn btn-success">
                🔄 Play Again (<?php echo $next_setter_name; ?> sets word)
            </a>
            <a href="reset.php?type=full" class="btn btn-secondary">
                🏠 New Game
            </a>
        </div>
    </div>
</body>
</html>
