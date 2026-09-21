<?php
namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Gestor de conexión PDO con soporte transaccional bajo el patrón Singleton.
 */
class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    /**
     * Retorna la instancia activa de PDO con configuración segura.
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $configFile = dirname(__DIR__, 2) . '/config/database.php';

            if (!file_exists($configFile)) {
                throw new RuntimeException("Archivo de configuración de base de datos no encontrado en: {$configFile}");
            }

            $config = require $configFile;

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $config['host'],
                $config['port'] ?? 3306,
                $config['database'],
                $config['charset'] ?? 'utf8mb4'
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // Prevenir inyecciones SQL mediante prepares nativos
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']} COLLATE utf8mb4_unicode_ci"
            ];

            try {
                self::$instance = new PDO($dsn, $config['username'], $config['password'], $options);
            } catch (PDOException $e) {
                error_log("Fallo crítico de conexión PDO: " . $e->getMessage());
                throw new RuntimeException("Error al conectar con la base de datos MariaDB: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    /**
     * Inicia una transacción atómica segura si no hay otra en curso.
     */
    public static function beginTransaction(): bool {
        $pdo = self::getConnection();
        return $pdo->inTransaction() ? false : $pdo->beginTransaction();
    }

    /**
     * Confirma la transacción actual.
     */
    public static function commit(): bool {
        $pdo = self::getConnection();
        return $pdo->inTransaction() ? $pdo->commit() : false;
    }

    /**
     * Revierte cambios en caso de error transaccional.
     */
    public static function rollBack(): bool {
        $pdo = self::getConnection();
        return $pdo->inTransaction() ? $pdo->rollBack() : false;
    }
}