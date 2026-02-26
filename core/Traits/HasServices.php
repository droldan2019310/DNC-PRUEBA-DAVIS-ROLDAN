<?php
namespace Core\Traits;

use Core\Services\FileService;
use Core\Services\WorkflowService;
use Core\Services\FacturaService;
use Core\Services\ContabilidadService;
use Core\Services\KardexService;
use Core\Services\PosicionService;
use Core\Services\CatacionService;
use Core\Services\ApiService;
use Core\Services\MetaIntegrationService;
use Core\Services\CartService;
use Core\Services\ChartService;
use Core\Services\KardexDinaService;

/**
 * Trait HasServices
 *
 * Proporciona acceso a los servicios de lógica de negocio
 * en los controladores PSR-4.
 *
 * Uso en controladores:
 * ```php
 * use Core\Traits\HasServices;
 *
 * class MiController extends Controller {
 *     use HasServices;
 *
 *     public function create() {
 *         // Usar servicios
 *         $this->fileService()->upload('archivo', 'productos');
 *         $this->kardexService()->registrarEntrada($datos);
 *         $this->contabilidadService()->generarPartida($partida);
 *         $this->posicionService()->obtenerPosicionDia();
 *     }
 * }
 * ```
 *
 * @package Core\Traits
 */
trait HasServices {

    private ?FileService $fileServiceInstance = null;
    private ?WorkflowService $workflowServiceInstance = null;
    private ?FacturaService $facturaServiceInstance = null;
    private ?ContabilidadService $contabilidadServiceInstance = null;
    private ?KardexService $kardexServiceInstance = null;
    private ?PosicionService $posicionServiceInstance = null;
    private ?CatacionService $catacionServiceInstance = null;
    private ?ApiService $apiServiceInstance = null;
    private ?MetaIntegrationService $metaIntegrationServiceInstance = null;
    private ?CartService $cartServiceInstance = null;
    private ?ChartService $chartServiceInstance = null;
    private ?KardexDinaService $kardexDinaServiceInstance = null;

    /**
     * Obtener servicio de archivos
     *
     * @return FileService
     */
    protected function fileService(): FileService {
        if ($this->fileServiceInstance === null) {
            $this->fileServiceInstance = new FileService();
        }
        return $this->fileServiceInstance;
    }

    /**
     * Obtener servicio de workflow
     *
     * @return WorkflowService
     */
    protected function workflowService(): WorkflowService {
        if ($this->workflowServiceInstance === null) {
            $this->workflowServiceInstance = new WorkflowService();
        }
        return $this->workflowServiceInstance;
    }

    /**
     * Obtener servicio de facturación
     *
     * @return FacturaService
     */
    protected function facturaService(): FacturaService {
        if ($this->facturaServiceInstance === null) {
            $this->facturaServiceInstance = new FacturaService();
        }
        return $this->facturaServiceInstance;
    }

    /**
     * Obtener servicio de contabilidad
     *
     * @return ContabilidadService
     */
    protected function contabilidadService(): ContabilidadService {
        if ($this->contabilidadServiceInstance === null) {
            $this->contabilidadServiceInstance = new ContabilidadService();
        }
        return $this->contabilidadServiceInstance;
    }

    /**
     * Obtener servicio de kardex/inventarios
     *
     * @return KardexService
     */
    protected function kardexService(): KardexService {
        if ($this->kardexServiceInstance === null) {
            $this->kardexServiceInstance = new KardexService();
        }
        return $this->kardexServiceInstance;
    }

    /**
     * Obtener servicio de posición de contratos
     *
     * @return PosicionService
     */
    protected function posicionService(): PosicionService {
        if ($this->posicionServiceInstance === null) {
            $this->posicionServiceInstance = new PosicionService();
        }
        return $this->posicionServiceInstance;
    }

    /**
     * Obtener servicio de catación de café
     *
     * @return CatacionService
     */
    protected function catacionService(): CatacionService {
        if ($this->catacionServiceInstance === null) {
            $this->catacionServiceInstance = new CatacionService();
        }
        return $this->catacionServiceInstance;
    }

    /**
     * Obtener servicio de API
     *
     * @return ApiService
     */
    protected function apiService(): ApiService {
        if ($this->apiServiceInstance === null) {
            $this->apiServiceInstance = new ApiService();
        }
        return $this->apiServiceInstance;
    }

    /**
     * Obtener servicio de integración con Meta/WhatsApp
     *
     * @return MetaIntegrationService
     */
    protected function metaIntegrationService(): MetaIntegrationService {
        if ($this->metaIntegrationServiceInstance === null) {
            $this->metaIntegrationServiceInstance = new MetaIntegrationService();
        }
        return $this->metaIntegrationServiceInstance;
    }

    /**
     * Obtener servicio de carrito de compras
     *
     * @return CartService
     */
    protected function cartService(): CartService {
        if ($this->cartServiceInstance === null) {
            $this->cartServiceInstance = new CartService();
        }
        return $this->cartServiceInstance;
    }

    /**
     * Obtener servicio de generación de gráficos
     *
     * @return ChartService
     */
    protected function chartService(): ChartService {
        if ($this->chartServiceInstance === null) {
            $this->chartServiceInstance = new ChartService();
        }
        return $this->chartServiceInstance;
    }

    /**
     * Obtener servicio de kardex dinámico por bodega
     *
     * @return KardexDinaService
     */
    protected function kardexDinaService(): KardexDinaService {
        if ($this->kardexDinaServiceInstance === null) {
            $this->kardexDinaServiceInstance = new KardexDinaService();
        }
        return $this->kardexDinaServiceInstance;
    }
}
