<?php
declare(strict_types=1);

namespace Core;

/**
 * Request - Manejo de peticiones HTTP
 * Actualizado para PHP 8.1+
 *
 * @package Core
 */
class Request {

    /**
     * Verificar si es petición POST
     */
    public function isPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Verificar si es petición GET
     */
    public function isGet(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Obtener datos POST
     */
    public function post(string|null $key = null, mixed $default = null): mixed {
        if ($key === null) {
            return $_POST;
        }

        return $_POST[$key] ?? $default;
    }

    /**
     * Obtener datos GET
     */
    public function get(string|null $key = null, mixed $default = null): mixed {
        if ($key === null) {
            return $_GET;
        }

        return $_GET[$key] ?? $default;
    }

    /**
     * Obtener URI
     */
    public function uri(): string {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    /**
     * Obtener método HTTP
     */
    public function method(): string {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Validar token CSRF
     */
    public function validateCsrfToken(): bool {
        $token = $this->post('ToKen') ?? $this->get('ToKen');

        if (!$token || !isset($_SESSION['ToKen'])) {
            return false;
        }

        if (is_array($_SESSION['ToKen'])) {
            return in_array($token, $_SESSION['ToKen']);
        }

        return $token === $_SESSION['ToKen'];
    }

    /**
     * Verificar si es petición AJAX
     */
    public function isAjax(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Obtener header específico
     */
    public function header(string $key): string|null {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $_SERVER[$key] ?? null;
    }

    /**
     * Obtener IP del cliente
     */
    public function ip(): string {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Obtener user agent
     */
    public function userAgent(): string {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Sanitizar valor contra inyección
     *
     * @param mixed $value
     * @return mixed
     */
    public function sanitize(mixed $value): mixed {
        if (is_array($value)) {
            return array_map([$this, 'sanitize'], $value);
        }

        if (!is_string($value)) {
            return $value;
        }

        // Remover caracteres peligrosos
        $value = strip_tags($value);
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        // Remover null bytes
        $value = str_replace(chr(0), '', $value);

        // Remover caracteres de control peligrosos
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);

        return trim($value);
    }

    /**
     * Obtener parámetro sanitizado de POST
     *
     * @param string $key Nombre del parámetro
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    public function postSanitized(string $key, mixed $default = null): mixed {
        $value = $this->post($key);
        if ($value === null) {
            return $default;
        }
        return $this->sanitize($value);
    }

    /**
     * Obtener parámetro sanitizado de GET
     *
     * @param string $key Nombre del parámetro
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    public function getSanitized(string $key, mixed $default = null): mixed {
        $value = $this->get($key);
        if ($value === null) {
            return $default;
        }
        return $this->sanitize($value);
    }

    /**
     * Obtener todos los parámetros POST sanitizados
     *
     * @param array<int, string> $exclude Claves a excluir
     * @return array<string, mixed>
     */
    public function allPostSanitized(array $exclude = ['ToKen', 'token']): array {
        $data = $_POST;

        // Remover claves excluidas
        foreach ($exclude as $key) {
            unset($data[$key]);
        }

        return $this->sanitize($data);
    }

    /**
     * Validar parámetro contra patrón regex
     *
     * @param string $value Valor a validar
     * @param string $pattern Patrón regex
     * @return bool
     */
    public function validate(string $value, string $pattern): bool {
        return preg_match($pattern, $value) === 1;
    }

    /**
     * Obtener parámetro como entero
     *
     * @param string $key Nombre del parámetro
     * @param int $default Valor por defecto
     * @return int
     */
    public function getInt(string $key, int $default = 0): int {
        $value = $this->get($key) ?? $this->post($key);
        return (int) filter_var($value, FILTER_VALIDATE_INT, ['options' => ['default' => $default]]);
    }

    /**
     * Obtener parámetro como float
     *
     * @param string $key Nombre del parámetro
     * @param float $default Valor por defecto
     * @return float
     */
    public function getFloat(string $key, float $default = 0.0): float {
        $value = $this->get($key) ?? $this->post($key);
        return (float) filter_var($value, FILTER_VALIDATE_FLOAT, ['options' => ['default' => $default]]);
    }

    /**
     * Obtener parámetro como booleano
     *
     * @param string $key Nombre del parámetro
     * @param bool $default Valor por defecto
     * @return bool
     */
    public function getBool(string $key, bool $default = false): bool {
        $value = $this->get($key) ?? $this->post($key);

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    /**
     * Obtener parámetro como email validado
     *
     * @param string $key Nombre del parámetro
     * @param string|null $default Valor por defecto
     * @return string|null
     */
    public function getEmail(string $key, string|null $default = null): string|null {
        $value = $this->get($key) ?? $this->post($key);

        if ($value === null) {
            return $default;
        }

        $email = filter_var($value, FILTER_VALIDATE_EMAIL);
        return $email !== false ? $email : $default;
    }
}
