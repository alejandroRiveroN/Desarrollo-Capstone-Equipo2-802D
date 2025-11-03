<?php
require '../vendor/autoload.php';

// Iniciar la sesión
session_start();

// Incluir la configuración de la base de datos y registrar la conexión con Flight
require_once '../config/database.php';
Flight::map('db', function () {
    global $pdo; // Usar la variable $pdo global del archivo database.php
    return $pdo;
});

// Configurar la ruta de las vistas
Flight::set('flight.views.path', '../views');

// Configurar la URL base dinámicamente para que funcione en subdirectorios (como en XAMPP)
$base_path = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
Flight::set('base_url', $base_path);

// --- RUTAS DE AUTENTICACIÓN ---
Flight::route('GET /login', ['App\Controllers\AuthController', 'login']);
Flight::route('POST /login', ['App\Controllers\AuthController', 'authenticate']);
Flight::route('GET /logout', function () {
    session_unset();
    session_destroy();

    $landing_url = 'http://' . $_SERVER['HTTP_HOST'] . Flight::get('base_url') . '/';
    Flight::redirect($landing_url);
});

// --- RUTAS PRINCIPALES ---
Flight::route('GET /', function () {
    Flight::render('landingpage.php'); // Renderiza PHP nativo, no display()
});
Flight::route('POST /contact', ['App\Controllers\ContactController', 'send']);
Flight::route('GET /dashboard', ['App\Controllers\DashboardController', 'index']);

// --- RUTAS DE CLIENTES ---
Flight::route('GET /clientes', ['App\Controllers\ClientController', 'index']);
Flight::route('GET /clientes/crear', ['App\Controllers\ClientController', 'create']);
Flight::route('POST /clientes', ['App\Controllers\ClientController', 'store']);
Flight::route('GET /clientes/editar/@id', ['App\Controllers\ClientController', 'edit']);
Flight::route('POST /clientes/editar/@id', ['App\Controllers\ClientController', 'update']);
Flight::route('POST /clientes/eliminar/@id', ['App\Controllers\ClientController', 'delete']);
Flight::route('GET /clientes/exportar/excel', ['App\Controllers\ClientController', 'exportExcel']);
Flight::route('GET /clientes/exportar/pdf', ['App\Controllers\ClientController', 'exportPdf']);
Flight::route('GET /clientes/exportar/imprimir', ['App\Controllers\ClientController', 'print']);

// Mostrar formulario de registro público
Flight::route('GET /registro_cliente', function(){
    Flight::render('registro_cliente.php', ['mensaje_error' => '', 'mensaje_exito' => '']);
});

// Procesar registro público
Flight::route('POST /registro_cliente', ['App\Controllers\ClientController', 'publicRegister']);

// --- RUTAS DE USUARIOS ---
Flight::route('GET /usuarios', ['App\Controllers\UserController', 'index']);
Flight::route('GET /usuarios/crear', ['App\Controllers\UserController', 'create']);
Flight::route('POST /usuarios', ['App\Controllers\UserController', 'store']);
Flight::route('GET /usuarios/editar/@id', ['App\Controllers\UserController', 'edit']);
Flight::route('POST /usuarios/editar/@id', ['App\Controllers\UserController', 'update']);
Flight::route('POST /usuarios/eliminar/@id', ['App\Controllers\UserController', 'delete']);

// --- RUTAS DE TIPOS DE CASO ---
Flight::route('GET /casos/tipos', ['App\Controllers\TipoCasoController', 'index']);
Flight::route('POST /casos/tipos', ['App\Controllers\TipoCasoController', 'store']);
Flight::route('GET /casos/tipos/editar/@id', ['App\Controllers\TipoCasoController', 'edit']);
Flight::route('POST /casos/tipos/editar/@id', ['App\Controllers\TipoCasoController', 'update']);
Flight::route('POST /casos/tipos/eliminar/@id', ['App\Controllers\TipoCasoController', 'delete']);

