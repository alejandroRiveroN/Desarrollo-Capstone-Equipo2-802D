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

// La ruta POST para autenticar ahora llama directamente al método del controlador.
Flight::route('POST /login', ['App\Controllers\AuthController', 'authenticate']);

Flight::route('GET /logout', function () {
    session_unset();
    session_destroy();
    
    // cambie la ruta de redirección al momento de deslogearse este te enviara a la página de inicio (landing page)
    $landing_url = 'http://' . $_SERVER['HTTP_HOST'] . Flight::get('base_url') . '/';
    Flight::redirect($landing_url);
});


// --- RUTAS PRINCIPALES ---
// La ruta raíz ahora muestra la landing page.
Flight::route('GET /', function(){
    Flight::render('landingpage.php');
});

// La ruta del dashboard ahora es /dashboard.
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
Flight::route('GET /clientes/imprimir', ['App\Controllers\ClientController', 'print']);


// --- RUTAS DE USUARIOS ---
Flight::route('GET /usuarios', ['App\Controllers\UserController', 'index']);
Flight::route('GET /usuarios/crear', ['App\Controllers\UserController', 'create']);
Flight::route('POST /usuarios', ['App\Controllers\UserController', 'store']);
Flight::route('GET /usuarios/editar/@id', ['App\Controllers\UserController', 'edit']);
Flight::route('POST /usuarios/editar/@id', ['App\Controllers\UserController', 'update']);
Flight::route('POST /usuarios/eliminar/@id', ['App\Controllers\UserController', 'delete']);

// --- RUTAS DE TIPOS DE CASO ---
// Estandarizado a /casos/tipos para que coincida con el menú
Flight::route('GET /casos/tipos', ['App\Controllers\TipoCasoController', 'index']);
Flight::route('POST /casos/tipos', ['App\Controllers\TipoCasoController', 'store']);
Flight::route('GET /casos/tipos/editar/@id', ['App\Controllers\TipoCasoController', 'edit']);
Flight::route('POST /casos/tipos/editar/@id', ['App\Controllers\TipoCasoController', 'update']);
Flight::route('POST /casos/tipos/eliminar/@id', ['App\Controllers\TipoCasoController', 'delete']);

// --- RUTAS DE CAMBIAR CONTRASEÑA ---
// Estandarizado a /password/cambiar
Flight::route('GET /password/cambiar', ['App\Controllers\PasswordController', 'index']);
Flight::route('POST /password/cambiar', ['App\Controllers\PasswordController', 'update']);

// --- RUTAS DE CONTRASEÑA OLVIDADA ---
Flight::route('GET|POST /contraseña_olvidada', ['App\Controllers\ForgotPasswordController', 'sendResetLink']);
Flight::route('GET|POST /reset_contraseña', ['App\Controllers\ForgotPasswordController', 'resetPassword']);

// --- RUTAS DE BACKUP ---
Flight::route('GET /backup', ['App\Controllers\BackupController', 'index']);
Flight::route('POST /backup', ['App\Controllers\BackupController', 'generate']);


// --- RUTAS DE ADMINISTRACIÓN (LIMPIEZA) ---

Flight::route('GET /admin/limpieza', ['App\Controllers\AdminController', 'limpieza']);
Flight::route('POST /admin/limpieza/test', ['App\Controllers\AdminController', 'limpiezaTest']);
Flight::route('POST /admin/limpieza/total', ['App\Controllers\AdminController', 'limpiezaTotal']);
Flight::route('POST /admin/limpieza/reset', ['App\Controllers\AdminController', 'limpiezaReset']);


// --- RUTAS DE TICKETS ---


// Muestra el formulario para crear un nuevo ticket
Flight::route('GET /tickets/crear', ['App\Controllers\TicketController', 'create']);

// Procesa la creación de un nuevo ticket
Flight::route('POST /tickets', ['App\Controllers\TicketController', 'store']);


// --- RUTAS PARA VER Y GESTIONAR UN TICKET ESPECÍFICO (Estandarizado a /tickets/ver/@id) ---

// Muestra la página de un ticket
Flight::route('GET /tickets/ver/@id', ['App\Controllers\TicketController', 'show']);

// Procesa la adición de un comentario
Flight::route('POST /tickets/ver/@id/comentario', ['App\Controllers\TicketController', 'addComment']);

// Procesa el cambio de estado
Flight::route('POST /tickets/ver/@id/estado', ['App\Controllers\TicketController', 'updateStatus']);

// Procesa la asignación de agente
Flight::route('POST /tickets/ver/@id/asignar', ['App\Controllers\TicketController', 'assignAgent']);

// Procesa la actualización de costos
Flight::route('POST /tickets/ver/@id/costo', ['App\Controllers\TicketController', 'updateCost']);

// Procesa la anulación de un ticket
Flight::route('POST /tickets/ver/@id/anular', ['App\Controllers\TicketController', 'cancel']);


// Imprimir Tickets
Flight::route('GET /tickets/imprimir', ['App\Controllers\TicketController', 'print']);

// --- RUTAS DE EXPORTACIÓN DE TICKETS ---
Flight::route('GET /tickets/exportar/excel', ['App\Controllers\TicketController', 'exportExcel']);
Flight::route('GET /tickets/exportar/pdf', ['App\Controllers\TicketController', 'exportPdf']);


Flight::start();
