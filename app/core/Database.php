<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Database — singleton PDO wrapper.
 * All queries go through here so connection is created once per request.
 */
class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require ROOT_PATH . '/config/database.php';

            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";

            try {
                self::$instance = new PDO($dsn, $config['username'], $config['password'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // Don't expose connection details to the browser in production
                error_log('DB connection failed: ' . $e->getMessage());
                die('Database connection error. Please check your configuration.');
            }
        }

        return self::$instance;
    }

    // Prevent instantiation and cloning
    private function __construct() {}
    private function __clone() {}
}
