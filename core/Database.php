<?php
namespace Core;

use PDO;
use PDOException;

/**
 * Database - Singleton para conexión a base de datos
 * Proporciona acceso a PDO para el sistema moderno
 *
 * @package Core
 */
class Database {

    private static ?Database $instance = null;
    private PDO $pdo;

    /**
     * Constructor privado (Singleton)
     */
    private function __construct() {
        $config = require BASE_PATH . '/config/database.php';

        $dsn = sprintf(
            "%s:host=%s;dbname=%s;charset=%s",
            $config['driver'],
            $config['host'],
            $config['database'],
            $config['charset']
        );

        try {
            $this->pdo = new PDO(
                $dsn,
                $config['user'],
                $config['pass'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']}"
                ]
            );
        } catch (PDOException $e) {
            // En producción, loguear el error
            error_log("Database connection failed: " . $e->getMessage());
            die("Error de conexión a la base de datos. Contacte al administrador.");
        }
    }

    /**
     * Obtener instancia única
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Obtener conexión PDO
     */
    public function getPdo(): PDO {
        return $this->pdo;
    }

    /**
     * Ejecutar query directo
     */
    public function query(string $sql): \PDOStatement {
        return $this->pdo->query($sql);
    }

    /**
     * Preparar statement
     */
    public function prepare(string $sql): \PDOStatement {
        return $this->pdo->prepare($sql);
    }

    /**
     * Último ID insertado
     */
    public function lastInsertId(): string {
        return $this->pdo->lastInsertId();
    }

    /**
     * Iniciar transacción
     */
    public function beginTransaction(): bool {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit transacción
     */
    public function commit(): bool {
        return $this->pdo->commit();
    }

    /**
     * Rollback transacción
     */
    public function rollBack(): bool {
        return $this->pdo->rollBack();
    }

    /**
     * Prevenir clonación (Singleton)
     */
    private function __clone() {}

    /**
     * Prevenir unserialize (Singleton)
     */
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }
}
