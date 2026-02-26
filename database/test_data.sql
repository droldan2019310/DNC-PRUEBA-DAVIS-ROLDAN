-- ═══════════════════════════════════════════════════════════════════════════
-- DATOS DE PRUEBA - DNC-ERP
-- ═══════════════════════════════════════════════════════════════════════════
--
-- Este script genera datos FICTICIOS para simular un entorno realista
-- Total de registros: ~146,000
-- 
-- ADVERTENCIA: Este script puede tomar 2-3 minutos en ejecutarse
--
-- ═══════════════════════════════════════════════════════════════════════════

USE dnc_erp_test;

-- Deshabilitar checks temporalmente para velocidad
SET FOREIGN_KEY_CHECKS=0;
SET UNIQUE_CHECKS=0;
SET AUTOCOMMIT=0;

-- ═══════════════════════════════════════════════════════════════════════════
-- DATOS: seg_usuario
-- ═══════════════════════════════════════════════════════════════════════════

INSERT INTO seg_usuario (id, nombre, email, password_hash, estado) VALUES
(1, 'Admin Test', 'admin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
(2, 'Supervisor Bodega', 'supervisor@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
(3, 'Usuario Producción', 'produccion@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Password de todos: "test1234"

-- ═══════════════════════════════════════════════════════════════════════════
-- DATOS: productos (Productos de café específicos + genéricos)
-- ═══════════════════════════════════════════════════════════════════════════

-- Productos de café específicos (para la Parte 2)
INSERT INTO productos (id, nombre, codigo, descripcion, estado) VALUES
(45, 'Café Cereza', 'CEREZA-001', 'Café en cereza (fruto completo)', 1),
(67, 'Café Pergamino', 'PERGAM-001', 'Café en pergamino húmedo', 1),
(89, 'Café Oro', 'ORO-001', 'Café en oro (verde)', 1);

-- Generar 853 productos adicionales para alcanzar 856 total
-- Esto simula un catálogo realista
INSERT INTO productos (nombre, codigo, descripcion, estado)
SELECT 
    CONCAT('Producto ', LPAD(n, 4, '0')) as nombre,
    CONCAT('PROD-', LPAD(n, 4, '0')) as codigo,
    CASE 
        WHEN n % 3 = 0 THEN 'Café en proceso tipo A'
        WHEN n % 3 = 1 THEN 'Café en proceso tipo B'
        ELSE 'Café procesado tipo C'
    END as descripcion,
    1 as estado
FROM (
    SELECT @row := @row + 1 AS n
    FROM information_schema.tables t1,
         information_schema.tables t2,
         (SELECT @row := 3) init
    LIMIT 853
) nums;

-- ═══════════════════════════════════════════════════════════════════════════
-- DATOS: bodegas (12 bodegas)
-- ═══════════════════════════════════════════════════════════════════════════

INSERT INTO bodegas (nombre, codigo, ubicacion, estado) VALUES
('Bodega Central', 'BOD-01', 'Oficina Central, Guatemala', 1),
('Bodega Beneficio Norte', 'BOD-02', 'Beneficio Huehuetenango', 1),
('Bodega Beneficio Sur', 'BOD-03', 'Beneficio Antigua', 1),
('Bodega Exportación', 'BOD-04', 'Puerto Quetzal', 1),
('Bodega Temporal 1', 'BOD-05', 'Zona 12, Guatemala', 1),
('Bodega Temporal 2', 'BOD-06', 'Zona 12, Guatemala', 1),
('Bodega Beneficio Este', 'BOD-07', 'Cobán, Alta Verapaz', 1),
('Bodega Beneficio Oeste', 'BOD-08', 'Quetzaltenango', 1),
('Bodega Calidad', 'BOD-09', 'Laboratorio Central', 1),
('Bodega Cuarentena', 'BOD-10', 'Área Restringida', 1),
('Bodega Tránsito', 'BOD-11', 'Zona de Carga', 1),
('Bodega Proceso', 'BOD-12', 'Planta de Proceso', 1);

COMMIT;

-- ═══════════════════════════════════════════════════════════════════════════
-- DATOS: kardex (145,000 movimientos)
-- ═══════════════════════════════════════════════════════════════════════════
--
-- IMPORTANTE: Este es el dataset CRÍTICO para la prueba
-- Genera movimientos realistas distribuidos en el último año
-- 
-- ADVERTENCIA: Esto puede tomar 1-2 minutos
--
-- ═══════════════════════════════════════════════════════════════════════════

-- Generar 145,000 movimientos de kardex
-- Distribución realista:
-- - 60% entradas
-- - 40% salidas  
-- - Distribuidos en último año
-- - Precios realistas ($50-$200 por unidad)
-- - Cantidades realistas (1-1000 unidades)

INSERT INTO kardex (producto_id, bodega_id, cantidad, tipo, precio_unitario, fecha, usuario_id, observaciones)
SELECT 
    -- Producto aleatorio (1 a 856)
    FLOOR(1 + (RAND() * 856)) as producto_id,
    
    -- Bodega aleatoria (1 a 12)
    FLOOR(1 + (RAND() * 12)) as bodega_id,
    
    -- Cantidad aleatoria (1 a 1000 unidades, decimal)
    ROUND(1 + (RAND() * 999), 2) as cantidad,
    
    -- Tipo: 60% entradas, 40% salidas
    IF(RAND() < 0.6, 'entrada', 'salida') as tipo,
    
    -- Precio unitario aleatorio ($50 a $200)
    ROUND(50 + (RAND() * 150), 2) as precio_unitario,
    
    -- Fecha aleatoria en último año
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 365) DAY) as fecha,
    
    -- Usuario aleatorio (1, 2, o 3)
    FLOOR(1 + (RAND() * 3)) as usuario_id,
    
    -- Observaciones variadas
    CASE 
        WHEN RAND() < 0.3 THEN 'Movimiento regular'
        WHEN RAND() < 0.6 THEN 'Ajuste de inventario'
        WHEN RAND() < 0.9 THEN 'Transferencia entre bodegas'
        ELSE NULL
    END as observaciones

FROM (
    -- Generar 145,000 filas usando product cartesiano de tablas del sistema
    SELECT @row := @row + 1 AS n
    FROM information_schema.tables t1,
         information_schema.tables t2,
         information_schema.tables t3,
         (SELECT @row := 0) init
    LIMIT 145000
) nums;

COMMIT;

-- ═══════════════════════════════════════════════════════════════════════════
-- DATOS ESPECÍFICOS PARA PARTE 2 (Bug de transformaciones)
-- ═══════════════════════════════════════════════════════════════════════════
--
-- Insertar algunos movimientos "problemáticos" que demuestran el bug
-- Estos servirán como ejemplos concretos del problema
--

-- Ejemplo 1: Transformación con el bug
-- 100 qq cereza → debería ser 85 qq pergamino, pero registra 100 qq
INSERT INTO kardex (producto_id, bodega_id, cantidad, tipo, precio_unitario, fecha, usuario_id, observaciones) VALUES
(45, 2, 100.00, 'entrada', 95.00, NOW() - INTERVAL 5 DAY, 2, 'Entrada cereza para transformación'),
(67, 2, 100.00, 'salida', 120.00, NOW() - INTERVAL 5 DAY, 2, 'Salida pergamino transformado - BUG');

-- Ejemplo 2: Otra transformación con el mismo bug
INSERT INTO kardex (producto_id, bodega_id, cantidad, tipo, precio_unitario, fecha, usuario_id, observaciones) VALUES
(45, 3, 250.00, 'entrada', 98.00, NOW() - INTERVAL 3 DAY, 2, 'Entrada cereza para transformación'),
(67, 3, 250.00, 'salida', 125.00, NOW() - INTERVAL 3 DAY, 2, 'Salida pergamino transformado - BUG');

-- Ejemplo 3: Transformación cereza → pergamino → oro (doble bug)
INSERT INTO kardex (producto_id, bodega_id, cantidad, tipo, precio_unitario, fecha, usuario_id, observaciones) VALUES
(45, 4, 500.00, 'entrada', 92.00, NOW() - INTERVAL 7 DAY, 2, 'Entrada cereza'),
(67, 4, 500.00, 'salida', 118.00, NOW() - INTERVAL 6 DAY, 2, 'Transformación a pergamino - BUG'),
(67, 4, 500.00, 'entrada', 118.00, NOW() - INTERVAL 6 DAY, 2, 'Entrada pergamino'),
(89, 4, 500.00, 'salida', 150.00, NOW() - INTERVAL 5 DAY, 2, 'Transformación a oro - BUG');

COMMIT;

-- Reactivar checks
SET FOREIGN_KEY_CHECKS=1;
SET UNIQUE_CHECKS=1;
SET AUTOCOMMIT=1;

-- ═══════════════════════════════════════════════════════════════════════════
-- VERIFICACIÓN DE DATOS
-- ═══════════════════════════════════════════════════════════════════════════

SELECT '════════════════════════════════════════════════' as '';
SELECT 'VERIFICACIÓN DE DATOS CARGADOS' as '';
SELECT '════════════════════════════════════════════════' as '';

-- Contar usuarios
SELECT 'Usuarios:' as Tabla, COUNT(*) as Total FROM seg_usuario;

-- Contar productos
SELECT 'Productos:' as Tabla, COUNT(*) as Total FROM productos;

-- Contar bodegas
SELECT 'Bodegas:' as Tabla, COUNT(*) as Total FROM bodegas;

-- Contar movimientos kardex
SELECT 'Kardex (movimientos):' as Tabla, COUNT(*) as Total FROM kardex;

-- Distribución de movimientos por tipo
SELECT '═══ Distribución Kardex por Tipo ═══' as '';
SELECT tipo as Tipo, COUNT(*) as Total, 
       CONCAT(ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM kardex)), 2), '%') as Porcentaje
