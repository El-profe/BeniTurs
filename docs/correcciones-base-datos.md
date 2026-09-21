# Correcciones de integración — 21 de septiembre de 2026

La base local ya tiene aplicadas las migraciones 002 y 003. No hay que volver a importar el volcado.

Para actualizar otra instalación existente, seleccionar su base en phpMyAdmin y ejecutar, en orden:

1. `database/migrations/002_completar_esquema.sql`
2. `database/migrations/003_duracion_pagos.sql`

Ambas migraciones conservan los registros y pueden repetirse. La segunda agrega `pagos.meses_duracion`. Solo para los pagos históricos conserva la interpretación del código anterior: importes de 2500 o más corresponden a 12 meses; los demás, a uno. No modifica las vigencias existentes. Para nuevos pagos, el administrador elige explícitamente mensual o anual; la solicitud propone el plan al seleccionar el comercio. El importe se confirma por separado.

`001_crear_tablas.sql` se corrigió para instalaciones nuevas: incluye las 11 tablas, `plan_solicitado`, `meses_duracion` y la definición válida de `tamano_bytes`. Este archivo reconstruye tablas: no usarlo para actualizar una base con datos.

El volcado `database/seeds/trinidad_turismo_db.sql` se conserva como exportación histórica original. Si se restaura en otra base, aplicar luego 002 y 003.

Cambios de comportamiento:

- La búsqueda funciona con consultas preparadas nativas.
- Administración y catálogo usan la misma regla de visibilidad.
- Confirmar pagos conserva aprobación, suspensión y habilitación administrativas.
- Las renovaciones excluyen pagos anulados y las confirmaciones del mismo comercio se serializan.
- La conversión de solicitudes es atómica y rechaza duplicados.
- El acceso administrativo verifica únicamente el hash almacenado; no hay contraseña de respaldo ni restablecimiento automático.

Validación: `php tests/integracion_bd.php` crea y elimina una base de prueba aislada; requiere permiso para crear bases. Comprueba instalación, migraciones repetidas, búsqueda, visibilidad, mensual/anual, suspensiones, duplicados y reversión ante errores. `php tests/solicitudes.php` verifica el envío de solicitudes con transacciones revertidas.
