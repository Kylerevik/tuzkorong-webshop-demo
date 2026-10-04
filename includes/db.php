<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $config = require __DIR__ . '/config.php';
    $db = $config['db'];

    try {
        $pdo = new PDO(
            "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4",
            $db['user'],
            $db['pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $e) {
        error_log('Adatbázis-kapcsolati hiba: ' . $e->getMessage());
        http_response_code(503);
        exit('Az oldal átmenetileg nem érhető el. Kérjük, próbálja meg később.');
    }

    return $pdo;
}
