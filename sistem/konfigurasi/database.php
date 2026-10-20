<?php
// Ambil variabel environment dari Node.js (Electron) jika ada, jika tidak fallback ke folder lokal
$db_dir = getenv('DATABASE_DIR') ?: SISTEM_PATH . '/database';
define('DB_PATH', $db_dir . '/koperasi.sqlite');