<?php
// ============================================================
// GlobeTrotter — Authentication & Role Access Guard
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Enforce authentication on protected pages.
 * Redirects unauthenticated visitors to login.php immediately.
 */
function require_auth(): int {
    if (empty($_SESSION['user_id'])) {
        $currentPage = urlencode($_SERVER['REQUEST_URI'] ?? 'dashboard.php');
        header("Location: login.php?auth=required&redirect={$currentPage}");
        exit();
    }
    return (int)$_SESSION['user_id'];
}

/**
 * Enforce Administrator privileges.
 */
function require_admin(): int {
    $userId = require_auth();
    if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: dashboard.php?error=forbidden");
        exit();
    }
    return $userId;
}

/**
 * Helper boolean checkers
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function is_admin(): bool {
    return !empty($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function current_user_id(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function current_user_name(): string {
    return $_SESSION['first_name'] ?? 'Traveler';
}
?>
