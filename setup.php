<?php
session_start();
$error = '';
$diffs = [
    'easy'   => ['label' => '😊 Easy',   'detail' => '8 lives · 90s · 2 hints'],
    'medium' => ['label' => '😐 Medium', 'detail' => '6 lives · 60s · 1 hint'],
    'hard'   => ['label' => '😈 Hard',   'detail' => '4 lives · 30s · 0 hints']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p1 = trim($_POST['player1'] ?? '');
    $p2 = trim($_POST['player2'] ?? '');
    $diff = $_POST['difficulty'] ?? 'medium';

    if (!$p1 || !$p2) {
        $error = 'Both player names are required!';
    } elseif (strcasecmp($p1, $p2) === 0) {
        $error = 'Player names must be different!';
    } elseif (!isset($diffs[$diff])) {
        $error = 'Please select a valid difficulty!';
    } else {
        $_SESSION['player1'] = htmlspecialchars($p1);
        $_SESSION['player2'] = htmlspecialchars($p2);
        $_SESSION['difficulty'] = $diff;
        $_SESSION['score_p1'] = $_SESSION['score_p2'] = 0;
        $_SESSION['current_setter'] = 1;
        $_SESSION['round_history'] = [];
        header('Location: word_entry.php');
        exit;
    }
}
$page_title = 'Player Setup — Word Guess';
include 'header.php';
$sel_diff = $_POST['difficulty'] ?? 'medium';
?>
<h1>👤 Player Setup</h1>
<p class="subtitle">Enter your names and choose difficulty</p>

<?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>

<form method="POST" action="setup.php">
    <div class="form-group">
        <label for="player1">🔴 Player 1 Name</label>
        <input type="text" id="player1" name="player1" placeholder="Enter Player 1 name"
               value="<?= e($_POST['player1'] ?? '') ?>" maxlength="20" required>
    </div>

    <div class="form-group">
        <label for="player2">🔵 Player 2 Name</label>
        <input type="text" id="player2" name="player2" placeholder="Enter Player 2 name"
               value="<?= e($_POST['player2'] ?? '') ?>" maxlength="20" required>
    </div>

    <div class="form-group">
        <label>🎯 Difficulty</label>
        <div class="difficulty-options">
            <?php foreach ($diffs as $k => $d): ?>
                <div class="difficulty-option">
                    <input type="radio" id="<?= $k ?>" name="difficulty" value="<?= $k ?>" <?= ($sel_diff === $k) ? 'checked' : '' ?>>
                    <label for="<?= $k ?>"><?= $d['label'] ?> <span class="diff-detail"><?= $d['detail'] ?></span></label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-block">🎮 Start Game</button>
</form>

<div class="btn-group" style="margin-top: 15px;">
    <a href="index.php" class="btn btn-secondary">← Back</a>
</div>
<?php include 'footer.php'; ?>
