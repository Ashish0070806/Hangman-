<?php
session_start();
if (!isset($_SESSION['word'], $_SESSION['player1'])) {
    header('Location: index.php');
    exit;
}

function finishRound($winner, $result, $time) {
    $_SESSION['game_result'] = $result;
    $_SESSION['time_taken'] = $time;
    $entry = [
        'winner' => $winner, 'word' => $_SESSION['word'],
        'category' => $_SESSION['category'] ?? 'Custom',
        'time' => is_numeric($time) ? "{$time}s" : $time, 'result' => $result
    ];
    $_SESSION['round_history'] = array_slice(array_merge([$entry], $_SESSION['round_history'] ?? []), 0, 5);
    header('Location: result.php');
    exit;
}

$word = $_SESSION['word'];
$guessed = $_SESSION['guessed'];
$lives = $_SESSION['lives'];
$max_lives = $_SESSION['max_lives'];
$start_time = $_SESSION['start_time'];
$time_limit = $_SESSION['time_limit'];
$setter = $_SESSION['current_setter'];
$setter_name = ($setter === 1) ? $_SESSION['player1'] : $_SESSION['player2'];
$guesser_name = ($setter === 1) ? $_SESSION['player2'] : $_SESSION['player1'];
$setter_key = ($setter === 1) ? 'score_p1' : 'score_p2';
$guesser_key = ($setter === 1) ? 'score_p2' : 'score_p1';
$category = $_SESSION['category'] ?? 'Custom';
$difficulty = $_SESSION['difficulty'] ?? 'medium';
$hints_remaining = $_SESSION['hints_remaining'] ?? 0;

$elapsed = time() - $start_time;
$remaining = max(0, $time_limit - $elapsed);

if ($remaining <= 0 && !isset($_GET['letter'], $_GET['hint'])) {
    $_SESSION[$setter_key] = ($_SESSION[$setter_key] ?? 0) + 1;
    finishRound($setter_name, 'timeout', $time_limit);
}

if (isset($_GET['hint']) && $hints_remaining > 0) {
    $unrevealed = array_values(array_diff(array_unique(str_split($word)), $guessed));
    if (!empty($unrevealed)) {
        $_SESSION['guessed'][] = $unrevealed[array_rand($unrevealed)];
        $_SESSION['hints_remaining']--;
        $_SESSION['hints_used'] = ($_SESSION['hints_used'] ?? 0) + 1;
        $_SESSION['lives']--;
        $_SESSION['hint_revealed'] = true;
    }
    header('Location: play.php');
    exit;
}

if (isset($_GET['letter'])) {
    $letter = strtolower($_GET['letter']);
    if (preg_match('/^[a-z]$/', $letter) && !in_array($letter, $guessed)) {
        $_SESSION['guessed'][] = $letter;
        if (strpos($word, $letter) === false) $_SESSION['lives']--;
    }

    $elapsed = time() - $start_time;
    $remaining = max(0, $time_limit - $elapsed);
    $guessed = $_SESSION['guessed'];
    $lives = $_SESSION['lives'];
    $word_letters = array_unique(str_split($word));

    if (empty(array_diff($word_letters, $guessed))) {
        $_SESSION[$guesser_key] = ($_SESSION[$guesser_key] ?? 0) + 1;
        finishRound($guesser_name, 'win', $elapsed);
    }
    if ($lives <= 0) {
        $_SESSION[$setter_key] = ($_SESSION[$setter_key] ?? 0) + 1;
        finishRound($setter_name, 'lose', $elapsed);
    }
    if ($remaining <= 0) {
        $_SESSION[$setter_key] = ($_SESSION[$setter_key] ?? 0) + 1;
        finishRound($setter_name, 'timeout', $time_limit);
    }

    header('Location: play.php');
    exit;
}

$guessed = $_SESSION['guessed'];
$lives = $_SESSION['lives'];
$hints_remaining = $_SESSION['hints_remaining'] ?? 0;
$elapsed = time() - $start_time;
$remaining = max(0, $time_limit - $elapsed);
$word_letters = str_split($word);
$wrong_guesses = array_filter($guessed, fn($l) => strpos($word, $l) === false);

$stages = [
    "  ┌───┐\n  │   │\n  │\n  │\n  │\n  │\n══╧══",
    "  ┌───┐\n  │   │\n  │   O\n  │\n  │\n  │\n══╧══",
    "  ┌───┐\n  │   │\n  │   O\n  │   │\n  │\n  │\n══╧══",
    "  ┌───┐\n  │   │\n  │   O\n  │  /│\n  │\n  │\n══╧══",
    "  ┌───┐\n  │   │\n  │   O\n  │  /│\\\n  │\n  │\n══╧══",
    "  ┌───┐\n  │   │\n  │   O\n  │  /│\\\n  │  /\n  │\n══╧══",
    "  ┌───┐\n  │   │\n  │   O\n  │  /│\\\n  │  / \\\n  │\n══╧══",
    "  ┌───┐\n  │   │\n  │  [O]\n  │  /│\\\n  │  / \\\n  │\n══╧══",
    "  ┌───┐\n  │   │\n  │  [X]\n  │  /│\\\n  │  / \\\n  │  R.I.P\n══╧══",
];
$hangman = $stages[min(count($wrong_guesses), count($stages) - 1)];

