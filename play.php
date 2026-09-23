<?php
/**
 * play.php — Main Game Play Screen
 * 
 * Handles letter guessing, timer validation, hints, hangman art,
 * and win/lose detection. This is the core game loop.
 */
session_start();

// Redirect if game not set up
if (!isset($_SESSION['word']) || !isset($_SESSION['player1'])) {
    header('Location: index.php');
    exit;
}

// ---- Game State ----
$word = $_SESSION['word'];
$guessed = $_SESSION['guessed'];
$lives = $_SESSION['lives'];
$max_lives = $_SESSION['max_lives'];
$start_time = $_SESSION['start_time'];
$time_limit = $_SESSION['time_limit'];
$setter = $_SESSION['current_setter'];
$setter_name = ($setter === 1) ? $_SESSION['player1'] : $_SESSION['player2'];
$guesser_name = ($setter === 1) ? $_SESSION['player2'] : $_SESSION['player1'];
$category = $_SESSION['category'] ?? 'Custom';
$difficulty = $_SESSION['difficulty'] ?? 'medium';
$hints_remaining = $_SESSION['hints_remaining'] ?? 0;

// ---- Server-side Timer Check ----
$elapsed = time() - $start_time;
$remaining = max(0, $time_limit - $elapsed);

if ($remaining <= 0 && !isset($_GET['letter']) && !isset($_GET['hint'])) {
    // Time expired — setter wins
    $guesser_key = ($setter === 1) ? 'score_p2' : 'score_p1';
    $setter_key = ($setter === 1) ? 'score_p1' : 'score_p2';
    $_SESSION[$setter_key] = ($_SESSION[$setter_key] ?? 0) + 1;

    // Save round history
    $history_entry = [
        'winner' => $setter_name,
        'word' => $word,
        'category' => $category,
        'time' => "Time's up!",
        'result' => 'timeout'
    ];
    $_SESSION['round_history'] = array_slice(
        array_merge([($history_entry)], $_SESSION['round_history'] ?? []), 0, 5
    );
    $_SESSION['game_result'] = 'timeout';
    $_SESSION['time_taken'] = $time_limit;

    header('Location: result.php');
    exit;
}

// ---- Handle Hint Request ----
if (isset($_GET['hint']) && $hints_remaining > 0) {
    // Find unrevealed letters
    $word_letters = array_unique(str_split($word));
    $unrevealed = array_diff($word_letters, $guessed);

    if (!empty($unrevealed)) {
        // Pick a random unrevealed letter
        $unrevealed_indexed = array_values($unrevealed);
        $hint_letter = $unrevealed_indexed[array_rand($unrevealed_indexed)];

        $_SESSION['guessed'][] = $hint_letter;
        $_SESSION['hints_remaining']--;
        $_SESSION['hints_used'] = ($_SESSION['hints_used'] ?? 0) + 1;
        $_SESSION['lives']--; // Costs 1 life
        $_SESSION['hint_revealed'] = true;
    }

    header('Location: play.php');
    exit;
}

// ---- Handle Letter Guess ----
if (isset($_GET['letter'])) {
    $letter = strtolower($_GET['letter']);

    // Validate: single letter, not already guessed
    if (preg_match('/^[a-z]$/', $letter) && !in_array($letter, $guessed)) {
        $_SESSION['guessed'][] = $letter;

        // Check if letter is in the word
        if (strpos($word, $letter) === false) {
            // Wrong guess
            $_SESSION['lives']--;
        }
    }

    // Re-check timer after guess
    $elapsed = time() - $start_time;
    $remaining = max(0, $time_limit - $elapsed);

    // Reload state after modification
    $guessed = $_SESSION['guessed'];
    $lives = $_SESSION['lives'];

    // ---- Check Win/Lose ----
    $word_letters = array_unique(str_split($word));
    $all_revealed = empty(array_diff($word_letters, $guessed));

    if ($all_revealed) {
        // Guesser wins
        $guesser_key = ($setter === 1) ? 'score_p2' : 'score_p1';
        $_SESSION[$guesser_key] = ($_SESSION[$guesser_key] ?? 0) + 1;

        $time_taken = time() - $start_time;
        $history_entry = [
            'winner' => $guesser_name,
            'word' => $word,
            'category' => $category,
            'time' => $time_taken . 's',
            'result' => 'win'
        ];
        $_SESSION['round_history'] = array_slice(
            array_merge([$history_entry], $_SESSION['round_history'] ?? []), 0, 5
        );
        $_SESSION['game_result'] = 'win';
        $_SESSION['time_taken'] = $time_taken;

        header('Location: result.php');
        exit;
    }

    if ($lives <= 0) {
        // Setter wins (guesser lost)
        $setter_key = ($setter === 1) ? 'score_p1' : 'score_p2';
        $_SESSION[$setter_key] = ($_SESSION[$setter_key] ?? 0) + 1;

        $time_taken = time() - $start_time;
        $history_entry = [
            'winner' => $setter_name,
            'word' => $word,
            'category' => $category,
            'time' => $time_taken . 's',
            'result' => 'lose'
        ];
        $_SESSION['round_history'] = array_slice(
            array_merge([$history_entry], $_SESSION['round_history'] ?? []), 0, 5
        );
        $_SESSION['game_result'] = 'lose';
        $_SESSION['time_taken'] = $time_taken;

        header('Location: result.php');
        exit;
    }

    if ($remaining <= 0) {
        // Timeout
        $setter_key = ($setter === 1) ? 'score_p1' : 'score_p2';
        $_SESSION[$setter_key] = ($_SESSION[$setter_key] ?? 0) + 1;

        $history_entry = [
            'winner' => $setter_name,
            'word' => $word,
            'category' => $category,
            'time' => "Time's up!",
            'result' => 'timeout'
        ];
        $_SESSION['round_history'] = array_slice(
            array_merge([$history_entry], $_SESSION['round_history'] ?? []), 0, 5
        );
        $_SESSION['game_result'] = 'timeout';
        $_SESSION['time_taken'] = $time_limit;

        header('Location: result.php');
        exit;
    }

    // Redirect to prevent form resubmission on refresh
    header('Location: play.php');
    exit;
}

