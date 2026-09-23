# Solicitudes comerciales y aprovisionamiento BeniTurs

> Documento del flujo anterior. El registro público actual usa [autoservicio con pago pendiente](autoservicio-negocios.md). La bandeja de solicitudes y su purga se mantienen para registros históricos; los nuevos registros se revisan en Pagos.

## Instalación

En una base existente con las migraciones 002 y 003, ejecutar `database/migrations/004_cuarentena_aprovisionamiento.sql`. Es repetible y conserva los registros. En una instalación nueva, `001_crear_tablas.sql` ya incluye los campos nuevos; ese instalador reconstruye tablas y no sirve para actualizar bases existentes.

Se agregaron `solicitudes.comprobante_archivo`, `usuario_solicitado`, `password_hash_solicitado` y `numero_comprobante`, además de asegurar `plan_solicitado`. `pagos.comprobante_archivo` conserva la referencia al adjunto auditado. Las credenciales vacías identifican solicitudes anteriores a la migración; no se inventan usuarios ni contraseñas para ellas.

PHP requiere PDO MySQL, fileinfo y mbstring. El proceso de Apache debe poder escribir en `storage/uploads/comprobantes/` y en su directorio temporal de subida. `upload_max_filesize` debe admitir al menos 5M y `post_max_size` debe ser mayor que 5M para incluir los demás campos. Los límites locales de XAMPP son 40M; la aplicación limita el archivo a 5 MB reales.

## Flujo

1. `/solicitar-incorporacion` envía FormData con CSRF, categoría comercial activa, datos del establecimiento, plan, usuario, contraseña, número de operación y comprobante.
2. `SolicitudEntradaService::recibir()` valida longitudes, teléfono boliviano, correo, usuario y contraseña. `ComprobanteService` comprueba la subida HTTP, tamaño real, MIME con finfo y cabecera de imagen. Guarda un nombre aleatorio fuera de `public/`.
3. Solo se inserta una solicitud PENDIENTE. Si falla el INSERT se retira el archivo. No se crean cuentas, lugares ni pagos en este paso.
4. En `/admin/solicitudes`, el administrador abre el comprobante en el modal privado y pulsa **Verificar y Aprovisionar Todo**.
5. `SolicitudService::aprovisionarNegocioCompleto()` bloquea la solicitud con `FOR UPDATE` y crea, en una única transacción, la aceptación auditada, lugar, publicación, cuenta, pago confirmado y vigencia. Se requiere una tarifa vigente. Los importes del requerimiento son Bs 250 / 1 mes y Bs 2500 / 12 meses; fecha de inicio: hoy en America/La_Paz.
6. El hash bcrypt se copia a la cuenta sin volver a cifrarlo. Un usuario ocupado, una solicitud ya procesada o cualquier fallo revierten todas las escrituras. No se acepta una solicitud nueva por el antiguo flujo de conversión parcial.
7. La bandeja muestra el enlace de bienvenida a `wa.me/591...`. El administrador decide cuándo enviarlo; el sistema no envía mensajes ni incluye contraseñas. El enlace usa `config/app.php` para dirigir al portal; al publicar el sitio, configurar una URL accesible desde el teléfono del destinatario.

Los comprobantes solo se entregan por `/admin/solicitudes/comprobante?id=...` con sesión administrativa activa. No se expone una URL pública del archivo. El portal `/negocio/login` verifica exclusivamente la contraseña elegida; se retiró su antigua clave de respaldo.

## Purga programable

Comando para el Programador de tareas de Windows (por ejemplo, una vez al día):

```text
Programa: C:\xampp\php\php.exe
Argumentos: C:\xampp\htdocs\trinidad_turismo\bin\purgar_solicitudes.php
Iniciar en: C:\xampp\htdocs\trinidad_turismo
```

El script solo funciona por CLI. No se instaló una tarea del sistema ni se ejecutó la purga sobre los datos locales durante la implementación.

`purgarSolicitudesAbandonadas()` elimina PENDIENTES de más de 15 días que no estén vinculadas a un lugar. Comparte el bloqueo de fila con el aprovisionamiento. Tras confirmar la eliminación, retira sus archivos únicamente si ninguna solicitud ni pago los referencia. También reintenta archivos huérfanos de más de 15 días. Las aceptadas, rechazadas, pendientes recientes y comprobantes referenciados se conservan. La eliminación de archivos no es transaccional: los fallos se registran y el proceso devuelve código 1 para que el programador pueda alertar o reintentar.

## Pruebas

```text
C:\xampp\php\php.exe tests/solicitudes.php
C:\xampp\php\php.exe tests/integracion_bd.php
C:\xampp\php\php.exe tests/autenticacion.php
```

Las pruebas HTTP de solicitudes crean una base y un directorio temporal aislados, usan uploads reales y sesiones reales, y los eliminan al terminar. Verifican entradas inválidas, archivos falsos/grandes, CSRF, cuarentena, acceso privado, rollback ante fallo en vigencias, mensual/anual, duplicados, acceso al portal, WhatsApp y purga. No envían WhatsApp ni usan datos de producción para aprovisionar.
