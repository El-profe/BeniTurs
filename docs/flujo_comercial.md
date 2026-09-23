# Incorporación comercial asistida

Este flujo sustituye el registro inmediato del autoservicio. Los negocios existentes conservan sus cuentas y sus pagos. Las nuevas solicitudes se guardan en cuarentena: no hay cuenta, ficha, publicación, pago ni vigencia hasta que un administrador verifica el abono y aprueba.

## Instalación

1. Aplicar `database/migrations/006_flujo_solicitud_comercial.sql` sobre la base existente, después de las migraciones 001–005. **No volver a ejecutar 001 sobre datos existentes**, pues recrea las tablas. La 006 es aditiva y admite repetición.
2. Configurar las variables de entorno de `config/comercial.php`, o copiar `config/comercial.example.php` a `config/comercial.local.php` y completar los valores. El archivo local está excluido de Git. Indicar banco, titular, cuenta y URL de la imagen del QR institucional real. El QR es estático; la pantalla indica Bs 250 o Bs 2,500 y el usuario ingresa ese monto en su aplicación bancaria. Nunca se inventan datos bancarios ni QR.
3. Configurar `BENITURS_BASE_URL` con la URL pública del directorio `public`, sin barra final. Se usa en el enlace de acceso al portal y para registrar el webhook. En local se mantiene `http://localhost/trinidad_turismo/public`.
4. Asegurar escritura en `storage/uploads/comprobantes` y `storage/private`, y que Apache bloquee el acceso HTTP a `storage`. Extensiones PHP necesarias: PDO MySQL, fileinfo, mbstring, OpenSSL y cURL. `upload_max_filesize` debe admitir 5M y `post_max_size` superar ese límite (por ejemplo 8M).

## Telegram

Variables: `TELEGRAM_BOT_TOKEN`, `TELEGRAM_ADMIN_CHAT_ID`, `TELEGRAM_WEBHOOK_SECRET` y opcionalmente `TELEGRAM_ADMIN_LOCAL_ID`. El administrador debe iniciar primero una conversación privada con el bot. Se verifica tanto `from.id` como el ID del chat y el encabezado secreto. Nunca se usa el ID de Telegram como una clave foránea de `administradores`.

Con una URL HTTPS accesible desde Internet, ejecutar:

```powershell
php bin/configurar_telegram.php
```

Telegram no puede llamar a `localhost`. Para desarrollo se necesita un dominio HTTPS accesible o un túnel configurado por el operador. El registro del webhook no se ejecuta automáticamente durante las pruebas.

Programar cada minuto (cron o Programador de tareas de Windows, ejecución oculta):

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\trinidad_turismo\bin\telegram_notificaciones.php
```

El POST del visitante inserta una notificación en la misma transacción que la solicitud; no realiza llamadas externas. El trabajador envía el comprobante y los botones con `sendPhoto`. Las aprobaciones y los rechazos también generan una notificación pendiente; los errores de red se reintentan sin deshacer operaciones comerciales. `telegram_notificaciones` permite revisar estado, intentos y último error. La entrega es **al menos una vez**: un timeout remoto puede producir un mensaje duplicado, pero la resolución de la solicitud es idempotente y no duplica registros.

Al aprobar desde Telegram, se quitan los botones del comprobante y se envía al administrador un enlace para entregar las credenciales por WhatsApp. El enlace abre un borrador; el administrador decide cuándo enviarlo. No se envían mensajes de WhatsApp automáticamente.

Referencia de los métodos y del encabezado de autenticación: [Telegram Bot API](https://core.telegram.org/bots/api).

## Datos y transacciones

La aprobación bloquea la solicitud con `SELECT ... FOR UPDATE`. Crea ficha, publicación habilitada y aprobada, cuenta con bcrypt, pago confirmado y vigencia de 1 o 12 meses dentro de una única transacción. El precio se calcula en el servidor: los importes manipulados en el navegador no se aceptan como precio del plan. La validación bancaria sigue siendo responsabilidad del administrador; una imagen no acredita automáticamente el abono.

En este esquema `aprobado` y `habilitado` pertenecen a `publicaciones`, no a `lugares`: se mantiene esa fuente única de estado para ser compatible con las consultas de visibilidad existentes.

El usuario y la contraseña aleatoria de 8 caracteres se generan al aprobar. Solo el hash bcrypt se almacena en la cuenta. Para poder recuperar la entrega tras una recarga o un fallo de Telegram, el enlace de WhatsApp se guarda cifrado con AES-256-GCM. Se elimina esa copia al primer acceso correcto del negocio. `BENITURS_CREDENTIAL_KEY` admite una clave Base64 de 32 bytes; si no se configura, se genera una clave privada persistente en `storage/private/credenciales.key`. Incluir esa clave en los respaldos privados: cambiarla o perderla impide recuperar entregas pendientes.

Los comprobantes se validan por MIME real, estructura de imagen y tamaño (máximo 5 MiB); llevan nombre aleatorio y se sirven exclusivamente por el controlador administrativo autenticado. Los formularios administrativos y públicos usan CSRF.

## Verificación

```powershell
php tests/solicitudes.php
node --check public/assets/js/publico/solicitudes.js
node --check public/assets/js/admin/solicitudes.js
```

La prueba HTTP crea y elimina una base MariaDB y directorios temporales exclusivos. Comprueba cuarentena, archivos inválidos, CSRF, aprobación mensual/anual, rollback forzado, reintentos, rechazo, permisos de webhook y acceso con la clave generada. No envía mensajes reales. `tests/autoservicio_casos.php` documenta el flujo anterior y ya no se invoca desde esta suite.
