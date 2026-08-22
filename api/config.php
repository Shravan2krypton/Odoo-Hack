<?php
// ============================================================
// GlobeTrotter — API Config & DB Connection (PDO)
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'globetrotter');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ── CORS / JSON headers ──────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// ── PDO Connection ───────────────────────────────────────────
function getPDO(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            jsonError('Database connection failed', 500);
        }
    }
    return $pdo;
}

// ── Response Helpers ─────────────────────────────────────────
function jsonSuccess(mixed $data = [], int $code = 200): never {
    http_response_code($code);
    echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit();
}

function jsonError(string $message, int $code = 400): never {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit();
}

// ── Session Auth Guard ───────────────────────────────────────
function requireAuth(): int {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user_id'])) {
        jsonError('Unauthorized — please login', 401);
    }
    return (int)$_SESSION['user_id'];
}

function requireAdmin(): int {
    $uid = requireAuth();
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        jsonError('Forbidden — admin only', 403);
    }
    return $uid;
}

// ── Parse request body (JSON or form) ───────────────────────
function getBody(): array {
    $raw = file_get_contents('php://input');
    if (!empty($raw)) {
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) return $decoded;
    }
    return $_POST;
}
