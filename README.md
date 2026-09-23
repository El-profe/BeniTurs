Trinidad Turismo — estructura inicial de carpetas

Flujo comercial actual: ver [incorporación por pasos, aprobación atómica y Telegram](docs/flujo_comercial.md). Sustituye el registro inmediato de cuentas; requiere la migración 006 y configurar los datos de cobro y del bot. La descripción de la estructura inicial que sigue es histórica.

Este paquete contiene las carpetas del proyecto y esta guía. Los archivos PHP, JavaScript, CSS, SQL y Bootstrap se implementarán o incorporarán en los siguientes pasos. La estructura todavía no ejecuta una aplicación web.

Tecnologías acordadas: PHP puro con programación orientada a objetos, arquitectura MVC, Bootstrap, CSS, JavaScript nativo con Fetch y JSON, Apache y MariaDB de XAMPP, y PDO para el acceso a datos.

La página oficial de Bootstrap consultada para este trabajo indica la versión estable 5.3.8. Se usará esa versión tanto en CSS como en el bundle de JavaScript. Las carpetas vendor/bootstrap están reservadas para los archivos originales; no contienen copias ficticias ni archivos de la dependencia todavía.

Para usar las carpetas en una instalación habitual de XAMPP para Windows:

1. Descomprimir el ZIP; contiene una única carpeta raíz llamada trinidad_turismo.
2. Colocar esa carpeta en C:\xampp\htdocs\trinidad_turismo y abrirla en el editor.
3. En el siguiente paso se implementarán la entrada public/index.php, la inicialización y las rutas, y se configurará Apache para servir únicamente la carpeta public.
4. Como el código estará dentro de htdocs, también se bloqueará el acceso por el sitio localhost a app, config, database, storage y docs. El nombre public por sí solo no realiza esa protección.
5. Después de configurar la entrada y las rutas, se verificará la página base desde el navegador. Este ZIP no necesita importar una base de datos todavía.

Distribución de carpetas; las rutas son relativas a trinidad_turismo:

| Carpeta | Responsabilidad |
| --- | --- |
| `app/Controllers/Publico/` | Controladores del catálogo, fichas, solicitudes públicas e imágenes autorizadas. |
| `app/Controllers/Admin/` | Controladores de acceso administrativo, lugares, solicitudes, pagos y tarifas. |
| `app/Models/` | Clases que acceden a los datos: administrador, lugar, categoría, fotografía, publicación, solicitud, pago, vigencia y tarifa. |
| `app/Views/layouts/` | Plantillas generales para la parte pública y el panel administrativo. |
| `app/Views/components/` | Fragmentos reutilizables: navegación, tarjetas, alertas y paginación. |
| `app/Views/auth/` | Formulario de inicio de sesión del administrador. |
| `app/Views/publico/catalogo/` | Listado público y detalle de una ficha. |
| `app/Views/publico/solicitudes/` | Formulario para solicitar la publicación de un negocio. |
| `app/Views/admin/lugares/` | Listado interno y formularios de fichas. |
| `app/Views/admin/solicitudes/` | Revisión y seguimiento de solicitudes. |
| `app/Views/admin/pagos/` | Registro, confirmación, historial y consulta de vigencias. |
| `app/Views/admin/tarifas/` | Configuración de la tarifa mensual. |
| `app/Views/errors/` | Vistas de acceso denegado, página inexistente y error interno. |
| `app/Services/` | Reglas compartidas para pagos, vigencias, visibilidad de publicaciones y tratamiento de imágenes. |
| `app/Middleware/` | Comprobación de sesión administrativa y protección CSRF en solicitudes que modifican datos. |
| `app/Core/` | Componentes básicos propios del MVC: enrutador, conexión PDO, controlador base y carga de clases. |
| `config/` | Configuración general y de base de datos, accesible únicamente desde PHP. |
| `routes/` | Definición de rutas de páginas y de respuestas JSON para Fetch. |
| `database/migrations/` | Scripts SQL numerados para crear y modificar la base de datos. |
| `database/seeds/` | Datos iniciales, como categorías y tarifa de referencia. El administrador se dará de alta con un procedimiento específico y contraseña cifrada mediante hash. |
| `public/assets/css/` | Estilos CSS propios de la plataforma. |
| `public/assets/js/shared/` | Funciones comunes para peticiones Fetch, mensajes y formularios. |
| `public/assets/js/publico/` | Interacciones del catálogo, filtros y solicitudes públicas. |
| `public/assets/js/admin/` | Interacciones de fichas, solicitudes, pagos y tarifas del administrador. |
| `public/assets/img/` | Recursos propios del diseño, como logotipo e ilustraciones de interfaz. |
| `public/assets/vendor/bootstrap/css/` | Destino de la distribución oficial CSS de Bootstrap. |
| `public/assets/vendor/bootstrap/js/` | Destino de bootstrap.bundle.min.js de la misma versión del CSS. |
| `storage/uploads/lugares/` | Fotografías de fichas, fuera de la carpeta pública. Se entregarán mediante un controlador que compruebe la visibilidad de la publicación o la sesión administrativa. |
| `storage/logs/` | Registros internos de errores y diagnóstico. |
| `docs/` | Documentación del proyecto, reglas de negocio y requisitos funcionales. |

