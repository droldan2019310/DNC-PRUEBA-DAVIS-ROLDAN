-- ═══════════════════════════════════════════════════════════════════════════
-- SCRIPT DE BASE DE DATOS - PRUEBA TÉCNICA DNC-ERP
-- ═══════════════════════════════════════════════════════════════════════════
--
-- IMPORTANTE: Esta es una base de datos DE PRUEBA con datos FICTICIOS
-- NO contiene información real de producción
--
-- Propósito: Evaluación técnica de candidatos Senior
-- Versión: 1.0
-- Fecha: Febrero 2025
--
-- ═══════════════════════════════════════════════════════════════════════════

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS dnc_erp_test 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE dnc_erp_test;

-- ═══════════════════════════════════════════════════════════════════════════
-- TABLA: seg_usuario (Usuarios del sistema - simplificada)
-- ═══════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS seg_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL COMMENT 'Nombre completo del usuario',
    email VARCHAR(100) NOT NULL UNIQUE COMMENT 'Email de acceso',
    password_hash VARCHAR(255) NOT NULL COMMENT 'Password hasheado con bcrypt',
    estado TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_estado (estado)
) ENGINE=InnoDB 
COMMENT='Usuarios del sistema - versión simplificada para prueba';

-- ═══════════════════════════════════════════════════════════════════════════
-- TABLA: productos (Productos de café en diferentes estados)
-- ═══════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL COMMENT 'Nombre del producto',
    codigo VARCHAR(50) NOT NULL UNIQUE COMMENT 'Código único del producto',
    descripcion TEXT COMMENT 'Descripción del producto',
    estado TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_codigo (codigo),
    INDEX idx_estado (estado),
    INDEX idx_nombre (nombre)
) ENGINE=InnoDB 
COMMENT='Catálogo de productos (café en diferentes estados de procesamiento)';

-- ═══════════════════════════════════════════════════════════════════════════
-- TABLA: bodegas (Bodegas/almacenes)
-- ═══════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS bodegas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL COMMENT 'Nombre de la bodega',
    codigo VARCHAR(20) NOT NULL UNIQUE COMMENT 'Código único',
    ubicacion VARCHAR(200) COMMENT 'Ubicación física',
    estado TINYINT(1) DEFAULT 1 COMMENT '1=Activa, 0=Inactiva',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_codigo (codigo),
    INDEX idx_estado (estado)
) ENGINE=InnoDB 
COMMENT='Bodegas y almacenes del sistema';

-- ═══════════════════════════════════════════════════════════════════════════
-- TABLA: kardex (Movimientos de inventario)
-- ═══════════════════════════════════════════════════════════════════════════
--
-- Esta es la tabla CRÍTICA para la prueba técnica
-- Contiene 145,000 registros de prueba para simular carga real
--
-- NOTA: Los índices actuales son BÁSICOS intencionalmente
--       El candidato debe proponer índices OPTIMIZADOS en la Parte 3
--

CREATE TABLE IF NOT EXISTS kardex (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL COMMENT 'ID del producto',
    bodega_id INT NOT NULL COMMENT 'ID de la bodega',
    cantidad DECIMAL(10,2) NOT NULL COMMENT 'Cantidad del movimiento',
    tipo ENUM('entrada', 'salida') NOT NULL COMMENT 'Tipo de movimiento',
    precio_unitario DECIMAL(10,2) DEFAULT 0 COMMENT 'Precio/costo unitario',
    fecha DATETIME NOT NULL COMMENT 'Fecha del movimiento',
    usuario_id INT NOT NULL COMMENT 'Usuario que registró',
    observaciones TEXT COMMENT 'Observaciones del movimiento',
    transformacion_id INT NULL COMMENT 'ID de transformación si aplica',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    FOREIGN KEY (bodega_id) REFERENCES bodegas(id),
    FOREIGN KEY (usuario_id) REFERENCES seg_usuario(id),
    
    -- Índices BÁSICOS (intencionales para la prueba)
    -- ⚠️ El candidato debe identificar que estos NO son óptimos
    INDEX idx_producto (producto_id),
    INDEX idx_bodega (bodega_id),
    INDEX idx_fecha (fecha)
    
    -- ÍNDICES QUE FALTAN (el candidato debe proponerlos):
    -- INDEX idx_producto_bodega_tipo (producto_id, bodega_id, tipo)
    -- INDEX idx_producto_tipo_fecha (producto_id, tipo, fecha DESC)
    
) ENGINE=InnoDB 
COMMENT='Movimientos de inventario (kardex) - 145k registros de prueba';

-- ═══════════════════════════════════════════════════════════════════════════
-- TABLA: transformaciones (Para la Parte 2 - propuesta de solución)
-- ═══════════════════════════════════════════════════════════════════════════
--
-- Esta tabla NO existe actualmente (es parte del bug)
-- El candidato debe proponerla como parte de la solución
--

