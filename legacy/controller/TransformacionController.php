<?php
/**
 * Controlador de Transformaciones
 * Maneja el proceso de transformación de productos (ej: café cereza a pergamino)
 */

defined('EXECUTE') or die('Restricted access');

class TransformacionController extends ControladorBase {
    
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
     * Procesa una transformación de producto
     * 
     * Registra la transformación de un producto a otro
     * Por ejemplo: Café cereza → Café pergamino
     */
    public function ProcesarTransformacion() {
        
        if (!$this->ValToken()) {
            echo json_encode([
                'Result' => '0',
                'Error' => 'Token inválido'
            ]);
            return;
        }
        
        $Post = $this->RenderPost(['BtnGrabar', 'ToKen']);
        
        $producto_entrada = $Post['Val'][0];
        $cantidad_entrada = $Post['Val'][1];
        $producto_salida = $Post['Val'][2];
        $bodega_id = $Post['Val'][3];
        
        $Datos = new KardexFpdoModel($this->AdapterModel);
        
        // Registrar entrada de materia prima
        $result1 = $Datos->fluent()->insertInto('kardex', [
            'producto_id' => $producto_entrada,
            'cantidad' => $cantidad_entrada,
            'tipo' => 'entrada',
            'bodega_id' => $bodega_id,
            'fecha' => date('Y-m-d H:i:s'),
            'usuario_id' => $_SESSION['User_ID']
        ])->execute();
        
        // Registrar salida de producto transformado
        $result2 = $Datos->fluent()->insertInto('kardex', [
            'producto_id' => $producto_salida,
            'cantidad' => $cantidad_entrada,
            'tipo' => 'salida',
            'bodega_id' => $bodega_id,
            'fecha' => date('Y-m-d H:i:s'),
            'usuario_id' => $_SESSION['User_ID']
        ])->execute();
        
        if ($result1 && $result2) {
            echo json_encode([
                'Result' => '1',
                'Message' => 'Transformación procesada exitosamente'
            ]);
        } else {
            echo json_encode([
                'Result' => '0',
                'Error' => 'Error al procesar transformación'
            ]);
        }
    }
    
    /**
     * Obtiene el factor de rendimiento entre dos productos
     */
    private function obtenerRendimiento($producto_entrada_id, $producto_salida_id) {
        
        $Datos = new TransformacionTipoModel($this->AdapterModel);
        
        $rendimiento = $Datos->fluent()
            ->from('transformacion_tipo')
            ->where('producto_entrada_id = ?', $producto_entrada_id)
            ->where('producto_salida_id = ?', $producto_salida_id)
            ->select('rendimiento')
            ->fetch();
        
        return $rendimiento['rendimiento'] ?? 0.85;
    }
}
