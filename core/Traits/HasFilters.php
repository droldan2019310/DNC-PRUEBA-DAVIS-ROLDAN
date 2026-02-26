<?php
namespace Core\Traits;

use Core\FilterManager;

/**
 * Trait HasFilters
 * Proporciona funcionalidad de filtros a controladores
 * Compatible con sintaxis legacy
 *
 * @package Core\Traits
 */
trait HasFilters {

    protected FilterManager $filterManager;

    /**
     * Inicializar gestor de filtros
     */
    protected function initFilters(string $controller = '', string $action = ''): void {
        $this->filterManager = new FilterManager($controller, $action);
    }

    /**
     * Agregar filtro (compatible con Add_Filter)
     */
    protected function addFilter(array $config): void {
        if (!isset($this->filterManager)) {
            $this->initFilters();
        }

        $this->filterManager->addFilter($config);
    }

    /**
     * Obtener filtros activos (compatible con Return_Filter)
     */
    protected function getFilters(array $options = []): array {
        if (!isset($this->filterManager)) {
            $this->initFilters();
        }

        return $this->filterManager->getActiveFilters($options);
    }

    /**
     * Aplicar filtros a query (compatible con ApplyFilter)
     */
    protected function applyFilters($query, array $options = []) {
        if (!isset($this->filterManager)) {
            $this->initFilters();
        }

        return $this->filterManager->applyToQuery($query, $options);
    }
}
