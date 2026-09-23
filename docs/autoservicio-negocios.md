# Autoservicio de negocios con verificación de pago

## Flujo actual

`/solicitar-incorporacion` conserva la selección visual de plan y categoría y pide datos del local, usuario, contraseña, referencia bancaria y comprobante JPG/PNG/WEBP de hasta 5 MB.

`RegistroNegocioService::registrar()` valida los datos con `SolicitudEntradaService::validarDatos()`. Dentro de una transacción guarda el comprobante, crea lugar COMERCIAL, publicación aprobada/habilitada, cuenta con bcrypt y pago PENDIENTE. Los importes son Bs 250 / 1 mes y Bs 2500 / 12 meses. Necesita una tarifa vigente en la base. Un error revierte todas las inserciones y elimina el comprobante que se acababa de subir.

No se crean solicitudes ni vigencias en el nuevo registro. Tras el commit se regenera la sesión y se asignan las variables del negocio. La respuesta JSON contiene `redirect: '/negocio/dashboard'`; Fetch resuelve el prefijo del proyecto mediante `http.buildUrl()`.

El portal permite fotos y promociones durante la verificación. El dueño puede ver sus imágenes privadas; visitantes y otras cuentas no pueden leerlas. La sesión se valida contra una cuenta activa en cada acceso. Los mensajes del portal distinguen contenido guardado de contenido publicado.

## Revisión administrativa

En `/admin/pagos?estado=PENDIENTE`, el administrador puede abrir el comprobante en un modal privado y:

- **Confirmar Abono:** confirma el pago y crea una vigencia de 1 o 12 meses calendario. Para registros iniciales, comienza el día de confirmación para no descontar la espera de revisión. Las renovaciones y pagos manuales conservan sus reglas anteriores. La confirmación no cambia la aprobación ni levanta suspensiones.
- **Rechazar / Eliminar:** abre una confirmación que identifica el comercio y la eliminación permanente. Solo se permite en altas iniciales de autoservicio pendientes, sin pagos confirmados ni historial de vigencias. El bloqueo de filas impide competir con una confirmación simultánea. Se elimina el lugar y sus relaciones mediante las FK `ON DELETE CASCADE`: cuenta, publicación, fotos, promociones y pagos. Los archivos físicos sin referencias se retiran tras el commit.

La eliminación de disco no participa en la transacción SQL. Si el sistema no puede retirar un archivo, el panel lo informa y el log guarda los nombres pendientes para limpieza. Un fallo SQL conserva los datos y sus archivos. La sesión del negocio eliminado deja de permitir el acceso.

`PublicacionService` sigue siendo la autoridad de visibilidad. El dashboard solo muestra “Ficha Activa y Visible en la Guía” si se cumple esa regla, junto al vencimiento. Un pago confirmado pero vencido, futuro o suspendido no produce un falso estado activo.

## Base de datos

En la base local se aplicó `database/migrations/005_autoservicio.sql`. Agrega `pagos.es_registro_inicial`, con valor 0 para registros históricos y 1 solo para nuevas altas. No crea tablas adicionales ni cambia los registros existentes. El instalador 001 también contiene la columna para instalaciones nuevas.

En otra instalación, aplicar las migraciones existentes hasta 005 en orden. No ejecutar el instalador 001 sobre datos existentes porque reconstruye tablas. La migración 005 puede repetirse.

La bandeja anterior de solicitudes queda disponible para procesar su historial. Su script de purga no elimina los nuevos negocios ni sus comprobantes referenciados en pagos.

## Verificación

```text
C:\xampp\php\php.exe tests/solicitudes.php
C:\xampp\php\php.exe tests/integracion_bd.php
C:\xampp\php\php.exe tests/autenticacion.php
```

Las pruebas HTTP usan una base y archivos temporales aislados. Cubren validaciones de entrada/archivos, CSRF, rollback del registro, sesión automática, galería privada, promociones en borrador, revisión del comprobante, confirmación mensual/anual, eliminación completa, fallo de eliminación, protección de negocios activos e invalidación de sesiones.
