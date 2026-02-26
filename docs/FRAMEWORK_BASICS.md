# Framework Basics - DNC-ERP

## Arquitectura General

El framework DNC-ERP usa un patrón MVC híbrido personalizado que permite coexistencia de código legacy y moderno (PSR-4).

```
┌─────────────────────────────────────────────┐
│             USUARIO (Browser)               │
└──────────────┬──────────────────────────────┘
               │ HTTP Request
               ▼
┌─────────────────────────────────────────────┐
│       ROUTER (public/index.php)             │
│  • Valida autenticación                     │
│  • Verifica permisos ACL                    │
│  • Carga controlador apropiado              │
└──────────────┬──────────────────────────────┘
               │
       ┌───────┴────────┐
       ▼                ▼
  ┌─────────┐      ┌──────────┐
  │ LEGACY  │      │  PSR-4   │
  │  (76%)  │      │  (24%)   │
  └────┬────┘      └────┬─────┘
       │                │
       └────────┬───────┘
                ▼
      ┌──────────────────┐
      │   REPOSITORY     │
      │   (FluentPDO)    │
      └────────┬─────────┘
               ▼
      ┌──────────────────┐
      │    MySQL DB      │
      └──────────────────┘
```

## Clases Core Principales

### Core\Controller

Clase base para todos los controladores PSR-4.

**Uso:**
```php
<?php
namespace App\Controllers;

use Core\Controller;

class MiController extends Controller {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function index(): void {
        // Tu código aquí
        $this->render('mi/vista', ['data' => $datos]);
    }
}
```

**Métodos disponibles:**

```php
// Renderizado
$this->render(string $view, array $data): void
$this->json(array $data, int $code = 200): void
$this->redirect(string $url, int $code = 302): void

// Request
$this->isPost(): bool
$this->isGet(): bool
$this->post(?string $key = null, $default = null): mixed
$this->get(?string $key = null, $default = null): mixed

// ACL (Control de Acceso)
$this->isAuthenticated(): bool
$this->requireAuth(string $redirectTo = '/login'): void
$this->canAccess(?string $controller = null): bool
$this->requireAccess(?string $controller = null): void
$this->can(string $permission): bool
$this->userId(): ?int
```

### Core\Model

Clase base para repositorios (modelos de datos).

**Uso:**
```php
<?php
namespace App\Models\Repository;

use Core\Model;

class ProductoRepository extends Model {
    
    protected string $table = 'productos';
    
    public function getActive(): array {
        return $this->from()
            ->where('estado = ?', 1)
            ->fetchAll();
    }
}
```

**Métodos heredados:**

```php
// CRUD Básico
$repo->find(int $id): ?object
$repo->create(array $data): int
$repo->update(int $id, array $data): bool
$repo->delete(int $id): bool
$repo->all(): array

// FluentPDO
$repo->fluent(): FluentPDO
$repo->from(?string $table = null): SelectQuery
$repo->query(): QueryBuilder
```

## FluentPDO - Query Builder

FluentPDO es el ORM/Query Builder usado en el sistema.

### SELECT Queries

```php
// Simple
$productos = $this->fluent()
    ->from('productos')
    ->where('estado = ?', 1)
    ->fetchAll();

// Con JOINs
$productos = $this->fluent()
    ->from('productos p')
    ->leftJoin('categorias c ON c.id = p.categoria_id')
    ->select('p.*')
    ->select('c.nombre AS categoria_nombre')
    ->where('p.estado = ?', 1)
    ->orderBy('p.nombre ASC')
    ->fetchAll();

// Con agregaciones
$total = $this->fluent()
    ->from('kardex')
    ->where('producto_id = ?', $id)
    ->select('SUM(cantidad) as total')
    ->fetch();
```

### INSERT

```php
$id = $this->fluent()
    ->insertInto('productos', [
        'nombre' => 'Producto X',
        'codigo' => 'PROD-001',
        'estado' => 1
    ])
    ->execute();
```

### UPDATE

```php
$this->fluent()
    ->update('productos')
    ->set(['nombre' => 'Nuevo Nombre'])
    ->where('id = ?', $id)
    ->execute();
```

### DELETE

