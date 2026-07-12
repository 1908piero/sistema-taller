USE taller_db;

-- 1. Desactivar protección de claves foráneas temporalmente para poder borrar sin errores
SET FOREIGN_KEY_CHECKS = 0;

-- =======================================================
-- BLOQUE 1: ELIMINAR MOVIMIENTOS Y TRANSACCIONES
-- (Borra todo el historial de operaciones)
-- =======================================================
TRUNCATE TABLE historial_ordenes; -- Bitácora de cambios de estado
TRUNCATE TABLE orden_repuestos;   -- Detalles de repuestos en órdenes
TRUNCATE TABLE ordenes_servicio;  -- Las órdenes en sí
TRUNCATE TABLE detalle_ventas;    -- Productos dentro de cada venta POS
TRUNCATE TABLE ventas;            -- Las ventas POS
TRUNCATE TABLE kardex;            -- Historial de movimientos de inventario
TRUNCATE TABLE gastos;            -- Gastos registrados

-- =======================================================
-- BLOQUE 2: ELIMINAR CATÁLOGOS (DATOS MAESTROS)
-- (Si quieres mantener tus Productos pero borrar Clientes, quita la línea de 'productos')
-- =======================================================
TRUNCATE TABLE clientes;
TRUNCATE TABLE productos; 

-- =======================================================
-- BLOQUE 3: REINICIAR USUARIOS (OPCIONAL)
-- (Mantener solo el usuario ID 1 - Admin, borrar los demás)
-- =======================================================
DELETE FROM usuarios WHERE id > 1; 

-- Nota: No usamos TRUNCATE en 'usuarios' ni 'configuracion' para no quedarnos fuera del sistema.

-- 2. Reactivar protección de claves foráneas
SET FOREIGN_KEY_CHECKS = 1;

-- Confirmación visual
SELECT 'Base de datos restablecida correctamente. Lista para producción.' AS Estado;