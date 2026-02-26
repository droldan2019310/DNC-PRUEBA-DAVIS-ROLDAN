<?php
declare(strict_types=1);

namespace Core;

/**
 * Session - Manejo de sesiones
 * Actualizado para PHP 8.1+
 *
 * @package Core
 */
class Session {

    /**
     * Obtener valor de sesión
     */
    public function get(string $key, mixed $default = null): mixed {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Establecer valor en sesión
     */
    public function set(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }

    /**
     * Verificar si existe clave
     */
    public function has(string $key): bool {
        return isset($_SESSION[$key]);
    }

    /**
     * Eliminar clave de sesión
     */
    public function remove(string $key): void {
        unset($_SESSION[$key]);
    }

    /**
     * Obtener todas las variables de sesión
     *
     * @return array<string, mixed>
     */
    public function all(): array {
        return $_SESSION;
    }

    /**
     * Destruir sesión
     */
    public function destroy(): void {
        session_destroy();
        $_SESSION = [];
    }

    /**
     * Regenerar ID de sesión
     */
    public function regenerate(): void {
        session_regenerate_id(true);
    }
}
