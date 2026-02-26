<?php

namespace App\Controllers;

use App\Core\ControladorBase;
use App\Models\KardexFpdoModel;
use App\Core\Conectar;

/**
 * Controlador de Kardex Refactorizado
 * Maneja el sistema de inventarios y movimientos de productos sin sufrir el problema de N+1 queries.
 * Desarrollado mediante estándares PSR-4.
 */
class KardexController extends ControladorBase
{
    public Conectar $conectar;
    public $Adapter;
    public $AdapterModel;
    private KardexFpdoModel $kardexModel;

    public function __construct()
    {
        parent::__construct();
        $this->conectar = new Conectar();
        $this->Adapter = $this->conectar->conexion();
        $this->AdapterModel = $this->conectar->startFluent();
        
        // Se instancia una sola vez el modelo
        $this->kardexModel = new KardexFpdoModel($this->AdapterModel);
    }

    /**
     * Refactorizado: N+1 queries eliminadas. Reducido a solo 2 queries.
     * Complejidad de Operación en DB bajó de O(N) a O(1), mientras que PHP se maneja en O(N) lineal.
     */
    public function index(): void
    {
        // Consulta #1: Obtener todos los productos activos
        $productos = $this->kardexModel->fluent()
            ->from('productos')
            ->where('estado = ?', 1)
            ->fetchAll();

        if (empty($productos)) {
            $this->view('Kardex/IndexView', [
                'datos' => [],
                'total_productos' => 0
            ]);
            return;
        }

        // Consulta #2: Obtener consolidado global de kardex agrupado por tipo y producto_id ("group by logic").
        $resumenKardex = $this->kardexModel->fluent()
            ->from('kardex')
            ->select(null)
            ->select('producto_id')
            ->select('SUM(CASE WHEN tipo = "entrada" THEN cantidad ELSE 0 END) as total_entradas')
            ->select('SUM(CASE WHEN tipo = "salida" THEN cantidad ELSE 0 END) as total_salidas')
            ->select('SUM(CASE WHEN tipo = "entrada" THEN (precio_unitario * cantidad) ELSE 0 END) as valor_total_historico')
            ->groupBy('producto_id')
            ->fetchAll();

        // Creamos un Diccionario Map indexado en PHP, permitiendo acceso hash O(1).
        $kardexMap = [];
        if ($resumenKardex) {
            foreach ($resumenKardex as $kardexItem) {
                $kardexMap[$kardexItem['producto_id']] = $kardexItem;
            }
        }

        $resultados = [];

        // Procesamiento e iteración final en el arreglo matriz.
        foreach ($productos as $producto) {
            $id = $producto['id'];
            
            $datosKardex = $kardexMap[$id] ?? null;

            $entradas     = $datosKardex ? (float)$datosKardex['total_entradas'] : 0.0;
            $salidas      = $datosKardex ? (float)$datosKardex['total_salidas'] : 0.0;
            $valorEntradas= $datosKardex ? (float)$datosKardex['valor_total_historico'] : 0.0;

            // Existencias neta real instantánea
            $existencia = $entradas - $salidas;

            // Cálculo matemático legado de Costo Promedio para sostener compatibilidad hereditaria. 
            $costoPromedio = ($entradas > 0) ? ($valorEntradas / $entradas) : 0;
            $costoPromedio = round($costoPromedio, 2);

            $resultados[] = [
                'producto_id'   => $id,
                'producto'      => $producto['nombre'],
                'codigo'        => $producto['codigo'] ?? '',
                'existencia'    => $existencia,
                'costo'         => $costoPromedio,
                'valor_total'   => $existencia * $costoPromedio
            ];
        }

        $this->view('Kardex/IndexView', [
            'datos'           => $resultados,
            'total_productos' => count($resultados)
        ]);
    }
}