// ---- Refresh State for Display ----
$guessed = $_SESSION['guessed'];
$lives = $_SESSION['lives'];
$hints_remaining = $_SESSION['hints_remaining'] ?? 0;
$elapsed = time() - $start_time;
$remaining = max(0, $time_limit - $elapsed);

// Build word display
$word_display = '';
$word_letters = str_split($word);

// Wrong guesses
$wrong_guesses = array_filter($guessed, function($l) use ($word) {
    return strpos($word, $l) === false;
});
$wrong_count = count($wrong_guesses);

// ---- Hangman Art ----
function getHangmanArt($wrong_count) {
    $stages = [
        // 0 wrong
        "  ┌───┐\n  │   │\n  │\n  │\n  │\n  │\n══╧══",
        // 1 wrong
        "  ┌───┐\n  │   │\n  │   O\n  │\n  │\n  │\n══╧══",
        // 2 wrong
        "  ┌───┐\n  │   │\n  │   O\n  │   │\n  │\n  │\n══╧══",
        // 3 wrong
        "  ┌───┐\n  │   │\n  │   O\n  │  /│\n  │\n  │\n══╧══",
        // 4 wrong
        "  ┌───┐\n  │   │\n  │   O\n  │  /│\\\n  │\n  │\n══╧══",
        // 5 wrong
        "  ┌───┐\n  │   │\n  │   O\n  │  /│\\\n  │  /\n  │\n══╧══",
        // 6 wrong
        "  ┌───┐\n  │   │\n  │   O\n  │  /│\\\n  │  / \\\n  │\n══╧══",
        // 7 wrong
        "  ┌───┐\n  │   │\n  │  [O]\n  │  /│\\\n  │  / \\\n  │\n══╧══",
        // 8 wrong (max)
        "  ┌───┐\n  │   │\n  │  [X]\n  │  /│\\\n  │  / \\\n  │  R.I.P\n══╧══",
    ];

    $index = min($wrong_count, count($stages) - 1);
    return $stages[$index];
}