```php
$this->fluent()
    ->deleteFrom('productos')
    ->where('id = ?', $id)
    ->execute();
```

## Sistema ACL

El sistema de permisos usa `$_SESSION['Controladores_aceptados']`.

### Estructura de Permisos

```php
$_SESSION['Controladores_aceptados'] = [
    'Producto' => [
        'index' => [
            'crear'       => '1',  // Puede crear
            'editar'      => '1',  // Puede editar
            'borrar'      => '0',  // NO puede borrar
            'ver_todos'   => '1',  // Ve todos los registros
            'admin'       => '0'   // NO es admin
        ]
    ]
];
```

### Validación en Controllers

```php
public function create(): void {
    // Verificar autenticación
    $this->requireAuth();
    
    // Verificar permiso específico
    if (!$this->can('crear')) {
        $this->json(['error' => 'Sin permisos'], 403);
        return;
    }
    
    // Continuar con lógica...
}
```

## Sistema de Filtros

Usa el trait `HasFilters` para búsquedas avanzadas.

```php
use Core\Traits\HasFilters;

class ProductoController extends Controller {
    use HasFilters;
    
    public function __construct() {
        parent::__construct();
        $this->initFilters('Producto', 'index');
    }
    
    public function index(): void {
        // Definir filtros
        $this->addFilter([
            'FName'   => 'f_nombre',      // Nombre del campo en form
            'TSeach'  => 'LIKE',          // Tipo de búsqueda
            'DBName'  => 'productos.nombre'  // Campo en BD
        ]);
        
        // Aplicar filtros al query
        $query = $this->repository->getQueryWithJoins();
        $query = $this->applyFilters($query);
        
        $productos = $query->fetchAll();
    }
}
```

## Convenciones

### Nombres

- Controllers: `ProductoController.php` (singular, PascalCase)
- Repositories: `ProductoRepository.php` (singular)
- Vistas: `producto/index.php` (minúsculas, plural de carpeta)
- Tablas: `productos` (plural, snake_case)

### Estructura de Archivos

```
app/
├── Controllers/
│   └── ProductoController.php
├── Models/
│   └── Repository/
│       └── ProductoRepository.php
└── Views/
    └── producto/
        ├── index.php
        ├── create.php
        └── edit.php
```

## Ejemplo Completo

```php
<?php
// Repository
namespace App\Models\Repository;
use Core\Model;

class ProductoRepository extends Model {
    protected string $table = 'productos';
    
    public function getWithCategoria() {
        return $this->fluent()
            ->from('productos p')
            ->leftJoin('categorias c ON c.id = p.categoria_id')
            ->select('p.*, c.nombre as categoria')
            ->where('p.estado = ?', 1)
            ->fetchAll();
    }
}

// Controller
namespace App\Controllers;
use Core\Controller;
use Core\Traits\HasFilters;
use App\Models\Repository\ProductoRepository;

class ProductoController extends Controller {
    use HasFilters;
    
    private ProductoRepository $repository;
    
    public function __construct() {
        parent::__construct();
        $this->repository = new ProductoRepository();
        $this->initFilters('Producto', 'index');
    }
    
    public function index(): void {
        $this->requireAuth();
        $this->requireAccess();
        
        $query = $this->repository->getWithCategoria();
        $query = $this->applyFilters($query);
        
        $productos = $query->fetchAll();
        
        $this->render('producto/index', [
            'productos' => $productos
        ]);
    }
    
    public function create(): void {
        $this->requireAuth();
        
        if (!$this->can('crear')) {
            $this->json(['error' => 'Sin permisos'], 403);
            return;
        }
        
        if ($this->isPost()) {
            $data = $this->post();
            
            try {
                $id = $this->repository->create([
                    'nombre' => $data['nombre'],
                    'codigo' => $data['codigo'],
                    'estado' => 1
                ]);
                
                $this->json(['Result' => '1', 'id' => $id]);
            } catch (\Exception $e) {
                $this->json(['Result' => '0', 'error' => $e->getMessage()]);
            }
        } else {
            $this->render('producto/create');
        }
    }
}
```

## Recursos

- [FluentPDO Documentation](https://github.com/fpdo/fluentpdo)
- [PSR-4 Autoloading](https://www.php-fig.org/psr/psr-4/)
