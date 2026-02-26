<?php
namespace Core;

use PDO;

/**
 * QueryBuilder - Wrapper para FluentPDO
 * Proporciona una interfaz moderna sobre FluentPDO heredado
 * Facilita migración futura a otro query builder si es necesario
 *
 * @package Core
 */
class QueryBuilder {

    private $fluent;
    private PDO $pdo;

    /**
     * Constructor
     */
    public function __construct(PDO $pdo = null) {
        $this->pdo = $pdo ?? Database::getInstance()->getPdo();

        // Cargar FluentPDO legacy
        if (!class_exists('FluentPDO')) {
            require_once BASE_PATH . '/legacy/FluentPDO/FluentPDO.php';
        }

        $this->fluent = new \FluentPDO($this->pdo);
    }

    /**
     * Obtener instancia de FluentPDO
     * Para compatibilidad con código legacy
     */
    public function getFluent() {
        return $this->fluent;
    }

    /**
     * Query FROM
     *
     * @param string $table Nombre de la tabla
     * @param mixed $primaryKey Clave primaria (opcional)
     * @return \SelectQuery
     */
    public function from(string $table, $primaryKey = null) {
        return $this->fluent->from($table, $primaryKey);
    }

    /**
     * INSERT INTO
     *
     * @param string $table Nombre de la tabla
     * @param array $values Valores a insertar
     * @return \InsertQuery
     */
    public function insertInto(string $table, array $values = []) {
        return $this->fluent->insertInto($table, $values);
    }

    /**
     * UPDATE
     *
     * @param string $table Nombre de la tabla
     * @param array $set Valores a actualizar
     * @param mixed $primaryKey Clave primaria (opcional)
     * @return \UpdateQuery
     */
    public function update(string $table, array $set = [], $primaryKey = null) {
        return $this->fluent->update($table, $set, $primaryKey);
    }

    /**
     * DELETE FROM
     *
     * @param string $table Nombre de la tabla
     * @param mixed $primaryKey Clave primaria (opcional)
     * @return \DeleteQuery
     */
    public function deleteFrom(string $table, $primaryKey = null) {
        return $this->fluent->deleteFrom($table, $primaryKey);
    }

    /**
     * Ejecutar query SQL directo
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
     * Obtener conexión PDO
     */
    public function getPdo(): PDO {
        return $this->pdo;
    }
}