-- CREATE TABLE IF NOT EXISTS transformaciones (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     producto_entrada_id INT NOT NULL,
--     cantidad_entrada DECIMAL(10,2) NOT NULL,
--     producto_salida_id INT NOT NULL,
--     cantidad_salida DECIMAL(10,2) NOT NULL,
--     merma DECIMAL(10,2) NOT NULL COMMENT 'Diferencia por pérdida natural',
--     rendimiento DECIMAL(5,2) NOT NULL COMMENT 'Porcentaje de rendimiento',
--     costo_transformacion DECIMAL(10,2) DEFAULT 0,
--     bodega_id INT NOT NULL,
--     fecha DATETIME NOT NULL,
--     usuario_id INT NOT NULL,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     
--     FOREIGN KEY (producto_entrada_id) REFERENCES productos(id),
--     FOREIGN KEY (producto_salida_id) REFERENCES productos(id),
--     FOREIGN KEY (bodega_id) REFERENCES bodegas(id),
--     FOREIGN KEY (usuario_id) REFERENCES seg_usuario(id),
--     
--     INDEX idx_fecha (fecha),
--     INDEX idx_productos (producto_entrada_id, producto_salida_id)
-- ) ENGINE=InnoDB 
-- COMMENT='Registro de transformaciones de productos';

-- ═══════════════════════════════════════════════════════════════════════════
-- QUERY PROBLEMA (Para Parte 3 - Optimización)
-- ═══════════════════════════════════════════════════════════════════════════
--
-- Este es el query problemático que el candidato debe optimizar
-- Actualmente tarda ~8.5 segundos con los datos de prueba
--

-- SELECT 
--     p.nombre as producto,
--     b.nombre as bodega,
--     (
--         SELECT COALESCE(SUM(cantidad), 0) 
--         FROM kardex k1 
--         WHERE k1.producto_id = p.id 
--         AND k1.bodega_id = b.id 
--         AND k1.tipo = 'entrada'
--     ) - (
--         SELECT COALESCE(SUM(cantidad), 0) 
--         FROM kardex k2 
--         WHERE k2.producto_id = p.id 
--         AND k2.bodega_id = b.id 
--         AND k2.tipo = 'salida'
--     ) as existencia,
--     (
--         SELECT precio_unitario 
--         FROM kardex k3 
--         WHERE k3.producto_id = p.id 
--         AND k3.tipo = 'entrada'
--         ORDER BY fecha DESC 
--         LIMIT 1
--     ) as ultimo_costo
-- FROM productos p
-- CROSS JOIN bodegas b
-- WHERE p.estado = 1 AND b.estado = 1
-- ORDER BY p.nombre, b.nombre;

-- ═══════════════════════════════════════════════════════════════════════════
-- VERIFICACIÓN DE ESTRUCTURA
-- ═══════════════════════════════════════════════════════════════════════════

-- Ver tablas creadas
SHOW TABLES;

-- Ver estructura de kardex (tabla crítica)
DESCRIBE kardex;

-- Verificar índices en kardex
SHOW INDEX FROM kardex;

-- ═══════════════════════════════════════════════════════════════════════════
-- NOTAS PARA EL EVALUADOR
-- ═══════════════════════════════════════════════════════════════════════════
--
-- ÍNDICES INTENCIONALES:
-- ----------------------
-- Los índices en la tabla kardex son BÁSICOS a propósito.
-- El candidato debe identificar que NO son óptimos y proponer:
--
-- 1. idx_producto_bodega_tipo (producto_id, bodega_id, tipo)
--    - Cubre las búsquedas más comunes
--    - Elimina la necesidad de 2 índices separados
--
-- 2. idx_producto_tipo_fecha (producto_id, tipo, fecha DESC)
--    - Para búsqueda de último precio
--    - El DESC es crucial para el ORDER BY
--
-- 3. ¿Mantener o eliminar los básicos?
--    - idx_producto: Redundante con el compuesto
--    - idx_bodega: Puede ser útil para otras queries
--    - idx_fecha: Útil para reportes por rango
--
-- QUERY PROBLEMÁTICO:
-- -------------------
-- 3 subqueries correlacionadas → deben convertirse a JOINs
-- CROSS JOIN → genera 10,272 combinaciones (856 × 12)
-- Sin índices óptimos → full table scan en cada subquery
--
-- SOLUCIÓN ESPERADA:
-- ------------------
-- 1 query con JOINs laterales o subqueries en FROM
-- Reducción de 8.5s a <500ms con índices apropiados
--
-- ═══════════════════════════════════════════════════════════════════════════
