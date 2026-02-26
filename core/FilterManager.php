<?php
namespace Core;

/**
 * FilterManager - Gestor de Filtros
 * Extrae la lógica de filtrado del ControladorBase legacy
 * Compatible con FluentPDO y query builders modernos
 *
 * @package Core
 */
class FilterManager {

    private array $filters = [];
    private string $sessionPrefix;

    /**
     * Constructor
     */
    public function __construct(string $controller = '', string $action = '') {
        $this->sessionPrefix = ($controller ?: ($_SESSION['CTRL'] ?? 'default')) . '-' .
                               ($action ?: ($_SESSION['ACT'] ?? 'index'));
    }

    /**
     * Agregar definición de filtro
     * Compatible con Add_Filter() legacy
     *
     * @param array $config Configuración del filtro
     *   - FName: Nombre del campo en el formulario
     *   - TSeach: Tipo de búsqueda (=, LIKE, FROM, TO, DATE, etc)
     *   - DBName: Nombre del campo en la BD
     *   - Default: Valor por defecto
     */
    public function addFilter(array $config): void {
        $defaults = [
            'FName'    => 'cForm_nombre',
            'TSeach'   => '=',
            'DBName'   => 'nombre',
            'Default'  => ''
        ];

        $filter = array_merge($defaults, $config);
        $this->filters[] = $filter;
    }

    /**
     * Obtener filtros activos desde REQUEST/SESSION
     * Compatible con Return_Filter() legacy
     */
    public function getActiveFilters(array $options = []): array {
        $defaults = [
            'OMTNames'    => [],  // Omitir por nombre de formulario
            'OMTDBNames'  => [],  // Omitir por nombre de BD
        ];

        $options = array_merge($defaults, $options);
        $activeFilters = [];

        // Determinar filtros a aplicar
        $filtersToApply = $this->getApplicableFilters($options);

        // Verificar si se envió formulario
        $sessionKey = $this->sessionPrefix;

        if (isset($_POST['SeachType']) || isset($_GET['SeachType'])) {
            $searchType = $_POST['SeachType'] ?? $_GET['SeachType'];
            $_SESSION[$sessionKey] = $searchType;

            if ($searchType === 'Buscar') {
                // Procesar filtros del formulario
                foreach ($filtersToApply as $filter) {
                    $value = $this->getFilterValue($filter);

                    if ($value !== '' || $filter['Default'] !== '') {
                        $filter['Fvalue'] = $value ?: $filter['Default'];
                        $activeFilters[] = $filter;
                    }
                }
            } else {
                // Limpiar filtros
                $this->clearFilters($filtersToApply);
            }
        } elseif (isset($_SESSION[$sessionKey]) && $_SESSION[$sessionKey] !== '') {
            // Recuperar filtros de sesión
            foreach ($filtersToApply as $filter) {
                $value = $this->getFilterValue($filter);

                if ($value !== '' || $filter['Default'] !== '') {
                    $filter['Fvalue'] = $value ?: $filter['Default'];
                    $activeFilters[] = $filter;
                }
            }
        }

        return $activeFilters;
    }

    /**
     * Aplicar filtros a query FluentPDO
     * Compatible con ApplyFilter() legacy
     */
    public function applyToQuery($query, array $options = []) {
        $defaults = [
            'Table'      => '',
            'ACLFilter'  => false,
            'DeptoName'  => '',
            'DeptoVal'   => '',
        ];

        $options = array_merge($defaults, $options);

        // Obtener filtros activos
        $activeFilters = $this->getActiveFilters($options);

        if (!empty($activeFilters)) {
            foreach ($activeFilters as $filter) {
                $query = $this->applyFilterCondition($query, $filter);
            }
        }

        // Aplicar filtros ACL si es necesario
        if ($options['ACLFilter']) {
            $query = $this->applyAclFilter($query, $options);
        }

        return $query;
    }

