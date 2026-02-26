<?php

namespace App\Services;

use App\Models\KardexFpdoModel;
use App\Models\TransformacionModel; 
use PDOException;
use Exception;

/**
 * Servicio de Transformaciones PSR-4
 * Corrige el bug crítico de producción manejando correctamente:
 * 1. Entradas y Salidas invertidas lógicamente.
 * 2. Aplicación de mermas y rendimientos.
 * 3. Cálculos de Costos del producto final basándose en el costo de la materia prima.
 */
class TransformacionService
{
    private KardexFpdoModel $kardexModel;
    private $pdo; 

    public function __construct(KardexFpdoModel $kardexModel, $pdo)
    {
        $this->kardexModel = $kardexModel;
        $this->pdo = $pdo;
    }

    public function procesarTransformacion(array $datosTransformacion): bool
    {
        $productoEntradaId = $datosTransformacion['producto_entrada_id']; 
        $cantidadMateriaPrima = (float)$datosTransformacion['cantidad_entrada']; 
        $productoSalidaId = $datosTransformacion['producto_salida_id']; 
        $bodegaId = $datosTransformacion['bodega_id'];
        $usuarioId = $datosTransformacion['usuario_id'];
        $fecha = date('Y-m-d H:i:s');

        try {
            // empieza transacción
            $this->pdo->beginTransaction();

            // Generaremos un ID de transformación virtual basado en tiempo
            $transformacionGlobalId = time() . random_int(10, 99); 

            // 2. Calcular el rendimiento y la cantidad real obtenida
            $rendimiento = $this->obtenerRendimiento($productoEntradaId, $productoSalidaId);
            $cantidadObtenida = $cantidadMateriaPrima * $rendimiento; 
            $merma = $cantidadMateriaPrima - $cantidadObtenida;

            // 3. Prorrateo de Costos
            $costoMateriaPrima = $this->obtenerCostoUnitarioActual($productoEntradaId, $bodegaId);
            $costoTotalInvertido = $cantidadMateriaPrima * $costoMateriaPrima;
            $costoUnitarioNuevoProducto = ($cantidadObtenida > 0) ? ($costoTotalInvertido / $cantidadObtenida) : 0;

            // 4. Salida de la materia prima
            $this->kardexModel->fluent()->insertInto('kardex', [
                'producto_id' => $productoEntradaId,
                'cantidad' => $cantidadMateriaPrima,
                'tipo' => 'salida',
                'bodega_id' => $bodegaId,
                'fecha' => $fecha,
                'usuario_id' => $usuarioId,
                'precio_unitario' => $costoMateriaPrima,
                'transformacion_id' => $transformacionGlobalId
            ])->execute();

            // 5. Entrada del producto limpio final
            $this->kardexModel->fluent()->insertInto('kardex', [
                'producto_id' => $productoSalidaId,
                'cantidad' => $cantidadObtenida,
                'tipo' => 'entrada',
                'bodega_id' => $bodegaId,
                'fecha' => $fecha,
                'usuario_id' => $usuarioId,
                'precio_unitario' => $costoUnitarioNuevoProducto, 
                'transformacion_id' => $transformacionGlobalId
            ])->execute();

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    private function obtenerRendimiento($productoEntradaId, $productoSalidaId): float
    {
        $rendimiento = $this->kardexModel->fluent()
            ->from('transformacion_tipo')
            ->where('producto_entrada_id = ?', $productoEntradaId)
            ->where('producto_salida_id = ?', $productoSalidaId)
            ->select('rendimiento')
            ->fetch();
        
        return isset($rendimiento['rendimiento']) ? (float)$rendimiento['rendimiento'] : 0.85;
    }

    private function obtenerCostoUnitarioActual($productoId, $bodegaId): float
    {
        $ultimoCosto = $this->kardexModel->fluent()
            ->from('kardex')
            ->where('producto_id = ?', $productoId)
            ->where('bodega_id = ?', $bodegaId)
            ->where('tipo = ?', 'entrada')
            ->select('precio_unitario')
            ->orderBy('fecha DESC')
            ->limit(1)
            ->fetch();

        return isset($ultimoCosto['precio_unitario']) ? (float)$ultimoCosto['precio_unitario'] : 0.0;
    }
}
