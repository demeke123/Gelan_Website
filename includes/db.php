<?php
/** Database connection and table setup. */

function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;
    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    if (DB_DRIVER === 'sqlite') {
        if (!is_dir(dirname(SQLITE_PATH))) mkdir(dirname(SQLITE_PATH), 0750, true);
        $pdo = new PDO('sqlite:' . SQLITE_PATH, null, null, $opts);
    } else {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
    }
    return $pdo;
}

/** Creates all tables if they don't exist yet. Safe to run more than once. */
function db_install(): void
{
    $sqlite = DB_DRIVER === 'sqlite';
    $id   = $sqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY';
    $text = $sqlite ? 'TEXT' : 'MEDIUMTEXT';
    $dt   = $sqlite ? 'TEXT' : 'DATETIME';
    $tail = $sqlite ? '' : ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
    $now  = "$dt DEFAULT CURRENT_TIMESTAMP";

    $tables = [
        "CREATE TABLE IF NOT EXISTS users (
            id $id,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(20) NOT NULL DEFAULT 'editor',
            active TINYINT NOT NULL DEFAULT 1,
            last_login $dt NULL,
            created_at $now
        )$tail",
        "CREATE TABLE IF NOT EXISTS posts (
            id $id,
            type VARCHAR(20) NOT NULL DEFAULT 'news',
            title_en VARCHAR(255) NOT NULL DEFAULT '',
            title_am VARCHAR(255) NOT NULL DEFAULT '',
            body_en $text,
            body_am $text,
            publish_date DATE NOT NULL,
            event_date DATE NULL,
            is_published TINYINT NOT NULL DEFAULT 1,
            created_by INT NULL,
            created_at $now,
            updated_at $dt NULL
        )$tail",
        "CREATE TABLE IF NOT EXISTS gallery (
            id $id,
            filename VARCHAR(255) NOT NULL,
            caption_en VARCHAR(255) NOT NULL DEFAULT '',
            caption_am VARCHAR(255) NOT NULL DEFAULT '',
            created_by INT NULL,
            created_at $now
        )$tail",
        "CREATE TABLE IF NOT EXISTS settings (
            skey VARCHAR(100) NOT NULL PRIMARY KEY,
            svalue $text
        )$tail",
        "CREATE TABLE IF NOT EXISTS messages (
            id $id,
            name VARCHAR(120) NOT NULL,
            phone VARCHAR(40) NOT NULL DEFAULT '',
            email VARCHAR(190) NOT NULL DEFAULT '',
            subject VARCHAR(200) NOT NULL DEFAULT '',
            message $text,
            ip VARCHAR(45) NOT NULL DEFAULT '',
            is_read TINYINT NOT NULL DEFAULT 0,
            created_at $now
        )$tail",
        "CREATE TABLE IF NOT EXISTS login_attempts (
            id $id,
            ip VARCHAR(45) NOT NULL,
            attempted_at INT NOT NULL
        )$tail",
    ];
    foreach ($tables as $sql) db()->exec($sql);
}