Archivos previstos para la implementación. Esta lista es una guía; los archivos todavía no están creados:

| Archivo previsto | Responsabilidad |
| --- | --- |
| `public/index.php` | Único punto de entrada PHP de las peticiones web. |
| `app/bootstrap.php` | Inicialización de configuración, carga de clases, sesión y rutas. |
| `app/Core/Router.php` | Relacionar método HTTP y URL con la acción de un controlador. |
| `app/Core/Database.php` | Crear la conexión PDO con MariaDB. |
| `app/Core/Controller.php` | Funciones comunes para renderizar vistas y responder con JSON. |
| `app/Core/Autoloader.php` | Cargar las clases propias sin requerir un framework PHP. |
| `app/Controllers/Publico/CatalogoController.php` | Atender listado, búsqueda, filtros y detalle público. |
| `app/Controllers/Publico/SolicitudController.php` | Mostrar y recibir solicitudes comerciales. |
| `app/Controllers/Publico/ImagenController.php` | Entregar fotografías según las condiciones de acceso de la ficha. |
| `app/Controllers/Admin/AuthController.php` | Gestionar inicio y cierre de sesión. |
| `app/Controllers/Admin/LugarController.php` | Gestionar las fichas turísticas y comerciales. |
| `app/Controllers/Admin/SolicitudController.php` | Revisar solicitudes y convertir las aceptadas en fichas. |
| `app/Controllers/Admin/PagoController.php` | Gestionar pagos, renovaciones, historial y próximos vencimientos. |
| `app/Controllers/Admin/TarifaController.php` | Configurar tarifas para nuevos períodos. |
| `app/Models/Administrador.php` | Datos del administrador autorizado. |
| `app/Models/Lugar.php` | Información común de lugares turísticos y establecimientos comerciales. |
| `app/Models/Categoria.php` | Clasificación del catálogo. |
| `app/Models/Fotografia.php` | Metadatos de fotografías y su asociación con una ficha. |
| `app/Models/Publicacion.php` | Aprobación y habilitación de una ficha. |
| `app/Models/Solicitud.php` | Datos y estado de la solicitud de incorporación. |
| `app/Models/Pago.php` | Importes, confirmación y responsabilidad administrativa. |
| `app/Models/Vigencia.php` | Períodos cubiertos por pagos confirmados. |
| `app/Models/Tarifa.php` | Tarifas aplicables a nuevos períodos. |
| `app/Services/PublicacionService.php` | Centralizar la regla de visibilidad para catálogo, detalle e imágenes. |
| `app/Services/PagoService.php` | Coordinar confirmación de pagos y sus períodos en una transacción. |
| `app/Services/VigenciaService.php` | Calcular vencimientos, renovaciones anticipadas y reactivaciones. |
| `app/Services/ImagenService.php` | Validar y guardar fotografías con nombres generados por el servidor. |
| `app/Middleware/AuthMiddleware.php` | Restringir las rutas administrativas a sesiones válidas. |
| `app/Middleware/CsrfMiddleware.php` | Verificar tokens CSRF en operaciones que modifican datos, incluidas las llamadas Fetch. |
| `config/app.php` | Nombre del proyecto, URL base y opciones generales. |
| `config/database.example.php` | Plantilla de configuración sin credenciales reales. |
| `config/database.php` | Configuración local de la conexión; quedará fuera del control de versiones. |
| `routes/web.php` | Rutas que muestran páginas y formularios. |
| `routes/api.php` | Rutas JSON para actualizaciones parciales mediante Fetch. |
| `public/assets/css/app.css` | Estilos compartidos y de la parte pública. |
| `public/assets/css/admin.css` | Estilos específicos del panel administrativo. |
| `public/assets/js/shared/http.js` | Peticiones Fetch, manejo de errores y envío del token CSRF. |
| `public/assets/js/publico/catalogo.js` | Actualizar resultados de búsquedas y filtros. |
| `public/assets/js/publico/solicitudes.js` | Enviar una solicitud y mostrar su resultado. |
| `public/assets/js/admin/lugares.js` | Actualizar fichas y su estado desde el panel. |
| `public/assets/js/admin/solicitudes.js` | Actualizar el seguimiento de solicitudes. |
| `public/assets/js/admin/pagos.js` | Registrar, confirmar y consultar pagos. |
| `public/assets/js/admin/tarifas.js` | Actualizar la tarifa para nuevos períodos. |

