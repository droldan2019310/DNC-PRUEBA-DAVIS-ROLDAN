
-- Estrategia: Reemplazar Subqueries Correlacionadas (O(N*M)) por Pre-Agrupaciones (O(K))

-- FORMA 1: COMPATIBILIDAD CON MYSQL 5.7+ (Usando Derived Tables / LEFT JOINs)
-- Extrae el último costo usando subconsultas INNER JOIN con max_fecha,
-- eliminando al 100% las Correlated Subqueries y reemplazándolas por 
-- un Merge/Hash Join que escala perfectamente.

SELECT
  p.nombre AS producto,
  b.nombre AS bodega,
  COALESCE(r.total_entradas,0) - COALESCE(r.total_salidas,0) AS existencia,
  uc.precio_unitario AS ultimo_costo
FROM productos p
CROSS JOIN bodegas b
LEFT JOIN (
  SELECT
    producto_id,
    bodega_id,
    SUM(CASE WHEN tipo='entrada' THEN cantidad ELSE 0 END) AS total_entradas,
    SUM(CASE WHEN tipo='salida'  THEN cantidad ELSE 0 END) AS total_salidas
  FROM kardex
  GROUP BY producto_id, bodega_id
) r
  ON r.producto_id = p.id AND r.bodega_id = b.id
LEFT JOIN (
  SELECT k.producto_id, k.precio_unitario
  FROM kardex k
  INNER JOIN (
    SELECT producto_id, MAX(fecha) AS max_fecha
    FROM kardex
    WHERE tipo='entrada'
    GROUP BY producto_id
  ) m
    ON m.producto_id = k.producto_id
   AND m.max_fecha   = k.fecha
  WHERE k.tipo='entrada'
) uc
  ON uc.producto_id = p.id
WHERE p.estado=1 AND b.estado=1
ORDER BY p.nombre, b.nombre;


-- para MYSQL 8.0+

WITH k_resumen AS (
  SELECT producto_id, bodega_id,
         SUM(tipo='entrada' * cantidad) AS total_entradas,
         SUM(tipo='salida'  * cantidad) AS total_salidas
  FROM kardex
  GROUP BY producto_id, bodega_id
),
ultimo_costo AS (
  SELECT producto_id, precio_unitario
  FROM (
    SELECT producto_id, precio_unitario,
           ROW_NUMBER() OVER (PARTITION BY producto_id ORDER BY fecha DESC) AS rn
    FROM kardex
    WHERE tipo='entrada'
  ) x
  WHERE rn=1
)
SELECT
  p.nombre AS producto,
  b.nombre AS bodega,
  COALESCE(r.total_entradas,0) - COALESCE(r.total_salidas,0) AS existencia,
  uc.precio_unitario AS ultimo_costo
FROM productos p
CROSS JOIN bodegas b
LEFT JOIN k_resumen r
  ON r.producto_id=p.id AND r.bodega_id=b.id
LEFT JOIN ultimo_costo uc
  ON uc.producto_id=p.id
WHERE p.estado=1 AND b.estado=1
ORDER BY p.nombre, b.nombre;