// --- RUTAS DE CAMBIAR CONTRASEÑA ---
Flight::route('GET /password/cambiar', ['App\Controllers\PasswordController', 'index']);
Flight::route('POST /password/cambiar', ['App\Controllers\PasswordController', 'update']);

// --- RUTAS DE CONTRASEÑA OLVIDADA ---
Flight::route('GET|POST /contraseña_olvidada', ['App\Controllers\ForgotPasswordController', 'sendResetLink']);
Flight::route('GET|POST /reset_contraseña', ['App\Controllers\ForgotPasswordController', 'resetPassword']);

// --- RUTAS DE BACKUP ---
Flight::route('GET /backup', ['App\Controllers\BackupController', 'index']);
Flight::route('POST /backup', ['App\Controllers\BackupController', 'generate']);

// --- RUTAS DE ADMINISTRACIÓN ---
Flight::route('GET /admin/limpieza', ['App\Controllers\AdminController', 'limpieza']);
Flight::route('POST /admin/limpieza/test', ['App\Controllers\AdminController', 'limpiezaTest']);
Flight::route('POST /admin/limpieza/total', ['App\Controllers\AdminController', 'limpiezaTotal']);
Flight::route('POST /admin/limpieza/reset', ['App\Controllers\AdminController', 'limpiezaReset']);
Flight::route('GET /admin/mensajes', ['App\Controllers\AdminController', 'viewMessages']);
Flight::route('GET /admin/mensajes/ver/@id', ['App\Controllers\AdminController', 'viewMessage']);
Flight::route('POST /admin/mensajes/responder/@id', ['App\Controllers\AdminController', 'replyToMessage']);
Flight::route('POST /admin/mensajes/eliminar/@id', ['App\Controllers\AdminController', 'deleteMessage']);

// COTIZACIONES CLIENTE 
\Flight::route('GET /cotizaciones',        ['App\Controllers\CotizacionController', 'myIndex']);   
\Flight::route('GET /cotizaciones/crear',  ['App\Controllers\CotizacionController', 'createForm']); 
\Flight::route('POST /cotizaciones',       ['App\Controllers\CotizacionController', 'store']);       
\Flight::route('GET /cotizaciones/ver/@id',['App\Controllers\CotizacionController', 'showClient']);  

// COTIZACIONES ADMIN / SUPERVISOR 
\Flight::route('GET /admin/cotizaciones',          ['App\Controllers\CotizacionController', 'indexAdmin']);
\Flight::route('GET /admin/cotizaciones/ver/@id',  ['App\Controllers\CotizacionController', 'showAdmin']);
\Flight::route('POST /admin/cotizaciones/responder/@id', ['App\Controllers\CotizacionController', 'respond']);

// --- RUTAS DE TICKETS ---
Flight::route('GET /tickets/crear', ['App\Controllers\TicketController', 'create']);
Flight::route('POST /tickets', ['App\Controllers\TicketController', 'store']);
Flight::route('GET /tickets/ver/@id_ticket:[0-9]+', ['App\Controllers\TicketController', 'show']);
Flight::route('POST /tickets/ver/@id_ticket/comentario', ['App\Controllers\TicketController', 'addComment']);
Flight::route('POST /tickets/ver/@id_ticket/estado', ['App\Controllers\TicketController', 'updateStatus']);
Flight::route('POST /tickets/ver/@id_ticket/asignar', ['App\Controllers\TicketController', 'assignAgent']);
Flight::route('POST /tickets/ver/@id_ticket/costo', ['App\Controllers\TicketController', 'updateCost']);
Flight::route('POST /tickets/ver/@id_ticket/anular', ['App\Controllers\TicketController', 'cancel']);
Flight::route('POST /tickets/eliminar/@id', ['App\Controllers\TicketController', 'delete']);
Flight::route('GET /tickets/imprimir', ['App\Controllers\TicketController', 'print']);
Flight::route('GET /tickets/exportar/excel', ['App\Controllers\TicketController', 'exportExcel']);
Flight::route('GET /tickets/exportar/pdf', ['App\Controllers\TicketController', 'exportPdf']);

// --- INICIAR FLIGHT ---
Flight::start();