$hangman = getHangmanArt($wrong_count);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Play — Word Guess</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <!-- Timer -->
        <div class="timer" id="timer" data-remaining="<?php echo $remaining; ?>">
            <span class="timer-label">⏱️ Time Remaining</span>
            <span id="timer-display">
                <?php 
                    $mins = floor($remaining / 60);
                    $secs = $remaining % 60;
                    echo sprintf('%02d:%02d', $mins, $secs);
                ?>
            </span>
        </div>

        <!-- Game Info Bar -->
        <div class="game-info">
            <span class="player-name">🎯 <?php echo $guesser_name; ?>'s turn</span>
            <span class="category-badge"><?php echo $category; ?></span>
            <span class="difficulty-badge <?php echo $difficulty; ?>"><?php echo ucfirst($difficulty); ?></span>
        </div>

        <!-- Score (compact) -->
        <div class="scoreboard">
            <div class="score-card">
                <div class="player-name-label"><?php echo $_SESSION['player1']; ?></div>
                <div class="score-value"><?php echo $_SESSION['score_p1'] ?? 0; ?></div>
            </div>
            <div class="score-card">
                <div class="player-name-label"><?php echo $_SESSION['player2']; ?></div>
                <div class="score-value"><?php echo $_SESSION['score_p2'] ?? 0; ?></div>
            </div>
        </div>

        <!-- Hangman Art -->
        <div class="hangman-wrapper">
            <div class="hangman-art"><?php echo $hangman; ?></div>
        </div>

        <!-- Word Display -->
        <div class="word-display">
            <?php foreach ($word_letters as $char): ?>
                <?php if (in_array($char, $guessed)): ?>
                    <span class="letter revealed"><?php echo strtoupper($char); ?></span>
                <?php else: ?>
                    <span class="letter">_</span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Lives -->
        <div class="lives">
            <?php for ($i = 0; $i < $max_lives; $i++): ?>
                <?php if ($i < $lives): ?>
                    ❤️
                <?php else: ?>
                    <span class="heart-lost">🤍</span>
                <?php endif; ?>
            <?php endfor; ?>
            <div style="font-size: 0.8rem; color: #888; margin-top: 5px;">
                <?php echo $lives; ?> / <?php echo $max_lives; ?> lives
            </div>
        </div>

        <!-- Wrong Guesses -->
        <?php if (!empty($wrong_guesses)): ?>
            <div class="wrong-guesses">
                Wrong: 
                <?php foreach ($wrong_guesses as $wg): ?>
                    <span><?php echo strtoupper($wg); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Hint Section -->
        <div class="hint-section">
            <?php if ($hints_remaining > 0): ?>
                <a href="play.php?hint=1" class="btn-hint">
                    💡 Use Hint (<?php echo $hints_remaining; ?> left — costs 1 ❤️)
                </a>
            <?php else: ?>
                <button class="btn-hint" disabled>
                    💡 No hints remaining
                </button>
            <?php endif; ?>

            <?php 
            // Show hint text if player used a hint and hint text exists
            if (($_SESSION['hint_revealed'] ?? false) && !empty($_SESSION['hint_text'])): 
            ?>
                <div class="hint-text">
                    💬 Hint: "<?php echo $_SESSION['hint_text']; ?>"
                </div>
            <?php endif; ?>
        </div>

        <!-- Alphabet Grid -->
        <div class="alphabet-grid">
            <?php for ($i = 0; $i < 26; $i++): ?>
                <?php 
                    $letter = chr(97 + $i); // a-z
                    $is_guessed = in_array($letter, $guessed);
                    $is_correct = $is_guessed && strpos($word, $letter) !== false;
                    $is_wrong = $is_guessed && strpos($word, $letter) === false;

                    if ($is_correct) {
                        $btn_class = 'letter-btn correct';
                    } elseif ($is_wrong) {
                        $btn_class = 'letter-btn wrong';
                    } else {
                        $btn_class = 'letter-btn';
                    }
                ?>
                <?php if ($is_guessed): ?>
                    <span class="<?php echo $btn_class; ?>"><?php echo strtoupper($letter); ?></span>
                <?php else: ?>
                    <a href="play.php?letter=<?php echo $letter; ?>" class="<?php echo $btn_class; ?>">
                        <?php echo strtoupper($letter); ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Timer JavaScript -->
    <script>
        (function() {
            var remaining = <?php echo $remaining; ?>;
            var timerEl = document.getElementById('timer');
            var displayEl = document.getElementById('timer-display');

            function updateDisplay() {
                var mins = Math.floor(remaining / 60);
                var secs = remaining % 60;
                displayEl.textContent = 
                    String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');

                // Color warnings
                timerEl.classList.remove('warning', 'danger');
                if (remaining <= 5) {
                    timerEl.classList.add('danger');
                } else if (remaining <= 15) {
                    timerEl.classList.add('warning');
                }
            }

            function tick() {
                if (remaining <= 0) {
                    // Time's up — redirect to trigger server-side timeout
                    window.location.href = 'play.php';
                    return;
                }
                remaining--;
                updateDisplay();
            }

            updateDisplay();
            setInterval(tick, 1000);
        })();

        // Keyboard support: press a letter key to guess
        document.addEventListener('keydown', function(e) {
            var key = e.key.toLowerCase();
            if (/^[a-z]$/.test(key)) {
                // Find the matching button and click it
                var links = document.querySelectorAll('.alphabet-grid a.letter-btn');
                for (var i = 0; i < links.length; i++) {
                    if (links[i].textContent.trim().toLowerCase() === key) {
                        window.location.href = links[i].href;
                        break;
                    }
                }
            }
        });
    </script>
</body>
</html>
