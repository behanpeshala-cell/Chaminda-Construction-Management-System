<?php
/**
 * Database connection (PDO / MySQL)
 * Chaminda Construction Company Management System (CCMS)
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'ccms_db');
define('DB_USER', 'root');
define('DB_PASS', '');   // set your XAMPP MySQL password here if any

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed. Please make sure MySQL is running in XAMPP and the 'ccms_db' database has been imported. (" . $e->getMessage() . ")");
}

// App-wide settings
define('APP_NAME', 'CCMS - Chaminda Construction Company');
define('SESSION_TIMEOUT_SECONDS', 900);      // 15 min idle timeout
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_MINUTES', 15);
