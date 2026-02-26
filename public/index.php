<?php
/**
 * Front Controller - Prueba Técnica
 * Versión simplificada solo para testing
 */

// Autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Iniciar sesión
session_start();

// Cargar .env (si existe)
if (file_exists(__DIR__ . '/../.env')) {
    // Parsear .env manualmente (sin librería)
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Mensaje de bienvenida (para verificar setup)
if (!isset($_GET['controller'])) {
    echo "<h1>DNC-ERP Test Environment</h1>";
    echo "<p><strong>Setup OK!</strong> El servidor está funcionando.</p>";
    echo "<p>Este es el repositorio de prueba técnica.</p>";
    echo "<hr>";
    echo "<p><small>Framework Version: PSR-4 Hybrid</small></p>";
    exit();
}

// Aquí iría tu router completo
// Para la prueba, esto es suficiente
echo "Router activo - Controlador: " . htmlspecialchars($_GET['controller']);
