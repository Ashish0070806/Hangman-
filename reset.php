<?php
session_start();
$type = $_GET['type'] ?? 'full';

if ($type === 'round') {
    unset(
        $_SESSION['word'], $_SESSION['hint_text'], $_SESSION['category'],
        $_SESSION['guessed'], $_SESSION['lives'], $_SESSION['max_lives'],
        $_SESSION['hints_remaining'], $_SESSION['hints_used'],
        $_SESSION['start_time'], $_SESSION['time_limit'], $_SESSION['hint_revealed']
    );
    if (isset($_SESSION['current_setter'])) {
        $_SESSION['current_setter'] = ($_SESSION['current_setter'] === 1) ? 2 : 1;
    }
    header('Location: word_entry.php');
} else {
    session_unset();
    session_destroy();
    header('Location: index.php');
}
exit;
