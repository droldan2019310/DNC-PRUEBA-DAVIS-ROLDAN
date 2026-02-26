# Análisis de Prueba Técnica - Parte 1

## 1.1 Identificación de Problemas (10 pts)

**Problemas de Performance Identificados:**
1. **Problema de Consultas N+1 (N+1 Query Problem):** Dentro del método `Index()`, se realiza una consulta inicial para obtener todos los productos. Luego, por cada iteración del bucle `foreach` (que recorre los productos devueltos), se realizan **3 consultas adicionales**:
   - Una consulta para sumar las entradas.
   - Una consulta para sumar las salidas.
   - Una tercera consulta dentro de la función `calcularCostoPromedio()` para obtener *todos* los registros de entrada de un producto ordenados por fecha.
2. **Uso Ineficiente de Memoria en PHP:** En el método `calcularCostoPromedio()`, se traen **todos** los registros de movimientos de tipo "entrada" hacia la memoria temporal `fetchAll()`, y se iteran uno por uno en PHP para sumar y calcular el promedio. Esto consume memoria RAM y CPU innecesaria, un trabajo que se debe delegar al motor de la base de datos con agrupaciones de SQL.
3. **Instanciación Redundante del Modelo:** Se instancia `new KardexFpdoModel($this->AdapterModel)` no solo al inicio, sino *cada vez* que se evalúa y se invoca `calcularCostoPromedio()`, añadiendo sobrecarga de creación de clases y objetos.

**Impacto en Producción con 1,000 Productos:**
Al tener 1,000 productos activos en el catálogo, el actual controlador va a ejecutar:
- 1 consulta base para traer la tabla de los 1,000 productos.
- 1,000 iteraciones del bucle, donde adentro en cada paso se ejecutan 3 queries.
Resultando en **3,001 consultas a la base de datos** por cada visualización de la vista Index. 
El impacto en producción sería posiblemente en Timeouts: bloqueos de base de datos `Lock/Timeout` saturación de conexiones SQL `Too many connections` y picos de uso en RAM en PHP lo que provoca en una experiencia de usuario deficiente y lenta.

**Estimación de Queries SQL Ejecutadas:**
Se realizan **(1 + 3N) queries**, siendo N la cantidad de productos en el listado. Para 1,000 productos son estimadas un total de **3,001 queries**.

---

## 1.2 Problema de Negocio (8 pts)

**El costo promedio está mal calculado. ¿Por qué?**
El método que se muestra toma el valor financiero de todas las unidades que alguna vez entraron a la bodega de la historia de la DB y lo promedia ignorando totalmente los movimientos de salida o venta a lo largo del tiempo. Las unidades históricas ya vendidas o las mercaderías obsoletas continúan existiendo en esta balanza de costo promedio "infinito", afectando contablemente el inventario físico verdadero y calculando mal los precios de los inventarios más recientes.

**¿Qué método de costeo se debería usar? (FIFO, LIFO, Promedio Ponderado)**
En la gran mayoría de transacciones comerciales de un ERP, y especialmente dependientes de materias/commodities con grandes mermas, se asumen dos estándares: el Costo Promedio Ponderado o el FIFO (First-In, First-Out).
- El Costo Promedio Ponderado es ideal y ampliamente adaptado por sistemas ERP, recalcula y suaviza el costo solo después de cada nueva entrada de mercancía sobre el inventario base anterior, ofreciendo estabilidad y representación real de la bodega.

**Fórmula Correcta Propuesta (Costo Promedio Ponderado):**
Después de cada movimiento o evento de Entrada, el sistema debe insertar el costo actual calculando mediante esta regla aritmética:
```text
Nuevo Costo Promedio = ( (Existencia Anterior × Costo Promedio Anterior) + (Cantidad Entrante × Costo de la Nueva Entrada) ) / (Existencia Anterior + Cantidad Entrante)
```

*(Nota técnica: Para los efectos de la refactorización - Parte 1.3, para no alterar la estructura actual de la base de datos (como agregar una columna para el costo actual en la tabla `productos`) sin antes consultarlo con el equipo y para no romper integraciones en caliente, nuestro código refactorizado preservará el costo "promediado global heredado", resolviendo el coste al vuelo desde el SQL Group By).*

---

## 1.3 Refactorización (7 pts)
*El código se ha proveído en la ruta `entrega/codigo/KardexController.php` adaptado bajo normas PSR-4.*

**Acerca del Código Refactorizado y Carga de Queries (Reducido de O(1+3N) a máximo 2 consultas):**
El controlador ha sido reescrito para no ahondar en N+1 Queries:
1. **Consulta #1:** Obtiene la lista base y completa de todos los productos vigentes (estado=1).
2. **Consulta #2:** Realiza una sola query con `GROUP BY producto_id` para traerse sumas agregadas matemáticas en tiempo real desde la BD (usando `SUM(CASE WHEN...)`) de entradas matriciales, salidas y total gastado general, delegando la responsabilidad de cálculo a la DB y no a la iteración.
   - En PHP se empareja todo con el listado principal con una complejidad final de tiempo de `O(N)` usando un diccionario hash en la memoria en vez de iterar.

Se logra así mantener intacto el output del DataGrid a la vista `Kardex/IndexView` (`['producto', 'existencia', 'costo']`), respetando la compatibilidad de datos frontal requerida sin colapsar el motor.
