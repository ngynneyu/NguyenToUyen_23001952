<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(int $status, array $body): never
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

function fail(int $status, string $message): never
{
    respond($status, ['success' => false, 'message' => $message]);
}

function database(): PDO
{
    $host = getenv('WEEK9_DB_HOST') ?: '127.0.0.1';
    $port = getenv('WEEK9_DB_PORT') ?: '3306';
    $name = getenv('WEEK9_DB_NAME') ?: 'week9';
    $user = getenv('WEEK9_DB_USER') ?: 'root';
    $password = getenv('WEEK9_DB_PASSWORD');

    return new PDO(
        "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4",
        $user,
        $password === false ? '' : $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_FOUND_ROWS => true,
        ]
    );
}

function removeImage(?string $path): void
{
    if ($path === null || !preg_match('~\Auploads/[a-f0-9]{32}\.(?:jpg|png)\z~', $path)) {
        return;
    }

    $file = dirname(__DIR__) . '/' . $path;
    if (is_file($file)) {
        @unlink($file);
    }
}
