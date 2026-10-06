<?php
session_start();
if (!isset($_SESSION['game_result'], $_SESSION['word'])) {
    header('Location: index.php');
    exit;
}

$result = $_SESSION['game_result'];
$word = strtoupper($_SESSION['word']);
$time_taken = $_SESSION['time_taken'] ?? 0;
$setter = $_SESSION['current_setter'];
$setter_name = ($setter === 1) ? $_SESSION['player1'] : $_SESSION['player2'];
$guesser_name = ($setter === 1) ? $_SESSION['player2'] : $_SESSION['player1'];
$category = $_SESSION['category'] ?? 'Custom';
$difficulty = $_SESSION['difficulty'] ?? 'medium';
$round_history = $_SESSION['round_history'] ?? [];

$messages = [
    'win'     => ['🎉', "$guesser_name guessed the word!", 'win', "Solved in {$time_taken} seconds"],
    'timeout' => ['⏱️', "Time's up! $setter_name wins!", 'lose', "Ran out of time"],
    'lose'    => ['💀', "$guesser_name ran out of lives! $setter_name wins!", 'lose', "Failed after {$time_taken} seconds"]
];
[$message_icon, $message_text, $message_class, $time_info] = $messages[$result] ?? $messages['lose'];

$next_setter = ($setter === 1) ? 2 : 1;
$next_setter_name = ($next_setter === 1) ? $_SESSION['player1'] : $_SESSION['player2'];

$page_title = 'Result — Word Guess';
include 'header.php';
?>
<div class="result-message <?= $message_class ?>">
    <?= $message_icon ?> <?= e($message_text) ?>
</div>

<p style="text-align: center; color: #888; margin-bottom: 5px;">The word was:</p>
<div class="revealed-word"><?= e($word) ?></div>

<div style="text-align: center; margin-bottom: 5px;">
    <span class="category-badge" style="display: inline-block;"><?= e($category) ?></span>
    <span class="difficulty-badge <?= e($difficulty) ?>" style="display: inline-block; margin-left: 8px;">
        <?= ucfirst($difficulty) ?>
    </span>
</div>
<div class="time-taken"><?= e($time_info) ?></div>

<h3 style="text-align: center;">📊 Scoreboard</h3>
<div class="scoreboard">
    <div class="score-card">
        <div class="player-name-label">🔴 <?= e($_SESSION['player1']) ?></div>
        <div class="score-value"><?= $_SESSION['score_p1'] ?? 0 ?></div>
    </div>
    <div class="score-card">
        <div class="player-name-label">🔵 <?= e($_SESSION['player2']) ?></div>
        <div class="score-value"><?= $_SESSION['score_p2'] ?? 0 ?></div>
    </div>
</div>

<?php if (!empty($round_history)): ?>
    <h3 style="text-align: center; margin-top: 25px;">📜 Round History</h3>
    <table class="history-table">
        <thead>
            <tr><th>#</th><th>Winner</th><th>Word</th><th>Category</th><th>Time</th></tr>
        </thead>
        <tbody>
            <?php foreach ($round_history as $i => $round): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($round['winner']) ?></td>
                    <td style="font-family: monospace; letter-spacing: 2px;"><?= strtoupper(e($round['word'])) ?></td>
                    <td><?= e($round['category']) ?></td>
                    <td><?= e($round['time']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<div class="btn-group" style="margin-top: 30px;">
    <a href="reset.php?type=round" class="btn btn-success">
        🔄 Play Again (<?= e($next_setter_name) ?> sets word)
    </a>
    <a href="reset.php?type=full" class="btn btn-secondary">🏠 New Game</a>
</div>
<?php include 'footer.php'; ?>
