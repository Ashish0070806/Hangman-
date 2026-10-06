<?php
session_start();
if (!isset($_SESSION['player1'], $_SESSION['player2'])) {
    header('Location: setup.php');
    exit;
}

$error = '';
$setter = $_SESSION['current_setter'];
$setter_name = ($setter === 1) ? $_SESSION['player1'] : $_SESSION['player2'];
$guesser_name = ($setter === 1) ? $_SESSION['player2'] : $_SESSION['player1'];

$difficulty_config = [
    'easy'   => ['lives' => 8, 'time' => 90, 'hints' => 2],
    'medium' => ['lives' => 6, 'time' => 60, 'hints' => 1],
    'hard'   => ['lives' => 4, 'time' => 30, 'hints' => 0],
];
$difficulty = $_SESSION['difficulty'] ?? 'medium';
$config = $difficulty_config[$difficulty];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $word = trim($_POST['word'] ?? '');
    $category = $_POST['category'] ?? 'Custom';
    $hint_text = trim($_POST['hint_text'] ?? '');

    if (empty($word)) {
        $error = 'Please enter a word!';
    } elseif (!preg_match('/^[a-zA-Z]+$/', $word)) {
        $error = 'Word must contain only letters (A-Z)!';
    } elseif (strlen($word) < 3 || strlen($word) > 15) {
        $error = 'Word must be 3 to 15 characters long!';
    } else {
        $_SESSION['word'] = strtolower($word);
        $_SESSION['category'] = htmlspecialchars($category);
        $_SESSION['hint_text'] = htmlspecialchars($hint_text);
        $_SESSION['guessed'] = [];
        $_SESSION['lives'] = $_SESSION['max_lives'] = $config['lives'];
        $_SESSION['hints_remaining'] = $config['hints'];
        $_SESSION['hints_used'] = 0;
        $_SESSION['hint_revealed'] = false;
        $_SESSION['start_time'] = time();
        $_SESSION['time_limit'] = $config['time'];

        header('Location: play.php');
        exit;
    }
}

$categories = ['🐾 Animals', '🍎 Fruits', '🌍 Countries', '💻 Technology', '⚽ Sports', '✏️ Custom'];
$page_title = 'Enter Word — Word Guess';
include 'header.php';
?>
<h1>🔤 Enter Secret Word</h1>
<p class="subtitle"><?= e($setter_name) ?>, it's your turn to set the word!</p>

<div class="warning-box">
    ⚠️ <strong><?= e($guesser_name) ?></strong>, please look away while the word is being entered!
</div>

<?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>

<form method="POST" action="word_entry.php">
    <div class="form-group">
        <label for="category">📂 Category</label>
        <select id="category" name="category">
            <?php foreach ($categories as $cat): ?>
                <option value="<?= e($cat) ?>" <?= (($_POST['category'] ?? '') === $cat) ? 'selected' : '' ?>>
                    <?= e($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="word">🔒 Secret Word (3–15 letters)</label>
        <input type="password" id="word" name="word" placeholder="Enter your secret word"
               maxlength="15" required autocomplete="off">
    </div>

    <div class="form-group">
        <label for="hint_text">💡 Hint for guesser (optional)</label>
        <input type="text" id="hint_text" name="hint_text" placeholder="e.g. 'It's a tropical fruit'"
               value="<?= e($_POST['hint_text'] ?? '') ?>" maxlength="100">
    </div>

    <div class="rules" style="margin-bottom: 20px;">
        <h3>⚙️ Round Settings (<?= ucfirst($difficulty) ?>)</h3>
        <ul>
            <li>❤️ Lives: <strong><?= $config['lives'] ?></strong></li>
            <li>⏱️ Timer: <strong><?= $config['time'] ?> seconds</strong></li>
            <li>💡 Hints: <strong><?= $config['hints'] ?></strong></li>
        </ul>
    </div>

    <button type="submit" class="btn btn-primary btn-block">🎮 Start Round</button>
</form>

<div class="btn-group" style="margin-top: 15px;">
    <a href="reset.php?type=full" class="btn btn-secondary">← New Game</a>
</div>
<?php include 'footer.php'; ?>
