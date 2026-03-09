<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function is_admin() {
    return !empty($_SESSION['is_admin']);
}
function require_admin() {
    if (!is_admin()) {
        $next = urlencode($_SERVER['REQUEST_URI'] ?? '/');
        header("Location: /login.php?next=$next");
        exit;
    }
}