Criterios de organización:

- Los modelos acceden a los datos mediante PDO. Las vistas generan HTML y los controladores coordinan cada petición.
- Los servicios complementan MVC: mantienen las reglas de negocio compartidas, especialmente confirmación de pagos, mensualidades y visibilidad.
- Lugar representa la ficha común de un atractivo público, restaurante o bar. Las categorías los distinguen; la gratuidad o condición comercial se registra de forma explícita.
- Solicitud, Pago y Publicacion mantienen estados independientes, como se acordó en los requisitos.
- Las rutas web y JSON pasan por la misma entrada, los mismos controles administrativos y los mismos servicios. Los archivos JavaScript no acceden directamente a la base de datos.
- Fetch actualizará resultados y formularios sin recargar toda la página. La validación definitiva, los permisos, la tarifa y los vencimientos se comprobarán en PHP.
- La visibilidad pública se comprobará al consultar los datos. No dependerá únicamente de ejecutar una tarea programada de vencimientos.
- Las fotografías de fichas se conservan en storage/uploads/lugares. Un controlador comprobará el acceso antes de entregarlas. Los recursos propios de la interfaz sí estarán en public/assets/img.
- La vista pública de un negocio requiere aprobación, habilitación y cobertura vigente de un pago confirmado. Una suspensión administrativa seguirá vigente aunque se registre otro pago.
- Los lugares turísticos públicos serán gratuitos. Los negocios comerciales tendrán una tarifa mensual inicial de referencia de Bs 250 por ficha, con confirmación manual del pago.
- Se conservarán las fichas y el historial al vencer. Las renovaciones anticipadas continuarán el período actual y las reactivaciones comenzarán en la fecha acordada.
- Los nombres de clases y archivos respetarán las mayúsculas y los espacios de nombres. Las consultas SQL se escribirán en modelos y las comprobaciones de vigencia en sus servicios; las vistas no ejecutarán consultas SQL.

Orden previsto de implementación: entrada y rutas MVC; configuración y base de datos; autenticación del administrador; fichas turísticas y catálogo; solicitudes comerciales; pagos, tarifas y vigencias; interacciones Fetch por módulo.

Referencias técnicas consultadas:

- Bootstrap y versión actual: https://getbootstrap.com/
- Introducción e incorporación de Bootstrap: https://getbootstrap.com/docs/5.3/getting-started/introduction/
- Ubicación de proyectos en XAMPP: https://www.apachefriends.org/es/faq_windows.html
- PDO: https://www.php.net/manual/es/book.pdo.php
- Fetch: https://developer.mozilla.org/es/docs/Web/API/Fetch_API/Using_Fetch
