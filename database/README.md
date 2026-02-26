# Database - Instrucciones

## Setup Rápido

```bash
# 1. Crear BD y estructura
mysql -u root -p < schema.sql

# 2. Cargar datos de prueba (toma 1-2 minutos)
mysql -u root -p dnc_erp_test < test_data.sql
```

## Verificar

```bash
mysql -u root -p dnc_erp_test -e "SELECT COUNT(*) FROM kardex;"
```

Debe mostrar ~145,000 registros.

## Tablas Incluidas

- `seg_usuario` - 3 usuarios
- `productos` - 856 productos
- `bodegas` - 12 bodegas
- `kardex` - 145,000+ movimientos

## Credenciales de Prueba

**Todos los usuarios:**
- Password: `test1234` (hasheado con bcrypt)

**Usuarios:**
- admin@test.com
- supervisor@test.com
- produccion@test.com
