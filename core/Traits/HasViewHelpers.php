<?php
namespace Core\Traits;

/**
 * Trait para helpers de vistas (ACL, Botones, etc.)
 * Wrapper PSR-4 para AyudaVistasCls3w
 *
 * Uso en controladores:
 * use Core\Traits\HasViewHelpers;
 *
 * class MiController extends Controller {
 *     use HasViewHelpers;
 * }
 *
 * @package Core\Traits
 */
trait HasViewHelpers {

    private $viewHelper;

    /**
     * Inicializar helpers de vista
     * Se llama automáticamente en el constructor del Controller
     */
    protected function initViewHelpers(): void {
        if (!class_exists('AyudaVistasCls3w', false)) {
            require_once BASE_PATH . '/legacy/core/AyudaVistasCls3w.php';
        }
        $this->viewHelper = new \AyudaVistasCls3w();
    }

    /**
     * Verificar si el usuario tiene permiso
     *
     * @param string $controlador Nombre del controlador
     * @param string $permiso Tipo de permiso (crear, editar, ver, eliminar)
     * @return bool
     */
    protected function hasPermission(string $controlador, string $permiso = 'crear'): bool {
        return $this->viewHelper->getPermiso($controlador, $permiso);
    }

    /**
     * Obtener acceso del usuario a un módulo
     *
     * @param string $controlador Nombre del controlador
     * @return array|false Array con permisos o false si no tiene acceso
     */
    protected function getAccess(string $controlador) {
        return $this->viewHelper->GetAcceso($controlador);
    }

    /**
     * Verificar acceso a departamento
     *
     * @param int $departamentoId ID del departamento
     * @return bool
     */
    protected function hasDepartmentAccess(int $departamentoId): bool {
        return $this->viewHelper->AccesoDepartamento($departamentoId);
    }

    /**
     * Generar botón con control ACL para modal
     *
     * @param string $controlador Nombre del controlador
     * @param string $permiso Tipo de permiso
     * @param string $evento Evento a ejecutar
     * @param string $texto Texto del botón
     * @param string $icono Icono del botón
     * @param string $clase Clase CSS adicional
     * @return string HTML del botón o vacío si no tiene permiso
     */
    protected function aclButton(
        string $controlador,
        string $permiso = 'crear',
        string $evento = '',
        string $texto = 'Crear',
        string $icono = 'plus',
        string $clase = 'btn-primary'
    ): string {
        return $this->viewHelper->BtnAclMdl($controlador, $permiso, $evento, $texto, $icono, $clase);
    }

    /**
     * Generar botón con control ACL para URL
     *
     * @param string $controlador Nombre del controlador
     * @param string $permiso Tipo de permiso
     * @param string $url URL destino
     * @param string $texto Texto del botón
     * @param string $icono Icono del botón
     * @param string $clase Clase CSS adicional
     * @return string HTML del botón o vacío si no tiene permiso
     */
    protected function aclUrlButton(
        string $controlador,
        string $permiso = 'crear',
        string $url = '',
        string $texto = 'Crear',
        string $icono = 'plus',
        string $clase = 'btn-primary'
    ): string {
        return $this->viewHelper->BtnAclUrl($controlador, $permiso, $url, $texto, $icono, $clase);
    }

    /**
     * Generar botón con icono
     *
     * @param string $url URL destino
     * @param string $icono Icono Font Awesome
     * @param string $texto Texto del botón (opcional)
     * @param string $clase Clase CSS del botón
     * @return string HTML del botón
     */
    protected function iconButton(
        string $url,
        string $icono,
        string $texto = '',
        string $clase = 'btn-default'
    ): string {
        return $this->viewHelper->BotonUrlIcon($url, $icono, $texto, $clase);
    }

    /**
     * Lanzar error 403 si no tiene permiso
     *
     * @param string $controlador Nombre del controlador
     * @param string $permiso Tipo de permiso
     * @return void
     */
    protected function requirePermission(string $controlador, string $permiso = 'crear'): void {
        if (!$this->hasPermission($controlador, $permiso)) {
            $this->response->setStatusCode(403);
            $this->render('errors/403', [
                'message' => "No tiene permiso para {$permiso} en {$controlador}"
            ]);
            exit;
        }
    }

    /**
     * Middleware de ACL para controlador completo
     * Se puede llamar en __construct() para proteger todo el controlador
     *
     * @param string $controlador Nombre del controlador
     * @param array $permisos Permisos requeridos ['crear', 'editar', 'ver']
     * @return void
     */
    protected function protectController(string $controlador, array $permisos = ['ver']): void {
        $access = $this->getAccess($controlador);

        if (!$access) {
            $this->response->setStatusCode(403);
            $this->render('errors/403', [
                'message' => "No tiene acceso al módulo {$controlador}"
            ]);
            exit;
        }

        // Verificar permisos específicos
        foreach ($permisos as $permiso) {
            if (!$this->hasPermission($controlador, $permiso)) {
                $this->response->setStatusCode(403);
                $this->render('errors/403', [
                    'message' => "No tiene permiso para {$permiso} en {$controlador}"
                ]);
                exit;
            }
        }
    }

    /**
     * Acceso directo a la instancia legacy si se necesita
     *
     * @return \AyudaVistasCls3w
     */
    protected function getViewHelper(): \AyudaVistasCls3w {
        return $this->viewHelper;
    }
}
