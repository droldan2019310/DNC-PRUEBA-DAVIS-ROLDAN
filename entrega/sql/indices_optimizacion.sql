-- ÍNDICES PARA OPTIMIZACIÓN DE DASHBOARD APLICANDO EXPLAIN PLAN
-- Target: Bajar lectura de índice full sobre 145,000 registros

-- 1. Índice compuesto base para la lógica central del ERP: Inventario.
-- Abarca la búsqueda masiva agrupada por Producto, Bodega y clasificados por Tipo de Movimiento (Entrada/Salida).
-- El Index Condition Pushdown permitirá obviar el acceso a disco por cada COUNT/SUM.

ALTER TABLE `kardex`
ADD INDEX `idx_kardex_inventario_activo` (`producto_id`, `bodega_id`, `tipo`);

-- 2. Índice específico para el cálculo del Último Costo (ORDER BY fecha DESC LIMIT 1).
-- Filtra instantáneamente las Entradas del producto y encuentra la fecha mayor sin leer todo el conjunto.
ALTER TABLE `kardex`
ADD INDEX `idx_kardex_ultimo_costo` (`producto_id`, `tipo`, `fecha` DESC);

-- Asegurar que el filtrado inicial del WHERE principal responda sin table scan.
ALTER TABLE `productos`
ADD INDEX `idx_productos_estado` (`estado`);

ALTER TABLE `bodegas`
ADD INDEX `idx_bodegas_estado` (`estado`);
