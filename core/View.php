<?php
namespace Core;

use Core\TemplateEngine;
use Core\FormBuilder;
use Core\HtmlView;

/**
 * View - Motor de vistas compatible con sistema legacy
 *
 * Inyecta automáticamente en las vistas:
 * - $Layout (TemplateEngine) - Para templates .tpl
 * - $Helper (AyudaVistasCls3w) - Para ACL y permisos
 * - $Frmbuilder (FormBuilder) - Para formularios dinámicos
 * - $HtmlView (HtmlView) - Para componentes HTML (tablas, menús, gráficos)
 *
 * @package Core
 */
class View {

    private string $viewsPath;

    public function __construct() {
        $this->viewsPath = BASE_PATH . '/app/Views/';
    }

    /**
     * Renderizar vista con helpers inyectados
     *
     * Las vistas tienen acceso automático a:
     * - $Layout: TemplateEngine para usar .tpl
     * - $Helper: Helpers de ACL y permisos
     * - $Frmbuilder: Constructor de formularios
     * - $HtmlView: Componentes HTML (tablas, menús, gráficos)
     * - Todas las variables pasadas en $data
     */
    public function render(string $view, array $data = []): void {
        $viewFile = $this->viewsPath . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new \Exception("Vista no encontrada: {$view}");
        }

        // Inyectar helpers en las vistas (compatibilidad legacy)
        $Layout = new TemplateEngine();
        $Frmbuilder = new FormBuilder();
        $HtmlView = new HtmlView();

        // Helper requiere las clases legacy
        if (!class_exists('AyudaVistasCls3w', false)) {
            require_once BASE_PATH . '/legacy/core/AyudaVistasCls3w.php';
        }
        $Helper = new \AyudaVistasCls3w();

        // Extraer variables del data
        extract($data);

        // Capturar contenido
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Renderizar con layout si existe
        if (isset($data['layout']) && $data['layout']) {
            $this->renderWithLayout($content, $data);
        } else {
            echo $content;
        }
    }

    /**
     * Renderizar con layout
     */
    private function renderWithLayout(string $content, array $data): void {
        $layoutFile = $this->viewsPath . '../layouts/' . ($data['layout'] ?? 'main') . '.php';

        if (file_exists($layoutFile)) {
            extract($data);
            require $layoutFile;
        } else {
            echo $content;
        }
    }
}
