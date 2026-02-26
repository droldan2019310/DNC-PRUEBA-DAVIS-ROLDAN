-- =========================================================================
-- MIGRACIÓN Y CORRECCIÓN DE BUG DE TRANSFORMACIONES (legacy)
-- Objetivo: Identificar los registros de kardex con el bug,
-- recalcular cantidades (merma) y unirlos bajos su transformacion_id
-- =========================================================================

START TRANSACTION;

-- 1. IDENTIFICACIÓN Y MARCAJE:
-- Para los registros corruptos del bug que NO tienen un transformacion_id asociado,
-- crearemos un generador temporal para parearlos si es necesario, pero lo ideal
-- es actualizar basándose en los ID pareados temporalmente.

-- a. Corregir salidas erróneas que debieron ser Entradas finales de Pergamino (85%).
UPDATE kardex k_salida
INNER JOIN kardex k_entrada ON 
    k_salida.fecha = k_entrada.fecha 
    AND k_salida.usuario_id = k_entrada.usuario_id 
    AND k_salida.bodega_id = k_entrada.bodega_id
    AND k_salida.cantidad = k_entrada.cantidad
SET 
  k_salida.tipo = 'entrada', -- Lo que salió como pergamino ahora entra
  k_salida.cantidad = (k_entrada.cantidad * 0.85), -- Rendimiento 85% Cereza a Pergamino
  -- Asignamos un transformacion_id arbitrario para emparejarlos en el historial
  k_salida.transformacion_id = k_entrada.id
WHERE 
  k_entrada.tipo = 'entrada' 
  AND k_entrada.producto_id = 45 -- solo materia prima: Café Cereza
  AND k_salida.tipo = 'salida'
  AND k_salida.producto_id = 67 -- solo producto obtenido: Café Pergamino
  AND k_salida.id != k_entrada.id
  AND k_salida.transformacion_id IS NULL;

-- b. Corregir entradas erróneas que debían ser las Salidas de Materia prima (100%).
UPDATE kardex k_entrada
INNER JOIN kardex k_producto_final ON 
    k_entrada.fecha = k_producto_final.fecha 
    AND k_entrada.usuario_id = k_producto_final.usuario_id
    AND k_entrada.bodega_id = k_producto_final.bodega_id
    AND k_producto_final.transformacion_id = k_entrada.id -- Usamos el ID de emparejamiento previo
SET 
  k_entrada.tipo = 'salida', -- Lo que entró de materia prima ahora sale del inventario
  k_entrada.transformacion_id = k_entrada.id -- El mismo ID para agrupar ambos movimientos de kardex bajo la misma transformación
WHERE 
  k_entrada.tipo = 'entrada'
  AND k_entrada.producto_id = 45 -- Asegurarnos que solo alteramos el Café Cereza original
  AND k_entrada.id != k_producto_final.id;


COMMIT;