FROM kardex
GROUP BY tipo;

-- Movimientos por mes (últimos 6 meses)
SELECT '═══ Movimientos por Mes ═══' as '';
SELECT 
    DATE_FORMAT(fecha, '%Y-%m') as Mes,
    COUNT(*) as Movimientos
FROM kardex
WHERE fecha >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(fecha, '%Y-%m')
ORDER BY Mes DESC
LIMIT 6;

-- Productos con más movimientos
SELECT '═══ Top 10 Productos con Más Movimientos ═══' as '';
SELECT 
    p.codigo,
    p.nombre,
    COUNT(k.id) as total_movimientos
FROM productos p
INNER JOIN kardex k ON k.producto_id = p.id
GROUP BY p.id, p.codigo, p.nombre
ORDER BY total_movimientos DESC
LIMIT 10;

-- Verificar ejemplos de transformaciones con bug
SELECT '═══ Ejemplos de Transformaciones con BUG ═══' as '';
SELECT 
    k.id,
    p.nombre as producto,
    k.cantidad,
    k.tipo,
    DATE_FORMAT(k.fecha, '%Y-%m-%d %H:%i') as fecha,
    k.observaciones
FROM kardex k
INNER JOIN productos p ON p.id = k.producto_id
WHERE k.observaciones LIKE '%BUG%'
ORDER BY k.fecha DESC;

