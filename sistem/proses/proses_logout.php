<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    if (isset($_SESSION['user_login'])) {
        catatLog('Logout', 'Pengguna ' . $_SESSION['user_data']['nama_lengkap'] . ' telah keluar.');
    }
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
    header('Location: ' . BASE_URL . 'index.php?halaman=login');
    exit;
} else {
    http_response_code(405);
    echo "Method Not Allowed";
    exit;
}