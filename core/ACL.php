<?php
declare(strict_types=1);

namespace Core;

/**
 * ACL - Sistema de Control de Acceso simplificado
 * Para prueba técnica
 */
class ACL {
    
    public static function isAuthenticated(): bool {
        return isset($_SESSION['User_ID']) && !empty($_SESSION['User_ID']);
    }
    
    public static function requireAuth(string $redirectTo = '/index.php?controller=Login&action=index'): void {
        if (!self::isAuthenticated()) {
            header("Location: $redirectTo");
            exit();
        }
    }
    
    public static function canAccessController(string $controller): bool {
        if (!isset($_SESSION['Controladores_aceptados'])) {
            return false;
        }
        return isset($_SESSION['Controladores_aceptados'][$controller]);
    }
    
    public static function requireController(string $controller, string $redirectTo = '/index.php'): void {
        if (!self::canAccessController($controller)) {
            header("Location: $redirectTo");
            exit();
        }
    }
    
    public static function hasPermission(string $controller, string $permission, ?string $action = 'index'): bool {
        if (!isset($_SESSION['Controladores_aceptados'][$controller][$action])) {
            return false;
        }
        
        $permisos = $_SESSION['Controladores_aceptados'][$controller][$action];
        
        // Mapeo de permisos
        $map = [
            'crear' => 'crear',
            'editar' => 'editar',
            'borrar' => 'borrar',
            'leer' => 'ver_todos',
            'admin' => 'admin'
        ];
        
        $permissionKey = $map[$permission] ?? $permission;
        return ($permisos[$permissionKey] ?? '0') === '1';
    }
    
    public static function requirePermission(
        string $controller,
        string $permission,
        ?string $action = 'index',
        string $redirectTo = '/index.php'
    ): void {
        if (!self::hasPermission($controller, $permission, $action)) {
            header("Location: $redirectTo");
            exit();
        }
    }
    
    public static function isAdmin(string $controller, ?string $action = 'index'): bool {
        return self::hasPermission($controller, 'admin', $action);
    }
    
    public static function getCurrentUser(): ?array {
        if (!self::isAuthenticated()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['User_ID'] ?? null,
            'nombre' => $_SESSION['Nombre_Usuario'] ?? null,
            'email' => $_SESSION['Email_Usuario'] ?? null
        ];
    }
    
    public static function getUserId(): ?int {
        return isset($_SESSION['User_ID']) ? (int)$_SESSION['User_ID'] : null;
    }
    
    public static function getPermissions(string $controller, ?string $action = 'index'): ?array {
        if (!isset($_SESSION['Controladores_aceptados'][$controller][$action])) {
            return null;
        }
        return $_SESSION['Controladores_aceptados'][$controller][$action];
    }
}
