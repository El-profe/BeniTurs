<?php
// Prueba el controlador real sin conectar ni modificar la base de datos.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/Core/Autoloader.php';
App\Core\Autoloader::register();

class AdministradorPrueba extends App\Models\Administrador {
    public bool $hashModificado = false;
    public function __construct() {}
    public function buscarPorIdentificador(string $identificador): ?array {
        return ['id_administrador'=>1, 'nombre'=>'Prueba', 'usuario'=>'prueba',
            'password_hash'=>password_hash(' clave de prueba ', PASSWORD_DEFAULT)];
    }
    public function actualizarUltimoAcceso(int $idAdministrador): void {}
    public function actualizarPasswordHash(int $idAdministrador, string $nuevoHash): void {
        $this->hashModificado = true;
    }
}

if (isset($argv[1])) {
    $dir = sys_get_temp_dir() . '/trinidad-auth-test-' . bin2hex(random_bytes(8));
    mkdir($dir,0700);
    session_save_path($dir);
    session_start();
    $model = new AdministradorPrueba();
    $expected = $argv[1] === 'correcta';
    register_shutdown_function(static function () use ($model, $expected, $dir): void {
        $ok = !empty($_SESSION['admin_id']) === $expected && !$model->hashModificado;
        session_destroy();
        rmdir($dir);
        if (!$ok) { fwrite(STDERR,"Fallo de autenticacion\n"); exit(1); }
    });
    $reflection = new ReflectionClass(App\Controllers\Admin\AuthController::class);
    $controller = $reflection->newInstanceWithoutConstructor();
    $reflection->getProperty('adminModel')->setValue($controller,$model);
    $reflection->getProperty('config')->setValue($controller,['base_url'=>'/prueba']);
    $_SERVER['REQUEST_METHOD']='POST';
    $_POST = [
        'usuario'=>'prueba',
        'password'=>match ($argv[1]) {
            'correcta'=>' clave de prueba ',
            'respaldo'=>'AdminTrinidad2026!',
            default=>'incorrecta'
        },
        'csrf_token'=>App\Middleware\CsrfMiddleware::obtenerToken()
    ];
    $controller->procesarLogin();
    exit(1);
}
foreach (['correcta','incorrecta','respaldo'] as $case) {
    $process=proc_open([PHP_BINARY,__FILE__,$case],[1=>['pipe','w'],2=>['pipe','w']],$pipes);
    if (!is_resource($process)) throw new RuntimeException('No se pudo iniciar la prueba.');
    $out=stream_get_contents($pipes[1]); $err=stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    if (proc_close($process)!==0) throw new RuntimeException($out.$err);
    echo "OK: autenticacion $case sin cambiar el hash\n";
}
