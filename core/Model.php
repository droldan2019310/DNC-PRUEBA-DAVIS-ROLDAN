<?php
declare(strict_types=1);

namespace Core;

use PDO;

/**
 * Model - Clase base para modelos modernos
 * Soporte para FluentPDO y operaciones básicas CRUD
 * Actualizado para PHP 8.1+
 *
 * @package Core
 */
class Model {

    protected Database $db;
    protected QueryBuilder $queryBuilder;
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected bool $timestamps = true;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->queryBuilder = new QueryBuilder();
    }

    /**
     * Obtener QueryBuilder (FluentPDO wrapper)
     */
    public function query(): QueryBuilder {
        return $this->queryBuilder;
    }

    /**
     * Acceso directo a FluentPDO
     * Para compatibilidad con código legacy
     */
    public function fluent(): mixed {
        return $this->queryBuilder->getFluent();
    }

    /**
     * Query FROM con FluentPDO
     */
    public function from(string|null $table = null): mixed {
        return $this->queryBuilder->from($table ?? $this->table);
    }

    /**
     * Obtener todos los registros
     *
     * @return array<int, mixed>
     */
    public function all(): array {
        return $this->from()->fetchAll();
    }

    /**
     * Obtener por ID
     */
    public function find(int $id): object|null {
        $result = $this->from()
            ->where("{$this->primaryKey} = ?", $id)
            ->fetch();

        return $result ? (object)$result : null;
    }

    /**
     * Crear registro con FluentPDO
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): int {
        // Agregar timestamps si existen
        if ($this->timestamps) {
            $data['fecha_creacion'] = date('Y-m-d H:i:s');
            $data['creado_id'] = $_SESSION['Id_Usuario'] ?? null;
        }

        $query = $this->queryBuilder->insertInto($this->table, $data);
        $query->execute();

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualizar registro
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool {
        // Agregar timestamps si existen
        if ($this->timestamps) {
            $data['fecha_modificacion'] = date('Y-m-d H:i:s');
            $data['modificado_id'] = $_SESSION['Id_Usuario'] ?? null;
        }

        $query = $this->queryBuilder->update($this->table, $data, $id);
        return $query->execute() !== false;
    }

    /**
     * Eliminar registro
     */
    public function delete(int $id): bool {
        $query = $this->queryBuilder->deleteFrom($this->table, $id);
        return $query->execute() !== false;
    }

    /**
     * Contar registros
     */
    public function count(): int {
        $result = $this->from()->count();
        return (int)$result;
    }
}
