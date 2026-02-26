<?php
namespace Core;

/**
 * Response - Manejo de respuestas HTTP
 *
 * @package Core
 */
class Response {

    /**
     * Redireccionar
     */
    public function redirect(string $url, int $code = 302): void {
        header("Location: {$url}", true, $code);
        exit;
    }

    /**
     * Respuesta JSON
     */
    public function json(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Establecer código de estado HTTP
     */
    public function setStatusCode(int $code): void {
        http_response_code($code);
    }

    /**
     * Establecer header
     */
    public function setHeader(string $name, string $value): void {
        header("{$name}: {$value}");
    }
}
