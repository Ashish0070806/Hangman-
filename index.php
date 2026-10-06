<?php
session_start();
$page_title = 'Word Guess Game';
include 'header.php';
?>
<h1>🎮 Word Guess</h1>
<p class="subtitle">A 2-Player Hangman Game</p>

<div class="btn-group" style="margin: 30px 0;">
    <a href="setup.php" class="btn btn-primary btn-block" style="max-width: 300px; margin: 0 auto;">
        ▶ Start Game
    </a>
</div>

<div class="rules">
    <h3>📜 How to Play</h3>
    <ul>
        <li><strong>Player 1</strong> picks a category and enters a secret word.</li>
        <li><strong>Player 2</strong> guesses the word, one letter at a time.</li>
        <li>Each wrong guess costs a life — run out and you lose!</li>
        <li>Beat the <strong>countdown timer</strong> or the round is lost.</li>
        <li>Use <strong>hints</strong> to reveal a letter (costs 1 life).</li>
        <li>After each round, players <strong>swap roles</strong>.</li>
        <li>Choose your <strong>difficulty</strong> — it affects lives, time, and hints.</li>
    </ul>
</div>

<div class="rules" style="margin-top: 15px;">
    <h3>🎯 Difficulty Levels</h3>
    <ul>
        <li><strong>Easy:</strong> 8 lives · 90 seconds · 2 hints</li>
        <li><strong>Medium:</strong> 6 lives · 60 seconds · 1 hint</li>
        <li><strong>Hard:</strong> 4 lives · 30 seconds · No hints</li>
    </ul>
</div>
<?php include 'footer.php'; ?>