SELECT '════════════════════════════════════════════════' as '';
SELECT '✅ DATOS CARGADOS EXITOSAMENTE' as '';
SELECT '════════════════════════════════════════════════' as '';
SELECT '' as '';
SELECT 'Puedes comenzar la prueba técnica.' as '';
SELECT 'Base de datos: dnc_erp_test' as '';
SELECT 'Total de registros: ~146,000' as '';

-- ═══════════════════════════════════════════════════════════════════════════
-- QUERIES ÚTILES PARA DEBUGGING
-- ═══════════════════════════════════════════════════════════════════════════

-- Calcular existencias actuales (simplificado)
-- SELECT 
--     p.nombre,
--     SUM(CASE WHEN k.tipo = 'entrada' THEN k.cantidad ELSE -k.cantidad END) as existencia
-- FROM productos p
-- LEFT JOIN kardex k ON k.producto_id = p.id
-- WHERE p.id IN (45, 67, 89)
-- GROUP BY p.id, p.nombre;

-- Identificar transformaciones sospechosas
-- SELECT 
--     k1.fecha,
--     k1.cantidad as cantidad_entrada,
--     k2.cantidad as cantidad_salida,
--     (k1.cantidad - k2.cantidad) as diferencia
-- FROM kardex k1
-- INNER JOIN kardex k2 ON ABS(TIMESTAMPDIFF(SECOND, k1.fecha, k2.fecha)) < 60
-- WHERE k1.producto_id = 45 
--   AND k2.producto_id = 67
--   AND k1.tipo = 'entrada'
--   AND k2.tipo = 'salida'
--   AND k1.cantidad = k2.cantidad;  -- Bug: misma cantidad

-- ═══════════════════════════════════════════════════════════════════════════
-- NOTAS PARA EL EVALUADOR
-- ═══════════════════════════════════════════════════════════════════════════
--
-- DISTRIBUCIÓN DE DATOS:
-- ----------------------
-- - Usuarios: 3 (Admin, Supervisor, Usuario)
-- - Productos: 856 (3 específicos + 853 genéricos)
-- - Bodegas: 12
-- - Kardex: 145,000+ movimientos
--
-- DATOS REALISTAS:
-- ----------------
-- - Fechas distribuidas en último año
-- - Cantidades variables (1-1000)
-- - Precios realistas ($50-$200)
-- - 60% entradas / 40% salidas
-- - Observaciones variadas
--
-- EJEMPLOS DE BUG:
-- ----------------
-- - 3 ejemplos concretos de transformaciones con bug
-- - Marcados con 'BUG' en observaciones
-- - Para que el candidato los identifique fácilmente
--
-- PERFORMANCE:
-- ------------
-- Con 145,000 registros:
-- - El KardexController.Index() tardará ~8.5 segundos
-- - El query problemático de la Parte 3 también ~8.5 segundos
-- - Ideal para demostrar problemas de performance reales
--
-- ═══════════════════════════════════════════════════════════════════════════
