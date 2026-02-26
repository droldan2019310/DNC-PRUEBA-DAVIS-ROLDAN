<?php
declare(strict_types=1);

namespace Core;

use Core\Traits\HasViewHelpers;
use Core\Traits\HasServices;

/**
 * Controller - Clase base para controladores modernos PSR-4
 * Actualizado para PHP 8.1+
 *
 * Incluye automáticamente:
 * - Request/Response handlers
 * - View rendering
 * - Session management
 * - View helpers (ACL, botones, etc.)
 * - Business services (File, Workflow, Factura, Contabilidad, Kardex)
 *
 * @package Core
 */
class Controller {

    use HasViewHelpers;
    use HasServices;

    protected Request $request;
    protected Response $response;
    protected View $view;
    protected Session $session;

    public function __construct() {
        $this->request = new Request();
        $this->response = new Response();
        $this->view = new View();
        $this->session = new Session();

        // Inicializar view helpers automáticamente
        $this->initViewHelpers();
    }

    /**
     * Renderizar vista
     */
    protected function render(string $view, array $data = []): void {
        $this->view->render($view, $data);
    }

    /**
     * Redireccionar
     */
    protected function redirect(string $url, int $code = 302): void {
        $this->response->redirect($url, $code);
    }

    /**
     * Respuesta JSON
     */
    protected function json(array $data, int $code = 200): void {
        $this->response->json($data, $code);
    }

    // ============================================
    // MÉTODOS DE TIPO DE RESPUESTA (Paridad con Legacy)
    // ============================================

    /**
     * Establece headers para respuesta JSON
     * Uso: $this->setJsonResponse();
     */
    protected function setJsonResponse(): void {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    }

    /**
     * Establece headers para respuesta HTML
     * Uso: $this->setHtmlResponse();
     */
    protected function setHtmlResponse(): void {
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: private, max-age=0');
    }

    /**
     * Retorna respuesta JSON y termina ejecución
     * Alias de json() para paridad con ControladorBase legacy
     * Uso: $this->jsonResponse(['Result' => '1', 'Data' => $data]);
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        $this->setJsonResponse();
        echo json_encode($data);
        exit;
    }

    /**
     * Retorna error JSON y termina ejecución
     * Uso: $this->jsonError('Error message', 500);
     */
    protected function jsonError(string $message, int $code = 500): void {
        $this->jsonResponse([
            'Result' => '0',
            'Error' => $message,
            'Code' => $code
        ], $code);
    }

    // ============================================
    // MÉTODOS DE REQUEST
    // ============================================

    /**
     * Verificar si la petición es POST
     */
    protected function isPost(): bool {
        return $this->request->isPost();
    }

    /**
     * Verificar si la petición es GET
     */
    protected function isGet(): bool {
        return $this->request->isGet();
    }

    /**
     * Obtener datos POST
     */
    protected function post(string|null $key = null, mixed $default = null): mixed {
        return $this->request->post($key, $default);
    }

    /**
     * Obtener datos GET
     */
    protected function get(string|null $key = null, mixed $default = null): mixed {
        return $this->request->get($key, $default);
    }

    // ============================================
    // MÉTODOS DE CONTROL DE ACCESO (ACL)
    // ============================================

    /**
     * Verificar si el usuario está autenticado
     *
     * @return bool
     */
    protected function isAuthenticated(): bool {
        return ACL::isAuthenticated();
    }

    /**
     * Requerir autenticación - Redirige a login si no está autenticado
     *
     * @param string $redirectTo URL de redirección
     * @return void
     */
    protected function requireAuth(string $redirectTo = '/index.php?controller=Login&action=index'): void {
        ACL::requireAuth($redirectTo);
    }

    /**
     * Verificar si el usuario tiene acceso a este controlador
     *
     * @param string|null $controller Nombre del controlador (null = usar nombre actual)
     * @return bool
     */
    protected function canAccess(string|null $controller = null): bool {
        if ($controller === null) {
            $controller = $this->getControllerName();
        }
        return ACL::canAccessController($controller);
    }

    /**
     * Requerir acceso al controlador actual - Redirige si no tiene acceso
     *
     * @param string|null $controller Nombre del controlador (null = usar nombre actual)
     * @param string $redirectTo URL de redirección
     * @return void
     */
    protected function requireAccess(string|null $controller = null, string $redirectTo = '/index.php?controller=Crm&action=index'): void {
        if ($controller === null) {
            $controller = $this->getControllerName();
        }
        ACL::requireController($controller, $redirectTo);
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     *
     * @param string $permission Permiso (crear, editar, borrar, aprobar, ver_propios, ver_todos, admin)
     * @param string|null $controller Nombre del controlador (null = usar nombre actual)
     * @param string|null $action Acción específica
     * @return bool
     */
    protected function can(string $permission, string|null $controller = null, string|null $action = null): bool {
        if ($controller === null) {
            $controller = $this->getControllerName();
        }
        return ACL::hasPermission($controller, $permission, $action);
    }

    /**
     * Requerir un permiso específico - Redirige si no lo tiene
     *
     * @param string $permission Permiso requerido
     * @param string|null $controller Nombre del controlador (null = usar nombre actual)
     * @param string|null $action Acción específica
     * @param string $redirectTo URL de redirección
     * @return void
     */
    protected function requirePermission(
        string $permission,
        string|null $controller = null,
        string|null $action = null,
        string $redirectTo = '/index.php?controller=Crm&action=index'
    ): void {
        if ($controller === null) {
            $controller = $this->getControllerName();
        }
        ACL::requirePermission($controller, $permission, $action, $redirectTo);
    }

    /**
     * Verificar si el usuario es administrador del controlador
     *
     * @param string|null $controller Nombre del controlador (null = usar nombre actual)
     * @return bool
     */
    protected function isAdmin(string|null $controller = null): bool {
        if ($controller === null) {
            $controller = $this->getControllerName();
        }
        return ACL::isAdmin($controller);
    }

    /**
     * Obtener información del usuario actual
     *
     * @return array<string, mixed>|null
     */
    protected function currentUser(): array|null {
        return ACL::getCurrentUser();
    }

    /**
     * Obtener ID del usuario actual
     *
     * @return int|null
     */
    protected function userId(): int|null {
        return ACL::getUserId();
    }

    /**
     * Obtener todos los permisos del controlador actual
     *
     * @param string|null $controller Nombre del controlador (null = usar nombre actual)
     * @param string|null $action Acción específica
     * @return array<string, mixed>|null
     */
    protected function getPermissions(string|null $controller = null, string|null $action = null): array|null {
        if ($controller === null) {
            $controller = $this->getControllerName();
        }
        return ACL::getPermissions($controller, $action);
    }

    /**
     * Obtener el nombre del controlador actual (sin namespace ni "Controller")
     *
     * @return string
     */
    protected function getControllerName(): string {
        $className = get_class($this);
        // Remover namespace
        $className = basename(str_replace('\\', '/', $className));
        // Remover "Controller" del final
        $className = str_replace('Controller', '', $className);
        return $className;
    }
}
