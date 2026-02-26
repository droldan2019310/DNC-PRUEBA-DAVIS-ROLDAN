# DNC-ERP - Repositorio de Prueba Técnica

## ⚠️ IMPORTANTE

Este es un **repositorio sanitizado** para evaluación técnica. NO contiene código real de producción ni información sensible.

---

## 🚀 Setup Rápido

### Requisitos

- PHP 7.4+ (8.x recomendado)
- MySQL 5.7+ o 8.0
- Composer 2.x
- Git

### Instalación

```bash
# 1. Clonar repositorio
git clone [URL_REPO]
cd dnc-erp-prueba-tecnica

# 2. Instalar dependencias
composer install

# 3. Configurar base de datos
cp .env.example .env
# Editar .env con tus credenciales:
# DB_HOST=localhost
# DB_NAME=dnc_erp_test
# DB_USER=root
# DB_PASS=tu_password

# 4. Crear base de datos
mysql -u root -p < database/schema.sql

# 5. Cargar datos de prueba (toma 1-2 minutos)
mysql -u root -p dnc_erp_test < database/test_data.sql

# 6. Levantar servidor
php -S localhost:8000 -t public/

# 7. Verificar
# Abrir: http://localhost:8000
```

---

## 📁 Estructura del Proyecto

```
dnc-erp-prueba-tecnica/
├── core/                    # Framework PSR-4
│   ├── Controller.php       # Clase base controladores
│   ├── Model.php           # Clase base repositorios
│   ├── ACL.php             # Sistema de permisos
│   └── Traits/             # HasFilters, HasServices, etc.
│
├── legacy/                  # Código legacy de ejemplo
│   ├── controller/
│   │   ├── KardexController.php          # ⚠️ Parte 1 (N+1)
│   │   └── TransformacionController.php  # ⚠️ Parte 2 (Bug)
│   └── model/
│
├── database/
│   ├── schema.sql          # Estructura de BD
│   ├── test_data.sql       # 145k registros de prueba
│   └── README.md
│
├── docs/
│   ├── FRAMEWORK_BASICS.md  # Guía del framework
│   └── ACL_SIMPLIFIED.md    # Sistema de permisos
│
├── public/
│   └── index.php           # Front controller
│
├── app/                    # Tu código aquí
│   ├── Controllers/
│   ├── Models/Repository/
│   └── Views/
│
├── composer.json
├── .env.example
└── README.md (este archivo)
```

---

## 📚 Documentación

### Framework

- **[FRAMEWORK_BASICS.md](docs/FRAMEWORK_BASICS.md)** - Arquitectura, clases Core, FluentPDO
- **[ACL_SIMPLIFIED.md](docs/ACL_SIMPLIFIED.md)** - Sistema de permisos

### Base de Datos

Ver [database/README.md](database/README.md) para:
- Estructura de tablas
- Datos de prueba incluidos
- Credenciales de acceso

---

## 🎯 Contexto del Proyecto

### Negocio

Sistema ERP para empresa cafetalera que procesa:
- Compra de café en cereza
- Transformación (beneficio húmedo/seco)
- Control de inventarios (kardex)
- Gestión de contratos y exportación

### Datos de Prueba

- **Usuarios:** 3 (todos con password: `test1234`)
- **Productos:** 856 (diferentes estados de café)
- **Bodegas:** 12
- **Movimientos kardex:** 145,000+

### Código Problemático

El repositorio incluye 2 controladores con problemas intencionales:

1. **KardexController.php** (Parte 1)
   - Problema: N+1 queries
   - Impacto: ~3,000 queries por request
   - Tiempo: ~8.5 segundos

2. **TransformacionController.php** (Parte 2)
   - Problema: Bug conceptual
   - Impacto: Inventarios descuadrados
   - Criticidad: ALTA

---

## 🔧 Tecnologías

- **PHP 7.4+** (compatible con 8.x)
- **MySQL/MariaDB**
- **FluentPDO 2.2.0** - Query Builder
- **PSR-4** Autoloading
- **Sistema ACL** personalizado

---

## 🐛 Troubleshooting

### Error: "PDO Connection failed"

```bash
# Verificar que MySQL esté corriendo
mysql -u root -p

# Verificar credenciales en .env
cat .env
```

### Error: "Class 'FluentPDO' not found"

```bash
composer install
```

### Error: "Table doesn't exist"

```bash
# Cargar estructura y datos
mysql -u root -p < database/schema.sql
mysql -u root -p dnc_erp_test < database/test_data.sql
```

### Performance muy lento

El código legacy es intencionalmente lento para simular problemas reales. Con 145,000 registros:
- KardexController tarda ~8.5 segundos (esperado)
- Si es más lento, verifica índices en BD

---

## ✅ Checklist de Verificación

Antes de comenzar la prueba:

- [ ] PHP 7.4+ instalado (`php -v`)
- [ ] MySQL corriendo (`mysql -V`)
- [ ] Composer instalado (`composer -V`)
- [ ] Base de datos creada
- [ ] Datos cargados (145k registros)
- [ ] Servidor levantado
- [ ] Acceso a http://localhost:8000 funciona
- [ ] FluentPDO sin errores

---

## 🔒 Seguridad y Privacidad

### Este repositorio NO contiene:

- ❌ Lógica de negocio real
- ❌ Credenciales de producción
- ❌ Información de clientes reales
- ❌ APIs keys o tokens
- ❌ Código completo del ERP

### Datos ficticios:

- ✅ Nombres genéricos (Producto 001)
- ✅ Emails de prueba (@test.com)
- ✅ Passwords hasheados de ejemplo
- ✅ Cantidades y precios aleatorios

---

## 📞 Soporte Durante la Prueba

**Para dudas de setup técnico:**
- Email: dev@dnc-erp.com
- Respuesta en máximo 2 horas (horario laboral)

**NO respondemos:**
- Dudas sobre lógica de la prueba
- Cómo resolver los problemas planteados
- Sugerencias de implementación

---

## ⚖️ Licencia y Uso

Código propiedad de DNC-ERP. Uso exclusivo para evaluación técnica.

**Prohibido:**
- ❌ Uso comercial
- ❌ Redistribución
- ❌ Publicación en repositorios públicos

**Permitido:**
- ✅ Uso durante la prueba técnica
- ✅ Modificación para resolver ejercicios
- ✅ Ejecución local

---

## 📊 Estadísticas del Repositorio

```
Framework Core:    15 archivos PHP
Controladores:     2 (problemáticos)
Scripts SQL:       2 (estructura + datos)
Documentación:     5 archivos
Total archivos:    26
Tamaño:           39 KB (comprimido)
Registros BD:      ~146,000
```

---

**Versión:** 1.0  
**Fecha:** Febrero 2025  
**Propósito:** Evaluación Técnica Senior Developer  
**Generado desde:** Código real sanitizado
