<?php

declare(strict_types=1);

/**
 * Bootstrap application runtime, including local session storage for self-contained runs.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    $sessionPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions';
    if (!is_dir($sessionPath) && !mkdir($sessionPath, 0777, true) && !is_dir($sessionPath)) {
        throw new RuntimeException('Không thể tạo thư mục session cục bộ.');
    }

    session_save_path($sessionPath);
}

session_start();

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/action_handler.php';

$pdo = db();
initialize_database($pdo);
handle_post_actions();
