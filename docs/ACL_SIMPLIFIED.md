# Sistema ACL Simplificado

## Resumen

El sistema ACL (Access Control List) controla qué usuarios pueden acceder a qué recursos y qué operaciones pueden realizar.

## Permisos Disponibles

| Permiso | Descripción | Ejemplo |
|---------|-------------|---------|
| **crear** | Crear nuevos registros | Nuevo producto |
| **editar** | Modificar registros existentes | Actualizar precio |
| **borrar** | Eliminar registros | Borrar producto |
| **ver_todos** | Ver todos los registros | Lista completa |
| **admin** | Acceso administrativo completo | Sin restricciones |

## Uso en Controllers

```php
// Verificar autenticación
if ($this->isAuthenticated()) {
    // Usuario logueado
}

// Requerir autenticación (redirige si no)
$this->requireAuth();

// Verificar permiso específico
if ($this->can('crear')) {
    // Puede crear registros
}

// Requerir permiso (redirige si no)
$this->requirePermission('editar');

// Obtener ID del usuario actual
$userId = $this->userId();
```

## Para la Prueba Técnica

**Asume que el usuario tiene todos los permisos.**

No necesitas configurar permisos en BD. Para propósitos de la prueba, puedes asumir:

```php
$_SESSION['User_ID'] = 1;
$_SESSION['Controladores_aceptados'] = [
    'Kardex' => [
        'index' => [
            'crear' => '1',
            'editar' => '1',
            'borrar' => '1',
            'ver_todos' => '1',
            'admin' => '1'
        ]
    ]
];
```