    /**
     * Aplicar condición de filtro según tipo
     */
    private function applyFilterCondition($query, array $filter) {
        $dbName = $filter['DBName'];
        $value = $filter['Fvalue'];

        switch ($filter['TSeach']) {
            case 'LIKE':
                $query->where($dbName . ' LIKE ?', "%{$value}%");
                break;

            case 'LIKEI':
                $query->where($dbName . ' LIKE ?', "{$value}%");
                break;

            case 'FROM':
                $query->where($dbName . ' >= ?', $value);
                break;

            case 'TO':
                $query->where($dbName . ' <= ?', $value);
                break;

            case 'DATEF':
                $query->where("DATE({$dbName}) >= ?", $value);
                break;

            case 'DATET':
                $query->where("DATE({$dbName}) <= ?", $value);
                break;

            case 'DATE':
                $query->where("DATE({$dbName}) = ?", $value);
                break;

            case 'MONTH':
                $query->where("MONTH({$dbName}) = ?", $value);
                break;

            case 'YEAR':
                $query->where("YEAR({$dbName}) = ?", $value);
                break;

            case 'OR':
                if (is_array($value)) {
                    $conditions = array_map(fn($v) => "{$dbName} = '{$v}'", $value);
                    $query->where('(' . implode(' OR ', $conditions) . ')');
                }
                break;

            case 'ORLIKE':
                if (is_array($value)) {
                    $conditions = array_map(fn($v) => "{$dbName} LIKE '%{$v}%'", $value);
                    $query->where('(' . implode(' OR ', $conditions) . ')');
                }
                break;

            case 'NULL':
                $condition = ($value == '1') ? 'IS NOT NULL' : 'IS NULL';
                $query->where("{$dbName} {$condition}");
                break;

            default:
                // Operador igual o personalizado
                $query->where("{$dbName} {$filter['TSeach']} ?", $value);
                break;
        }

        return $query;
    }

    /**
     * Obtener valor de filtro desde REQUEST/SESSION
     */
    private function getFilterValue(array $filter) {
        $fieldName = str_replace('[]', '', $filter['FName']);
        $sessionKey = $this->sessionPrefix . '-' . $filter['FName'];

        // Prioridad: POST > GET > SESSION
        if (isset($_POST[$fieldName])) {
            $value = $_POST[$fieldName];
        } elseif (isset($_GET[$fieldName])) {
            $value = $_GET[$fieldName];
        } elseif (isset($_SESSION[$sessionKey])) {
            return $_SESSION[$sessionKey];
        } else {
            return '';
        }

        // Procesar valor según tipo
        if (is_array($value)) {
            $value = $this->sanitizeArray($value);
        } elseif (strpos($fieldName, 'date_') === 0) {
            // Campo de fecha
            if ($value !== '') {
                $date = new \DateTime($value);
                $value = $date->format('Y-m-d');
            }
        } else {
            $value = $this->sanitizeString($value);
        }

        // Guardar en sesión
        $_SESSION[$sessionKey] = $value;

        return is_array($value) ? $value : (string)$value;
    }

    /**
     * Limpiar filtros de sesión
     */
    private function clearFilters(array $filters): void {
        foreach ($filters as $filter) {
            $sessionKey = $this->sessionPrefix . '-' . $filter['FName'];
            unset($_SESSION[$sessionKey]);
        }

        unset($_SESSION[$this->sessionPrefix]);
    }

    /**
     * Obtener filtros aplicables (excluyendo omitidos)
     */
    private function getApplicableFilters(array $options): array {
        $applicable = [];

        foreach ($this->filters as $filter) {
            $skip = false;

            if (!empty($options['OMTNames']) &&
                in_array($filter['FName'], $options['OMTNames'])) {
                $skip = true;
            }

            if (!empty($options['OMTDBNames']) &&
                in_array($filter['DBName'], $options['OMTDBNames'])) {
                $skip = true;
            }

            if (!$skip) {
                $applicable[] = $filter;
            }
        }

        return $applicable;
    }

    /**
     * Aplicar filtros de ACL (permisos)
     */
    private function applyAclFilter($query, array $options) {
        $table = $options['Table'];
        $controller = $options['Ctrl'] ?? $_SESSION['CTRL'] ?? '';

        // Verificar permisos
        $isAdmin = $this->checkPermission($controller, 'admin');
        $canViewAll = $this->checkPermission($controller, 'ver_todos');
        $canViewDept = $this->checkPermission($controller, 'ver_dep');

        if ($isAdmin) {
            return $query;
        } elseif ($canViewAll) {
            return $query;
        } elseif ($canViewDept && !empty($options['DeptoName'])) {
            $query->where("{$table}.{$options['DeptoName']} = ?", $options['DeptoVal']);
        } else {
            $userId = $_SESSION['Id_Usuario'] ?? 0;
            $query->where("{$table}.creado_id = ?", $userId);
        }

        return $query;
    }

    /**
     * Verificar permiso de usuario
     */
    private function checkPermission(string $controller, string $permission): bool {
        $action = $_SESSION['ACT'] ?? 'index';

        if (!isset($_SESSION['Controladores_aceptados'][$controller][$action])) {
            return false;
        }

        return ($_SESSION['Controladores_aceptados'][$controller][$action][$permission] ?? 0) == 1;
    }

    /**
     * Sanitizar string (básico)
     */
    private function sanitizeString(string $value): string {
        return trim(strip_tags($value));
    }

    /**
     * Sanitizar array
     */
    private function sanitizeArray(array $values): array {
        return array_map([$this, 'sanitizeString'], $values);
    }
}