$page_title = 'Play — Word Guess';
include 'header.php';
?>
<div class="timer" id="timer" data-remaining="<?= $remaining ?>">
    <span class="timer-label">⏱️ Time Remaining</span>
    <span id="timer-display"><?= sprintf('%02d:%02d', floor($remaining / 60), $remaining % 60) ?></span>
</div>

<div class="game-info">
    <span class="player-name">🎯 <?= e($guesser_name) ?>'s turn</span>
    <span class="category-badge"><?= e($category) ?></span>
    <span class="difficulty-badge <?= e($difficulty) ?>"><?= ucfirst($difficulty) ?></span>
</div>

<div class="scoreboard">
    <div class="score-card">
        <div class="player-name-label"><?= e($_SESSION['player1']) ?></div>
        <div class="score-value"><?= $_SESSION['score_p1'] ?? 0 ?></div>
    </div>
    <div class="score-card">
        <div class="player-name-label"><?= e($_SESSION['player2']) ?></div>
        <div class="score-value"><?= $_SESSION['score_p2'] ?? 0 ?></div>
    </div>
</div>

<div class="hangman-wrapper">
    <div class="hangman-art"><?= $hangman ?></div>
</div>

<div class="word-display">
    <?php foreach ($word_letters as $char): ?>
        <span class="letter <?= in_array($char, $guessed) ? 'revealed' : '' ?>">
            <?= in_array($char, $guessed) ? strtoupper(e($char)) : '_' ?>
        </span>
    <?php endforeach; ?>
</div>

<div class="lives">
    <?php for ($i = 0; $i < $max_lives; $i++): ?>
        <?= ($i < $lives) ? '❤️' : '<span class="heart-lost">🤍</span>' ?>
    <?php endfor; ?>
    <div style="font-size: 0.8rem; color: #888; margin-top: 5px;"><?= $lives ?> / <?= $max_lives ?> lives</div>
</div>

<?php if (!empty($wrong_guesses)): ?>
    <div class="wrong-guesses">
        Wrong: 
        <?php foreach ($wrong_guesses as $wg): ?>
            <span><?= strtoupper(e($wg)) ?></span>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="hint-section">
    <?php if ($hints_remaining > 0): ?>
        <a href="play.php?hint=1" class="btn-hint">💡 Use Hint (<?= $hints_remaining ?> left — costs 1 ❤️)</a>
    <?php else: ?>
        <button class="btn-hint" disabled>💡 No hints remaining</button>
    <?php endif; ?>

    <?php if (($_SESSION['hint_revealed'] ?? false) && !empty($_SESSION['hint_text'])): ?>
        <div class="hint-text">💬 Hint: "<?= e($_SESSION['hint_text']) ?>"</div>
    <?php endif; ?>
</div>

<div class="alphabet-grid">
    <?php for ($i = 0; $i < 26; $i++):
        $char = chr(97 + $i);
        $is_guessed = in_array($char, $guessed);
        $cls = 'letter-btn' . ($is_guessed ? (strpos($word, $char) !== false ? ' correct' : ' wrong') : '');
    ?>
        <?php if ($is_guessed): ?>
            <span class="<?= $cls ?>"><?= strtoupper($char) ?></span>
        <?php else: ?>
            <a href="play.php?letter=<?= $char ?>" class="<?= $cls ?>"><?= strtoupper($char) ?></a>
        <?php endif; ?>
    <?php endfor; ?>
</div>

<script>
(function() {
    var remaining = <?= $remaining ?>;
    var timerEl = document.getElementById('timer');
    var displayEl = document.getElementById('timer-display');

    function update() {
        var mins = Math.floor(remaining / 60), secs = remaining % 60;
        displayEl.textContent = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        timerEl.classList.toggle('danger', remaining <= 5);
        timerEl.classList.toggle('warning', remaining > 5 && remaining <= 15);
    }
    function tick() {
        if (remaining <= 0) { window.location.href = 'play.php'; return; }
        remaining--;
        update();
    }
    update();
    setInterval(tick, 1000);
})();

document.addEventListener('keydown', function(e) {
    var key = e.key.toLowerCase();
    if (/^[a-z]$/.test(key)) {
        var link = Array.from(document.querySelectorAll('.alphabet-grid a.letter-btn'))
            .find(function(el) { return el.textContent.trim().toLowerCase() === key; });
        if (link) window.location.href = link.href;
    }
});
</script>
<?php include 'footer.php'; ?>
