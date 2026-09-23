<?php
/**
 * reset.php — Session Reset Handler
 * 
 * Handles full game reset or new-round reset.
 * Usage: reset.php?type=full  → clears everything, redirects to index
 *        reset.php?type=round → clears round data, keeps scores, redirects to word_entry
 */
session_start();

$type = isset($_GET['type']) ? $_GET['type'] : 'full';

if ($type === 'round') {
    // Keep player names, scores, difficulty, and history — reset round data only
    unset($_SESSION['word']);
    unset($_SESSION['hint_text']);
    unset($_SESSION['category']);
    unset($_SESSION['guessed']);
    unset($_SESSION['lives']);
    unset($_SESSION['max_lives']);
    unset($_SESSION['hints_remaining']);
    unset($_SESSION['hints_used']);
    unset($_SESSION['start_time']);
    unset($_SESSION['time_limit']);
    unset($_SESSION['hint_revealed']);

    // Swap roles: if player 1 was setting, now player 2 sets
    if (isset($_SESSION['current_setter'])) {
        $_SESSION['current_setter'] = ($_SESSION['current_setter'] === 1) ? 2 : 1;
    }

    header('Location: word_entry.php');
    exit;
} else {
    // Full reset — destroy everything
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}
