<?php
/**
 * Controlador de Kardex
 * Maneja el sistema de inventarios y movimientos de productos
 */

defined('EXECUTE') or die('Restricted access');

class KardexController extends ControladorBase {
    
    public $conectar;
    public $Adapter;
    public $AdapterModel;
     
    public function __construct() {
        parent::__construct();
        $this->conectar = new Conectar();
        $this->Adapter = $this->conectar->conexion();
        $this->AdapterModel = $this->conectar->startFluent();
    }
    
    /**
     * Listado de existencias de productos
     */
    public function Index() {
        
        $Datos = new KardexFpdoModel($this->AdapterModel);
        
        $productos = $Datos->fluent()
            ->from('productos')
            ->where('estado = ?', 1)
            ->fetchAll();
        
        $resultados = [];
        
        foreach ($productos as $producto) {
            
            $entradas = $Datos->fluent()
                ->from('kardex')
                ->where('producto_id = ?', $producto['id'])
                ->where('tipo = ?', 'entrada')
                ->select('SUM(cantidad) as total')
                ->fetch();
                
            $salidas = $Datos->fluent()
                ->from('kardex')
                ->where('producto_id = ?', $producto['id'])
                ->where('tipo = ?', 'salida')
                ->select('SUM(cantidad) as total')
                ->fetch();
            
            $existencia = ($entradas['total'] ?? 0) - ($salidas['total'] ?? 0);
            $costoPromedio = $this->calcularCostoPromedio($producto['id']);
            
            $resultados[] = [
                'producto_id'   => $producto['id'],
                'producto'      => $producto['nombre'],
                'codigo'        => $producto['codigo'],
                'existencia'    => $existencia,
                'costo'         => $costoPromedio,
                'valor_total'   => $existencia * $costoPromedio
            ];
        }
        
        $this->view('Kardex/IndexView', [
            'datos' => $resultados,
            'total_productos' => count($resultados)
        ]);
    }
    
    /**
     * Calcula el costo promedio de un producto
     */
    private function calcularCostoPromedio($producto_id) {
        
        $Datos = new KardexFpdoModel($this->AdapterModel);
        
        $movimientos = $Datos->fluent()
            ->from('kardex')
            ->where('producto_id = ?', $producto_id)
            ->where('tipo = ?', 'entrada')
            ->orderBy('fecha DESC')
            ->fetchAll();
        
        $suma_total = 0;
        $cantidad_total = 0;
        
        foreach ($movimientos as $mov) {
            $suma_total += ($mov['precio_unitario'] * $mov['cantidad']);
            $cantidad_total += $mov['cantidad'];
        }
        
        $promedio = $cantidad_total > 0 ? $suma_total / $cantidad_total : 0;
        
        return round($promedio, 2);
    }
    
    /**
     * Reporte detallado por bodega
     */
    public function ReporteDetallado() {
        
        $Datos = new KardexFpdoModel($this->AdapterModel);
        
        $productos = $Datos->fluent()->from('productos')->where('estado = ?', 1)->fetchAll();
        $bodegas = $Datos->fluent()->from('bodegas')->where('estado = ?', 1)->fetchAll();
        
        $reporte = [];
        
        foreach ($productos as $producto) {
            foreach ($bodegas as $bodega) {
                
                $existencia = $Datos->fluent()
                    ->from('kardex')
                    ->where('producto_id = ?', $producto['id'])
                    ->where('bodega_id = ?', $bodega['id'])
                    ->select('SUM(CASE WHEN tipo = "entrada" THEN cantidad ELSE -cantidad END) as total')
                    ->fetch();
                
                $reporte[] = [
                    'producto' => $producto['nombre'],
                    'bodega' => $bodega['nombre'],
                    'existencia' => $existencia['total'] ?? 0
                ];
            }
        }
        
        $this->view('Kardex/ReporteDetallado', ['datos' => $reporte]);
    }
}
