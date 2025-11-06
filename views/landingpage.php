<?php
// Unificar la gestión de mensajes (éxito y error) desde la sesión o URL.
$mensaje = '';
$tipo_mensaje = '';

if (isset($_SESSION['mensaje_exito'])) {
    $mensaje = $_SESSION['mensaje_exito'];
    $tipo_mensaje = 'exito';
    unset($_SESSION['mensaje_exito']);
} elseif (isset($_SESSION['mensaje_error'])) {
    $mensaje = $_SESSION['mensaje_error'];
    $tipo_mensaje = 'error';
    unset($_SESSION['mensaje_error']);
} elseif (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $mensaje = '¡Registro exitoso! Ya puedes iniciar sesión.';
        $tipo_mensaje = 'exito';
    } elseif ($_GET['status'] === 'error') {
        $mensaje = 'Ocurrió un error al registrarte. Intenta nuevamente.';
        $tipo_mensaje = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCE | Sistema de Soporte TI Especializado</title>
    <!-- Carga de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Configuración personalizada de Tailwind -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'mce-primary': '#1A2E44',
                        'mce-secondary': '#38B2AC',
                        'mce-bg': '#F7F7F7',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <!-- Fuente Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <!-- Estilos Unificados (main.css) -->
    <link rel="stylesheet" href="<?php echo Flight::get('base_url'); ?>/css/main.css?v=1.1">
</head>
<body class="font-sans bg-mce-bg text-gray-800">
    <?php if ($mensaje): ?>
        <div class="
            <?php echo $tipo_mensaje === 'exito' ? 'bg-green-500' : 'bg-red-500'; ?>
            text-white text-center p-3 font-semibold"
            role="alert"
        >
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <!-- Navbar -->
    <nav class="bg-mce-primary shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <a href="#" class="flex items-center space-x-2">
                    <span class="text-3xl font-extrabold text-white tracking-wider">MCE</span>
                    <span class="text-sm text-gray-300 hidden md:inline">Mantenimientos Computacionales Especializados</span>
                </a>
                <div class="flex items-center space-x-2 md:space-x-4">
                    <a href="#" class="text-white hover:text-mce-secondary px-3 py-2 rounded-md text-base font-medium transition duration-150 ease-in-out">Inicio</a>
                    <a href="#services" class="text-white hover:text-mce-secondary px-3 py-2 rounded-md text-base font-medium transition duration-150 ease-in-out">Servicios</a>
                    <a href="#contact" class="text-white hover:text-mce-secondary px-3 py-2 rounded-md text-base font-medium transition duration-150 ease-in-out">Contacto</a>
                    <!-- Botón de Registrarse (sin funcionalidad de momento) -->
                    <?php if (!isset($_SESSION['id_usuario'])): ?>
                        <!-- Si NO está logueado -->
                        <a href="<?php echo Flight::get('base_url'); ?>/registro_cliente" 
                            title="Registrar nuevo cliente" 
                            class="bg-mce-secondary hover:bg-teal-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                            Registrarse
                        </a>

                        <a href="<?php echo Flight::get('base_url'); ?>/login" 
                            class="bg-mce-secondary hover:bg-teal-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                            Ingresar
                        </a>

                    <?php else: ?>
                        <!-- Si ya está logueado -->
                        <a href="<?php echo Flight::get('base_url'); ?>/dashboard"
                            class="bg-mce-secondary hover:bg-teal-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                            Ir al Panel
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="min-h-screen">
        <!-- Carrusel -->
        <section id="carousel" class="relative w-full overflow-hidden h-96 md:h-[450px] bg-gray-900 shadow-xl">
            <div id="carousel-container" class="relative w-full h-full">
                <div class="carousel-slide absolute inset-0 opacity-100" data-index="0" style="background-image: url('https://images.ctfassets.net/63bmaubptoky/628gCiWkYRbvCawS5yb8jV/bbd222a6c0c9dc155ac9bf151c69c085/que-es-help-desk-MX-Capterra-header.png'); background-size: cover; background-position: center;">
                    <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                        <div class="text-center p-6 rounded-xl bg-black bg-opacity-20">
                            <h2 class="text-4xl md:text-6xl font-bold text-white mb-2 text-shadow-strong">Soporte TI Especializado</h2>
                            <p class="text-xl md:text-2xl text-gray-200 text-shadow-strong">Máxima eficiencia y seguridad para tu infraestructura.</p>
                        </div>
                    </div>
                </div>
                <div class="carousel-slide absolute inset-0 opacity-0" data-index="1" style="background-image: url('https://www.stelorder.com/wp-content/uploads/2022/01/portada-mantenimiento-correctivo.jpg'); background-size: cover; background-position: center;">
                    <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                        <div class="text-center p-6 rounded-xl bg-black bg-opacity-20">
                            <h2 class="text-4xl md:text-6xl font-bold text-white mb-2 text-shadow-strong">Mantenimiento Preventivo</h2>
                            <p class="text-xl md:text-2xl text-gray-200 text-shadow-strong">Evita problemas antes de que ocurran.</p>
                        </div>
                    </div>
                </div>
                <div class="carousel-slide absolute inset-0 opacity-0" data-index="2" style="background-image: url('https://cdn.crn.in/wp-content/uploads/2018/06/22091210/Digital-Partnership.jpg'); background-size: cover; background-position: center top;">
                    <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                        <div class="text-center p-6 rounded-xl bg-black bg-opacity-20">
                            <h2 class="text-4xl md:text-6xl font-bold text-white mb-2 text-shadow-strong">Tu Socio Tecnológico de Confianza</h2>
                            <p class="text-xl md:text-2xl text-gray-200 text-shadow-strong">Asistencia rápida y personalizada.</p>
                        </div>
                    </div>
                </div>
            </div>
            <button id="prevBtn" class="absolute top-1/2 left-4 transform -translate-y-1/2 bg-white bg-opacity-20 hover:bg-opacity-40 text-white p-3 rounded-full transition duration-300 z-10 hidden sm:block">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <button id="nextBtn" class="absolute top-1/2 right-4 transform -translate-y-1/2 bg-white bg-opacity-20 hover:bg-opacity-40 text-white p-3 rounded-full transition duration-300 z-10 hidden sm:block">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
            <div id="indicators" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2"></div>
        </section>

        <!-- Servicios -->
        <section id="services" class="py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-4xl font-extrabold text-mce-primary text-center mb-10">Nuestros Servicios Especializados</h2>
                <p class="text-xl text-gray-600 text-center mb-16 max-w-3xl mx-auto">Soluciones integrales para garantizar la operatividad y seguridad de tus sistemas.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Tarjetas de servicio (4) -->
                    <?php
                    // Ejemplo de cómo podrías generar dinámicamente las tarjetas 
                    $servicios = [
                        ['icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.28 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'titulo' => 'Mantenimiento Preventivo', 'desc' => 'Optimiza el rendimiento y prolonga la vida útil de tus equipos.'],
                        ['icon' => 'M9.75 17L9 20l-1 1h8l-1-1v-3.25m-7.25 0h14.5c.345 0 .625-.28.625-.625v-8.75c0-.345-.28-.625-.625-.625h-14.5c-.345 0-.625.28-.625.625v8.75c0 .345.28.625.625.625zM12 9h.01', 'titulo' => 'Soporte Remoto', 'desc' => 'Asistencia inmediata y eficiente a distancia.'],
                        ['icon' => 'M12 15v2m-6-4h12V9a6 6 0 00-12 0v2zM12 4a3 3 0 100 6 3 3 0 000-6z', 'titulo' => 'Seguridad Informática', 'desc' => 'Protección avanzada contra virus y amenazas cibernéticas.'],
                        ['icon' => 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01', 'titulo' => 'Redes y Servidores', 'desc' => 'Configuración y monitorización de infraestructura empresarial.']
                    ];
                    foreach ($servicios as $s) {
                        echo '
                        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-2xl transition duration-300 transform hover:-translate-y-1 border-t-4 border-mce-secondary">
                            <div class="text-mce-secondary mb-4">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="' . $s['icon'] . '"></path></svg>
                            </div>
                            <h3 class="text-xl font-semibold text-mce-primary mb-2">' . htmlspecialchars($s['titulo']) . '</h3>
                            <p class="text-gray-600 text-sm">' . htmlspecialchars($s['desc']) . '</p>
                        </div>';
                    }
                    ?>
                </div>
                <div class="text-center mt-12">
                    <a href="#support-plans" class="bg-mce-primary hover:bg-gray-700 text-white font-bold py-3 px-8 rounded-lg text-lg shadow-xl transition duration-300 transform hover:scale-105 inline-block">
                        Ver Planes de Soporte Completos
                    </a>
                </div>
            </div>
        </section>

        <!-- Tipos de Servicio -->
        <section id="support-plans" class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-4xl font-extrabold text-mce-primary text-center mb-4">Nuestros Tipos de Servicio</h2>
                <p class="text-xl text-gray-600 text-center mb-16 max-w-3xl mx-auto">Ofrecemos diferentes niveles de soporte para adaptarnos a las necesidades de tu empresa.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Servicio Esencial -->
                    <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200 flex flex-col">
                        <h3 class="text-2xl font-bold text-mce-primary">Servicio Esencial</h3>
                        <p class="text-gray-500 mt-2">Ideal para startups y pequeñas empresas.</p>
                        <ul class="mt-6 space-y-4 text-gray-600 flex-grow">
                            <li class="flex items-center"><svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Soporte Remoto</li>
                            <li class="flex items-center"><svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Horario de Oficina</li>
                            <li class="flex items-center"><svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Resolución de problemas básicos</li>
                        </ul>
                        <a href="#contact" class="mt-8 block w-full bg-white hover:bg-gray-100 text-mce-secondary text-center font-bold py-3 px-6 rounded-lg border border-mce-secondary transition">Consultar</a>
                    </div>

                    <!-- Servicio Avanzado (Destacado) -->
                    <div class="bg-mce-primary text-white p-8 rounded-xl shadow-2xl border-4 border-mce-secondary flex flex-col relative transform md:scale-105">
                        <span class="absolute top-0 right-4 -mt-4 bg-mce-secondary text-white text-xs font-bold px-3 py-1 rounded-full">MÁS POPULAR</span>
                        <h3 class="text-2xl font-bold">Servicio Avanzado</h3>
                        <p class="text-gray-300 mt-2">La solución completa para empresas en crecimiento.</p>
                        <ul class="mt-6 space-y-4 flex-grow">
                            <li class="flex items-center"><svg class="w-5 h-5 text-mce-secondary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Soporte Remoto y Presencial</li>
                            <li class="flex items-center"><svg class="w-5 h-5 text-mce-secondary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Soporte con prioridad</li>
                            <li class="flex items-center"><svg class="w-5 h-5 text-mce-secondary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Gestión de seguridad</li>
                            <li class="flex items-center"><svg class="w-5 h-5 text-mce-secondary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Mantenimiento Preventivo</li>
                        </ul>
                        <a href="#contact" class="mt-8 block w-full bg-mce-secondary hover:bg-teal-500 text-white text-center font-bold py-3 px-6 rounded-lg transition">Consultar</a>
                    </div>

                    <!-- Servicio Corporativo -->
                    <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200 flex flex-col">
                        <h3 class="text-2xl font-bold text-mce-primary">Servicio Corporativo</h3>
                        <p class="text-gray-500 mt-2">Soluciones a medida para grandes empresas.</p>
                        <ul class="mt-6 space-y-4 text-gray-600 flex-grow">
                            <li class="flex items-center"><svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Todo lo del Servicio Avanzado</li>
                            <li class="flex items-center"><svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Agente Dedicado</li>
                            <li class="flex items-center"><svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Consultoría Estratégica</li>
                            <li class="flex items-center"><svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Reportes de rendimiento</li>
                        </ul>
                        <a href="#contact" class="mt-8 block w-full bg-white hover:bg-gray-100 text-mce-secondary text-center font-bold py-3 px-6 rounded-lg border border-mce-secondary transition">Contactar</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Beneficios de Registrarse -->
        <section id="benefits" class="py-16 px-4 sm:px-6 lg:px-8 bg-white">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-4xl font-extrabold text-mce-primary mb-4">Beneficios de Registrarte</h2>
                <p class="text-xl text-gray-600 mb-12">Crea una cuenta y accede a un portal exclusivo para gestionar todas tus solicitudes.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="text-center p-6 max-w-xs">
                        <svg class="w-16 h-16 mx-auto text-mce-secondary mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        <h3 class="text-xl font-semibold text-mce-primary">Gestión de Tickets</h3>
                        <p class="text-gray-600 mt-2">Crea, visualiza y gestiona el historial de todos tus tickets de soporte desde un solo lugar.</p>
                    </div>
                    <div class="text-center p-6 max-w-xs">
                        <svg class="w-16 h-16 mx-auto text-mce-secondary mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <h3 class="text-xl font-semibold text-mce-primary">Solicitud de Cotizaciones</h3>
                        <p class="text-gray-600 mt-2">Pide cotizaciones para nuevos servicios o equipos y recibe respuestas directamente en tu panel.</p>
                    </div>
                    <div class="text-center p-6 max-w-xs mx-auto">
                        <svg class="w-16 h-16 mx-auto text-mce-secondary mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <h3 class="text-xl font-semibold text-mce-primary">Atención Personalizada</h3>
                        <p class="text-gray-600 mt-2">Accede a un canal de comunicación directo con nuestros agentes para un seguimiento detallado.</p>
                    </div>
                    <div class="text-center p-6 max-w-xs mx-auto">
                        <svg class="w-16 h-16 mx-auto text-mce-secondary mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        <h3 class="text-xl font-semibold text-mce-primary">Análisis de Datos</h3>
                        <p class="text-gray-600 mt-2">Conoce la duración promedio de tus casos y otras métricas de rendimiento desde tu panel.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Nuestro Proceso de Trabajo -->
        <section id="work-process" class="py-16 px-4 sm:px-6 lg:px-8 bg-gray-50">
            <div class="max-w-6xl mx-auto text-center">
                <h2 class="text-4xl font-extrabold text-mce-primary mb-4">Nuestro Proceso de Trabajo</h2>
                <p class="text-xl text-gray-600 mb-16 max-w-3xl mx-auto">Un flujo de trabajo transparente y eficiente para garantizar la mejor atención.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Paso 1 -->
                    <div class="bg-white p-6 rounded-xl shadow-lg text-center">
                        <div class="bg-mce-secondary text-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-mce-primary mb-2">1. Contacto y Diagnóstico</h3>
                        <p class="text-gray-600 text-sm">El cliente reporta un problema a través de nuestro portal o línea directa.</p>
                    </div>
                    <!-- Paso 2 -->
                    <div class="bg-white p-6 rounded-xl shadow-lg text-center">
                        <div class="bg-mce-secondary text-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-mce-primary mb-2">2. Asignación y Solución</h3>
                        <p class="text-gray-600 text-sm">Un agente experto toma el caso y comienza a trabajar en la solución de inmediato.</p>
                    </div>
                    <!-- Paso 3 -->
                    <div class="bg-white p-6 rounded-xl shadow-lg text-center">
                        <div class="bg-mce-secondary text-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-mce-primary mb-2">3. Verificación y Cierre</h3>
                        <p class="text-gray-600 text-sm">Confirmamos con el cliente que el problema ha sido resuelto satisfactoriamente.</p>
                    </div>
                    <!-- Paso 4 -->
                    <div class="bg-white p-6 rounded-xl shadow-lg text-center">
                        <div class="bg-mce-secondary text-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-mce-primary mb-2">4. Seguimiento</h3>
                        <p class="text-gray-600 text-sm">Realizamos un seguimiento posterior para asegurar la estabilidad y satisfacción a largo plazo.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Preguntas Frecuentes (FAQ) -->
        <section id="faq" class="py-16 px-4 sm:px-6 lg:px-8 bg-gray-50">
            <div class="max-w-3xl mx-auto">
                <h2 class="text-4xl font-extrabold text-mce-primary text-center mb-12">Preguntas frecuentes sobre el Soporte TI</h2>
                <div class="space-y-4">
                    <!-- FAQ Item 1 -->
                    <details class="group bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
                        <summary class="flex justify-between items-center font-semibold text-lg text-mce-primary cursor-pointer list-none">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3 text-mce-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>¿Qué tipo de soporte ofrecen?</span>
                            </div>
                            <span class="group-open:rotate-180 transition-transform duration-300">
                                <svg class="w-6 h-6 text-mce-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </summary>
                        <p class="mt-4 text-gray-600">
                            Ofrecemos un soporte TI integral que incluye mantenimiento preventivo, soporte remoto, seguridad informática, y gestión de redes y servidores. Nos adaptamos a las necesidades específicas de cada cliente, desde pequeñas empresas hasta grandes corporaciones.
                        </p>
                    </details>
                    <!-- FAQ Item 2 -->
                    <details class="group bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
                        <summary class="flex justify-between items-center font-semibold text-lg text-mce-primary cursor-pointer list-none">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3 text-mce-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>¿Cuál es su tiempo de respuesta?</span>
                            </div>
                            <span class="group-open:rotate-180 transition-transform duration-300">
                                <svg class="w-6 h-6 text-mce-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </summary>
                        <p class="mt-4 text-gray-600">
                            Nuestro tiempo de respuesta varía según la prioridad del ticket. Para problemas urgentes, garantizamos una respuesta inicial en menos de 1 hora. Utilizamos un sistema de tickets para asegurar que cada solicitud sea atendida de manera organizada y eficiente.
                        </p>
                    </details>
                    <!-- FAQ Item 3 -->
                    <details class="group bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
                        <summary class="flex justify-between items-center font-semibold text-lg text-mce-primary cursor-pointer list-none">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3 text-mce-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                <span>¿Cómo puedo registrarme para obtener soporte?</span>
                            </div>
                            <span class="group-open:rotate-180 transition-transform duration-300">
                                <svg class="w-6 h-6 text-mce-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </summary>
                        <p class="mt-4 text-gray-600">
                            Puedes registrarte fácilmente haciendo clic en el botón "Registrarse" en la parte superior de la página. Una vez registrado, tendrás acceso a nuestro portal de clientes para crear tickets de soporte, solicitar cotizaciones y ver el historial de tus solicitudes.
                        </p>
                    </details>
                </div>
            </div>
        </section>

        <!-- Nuestros Clientes -->
        <section id="clients" class="py-16 px-4 sm:px-6 lg:px-8 bg-white">
            <div class="max-w-6xl mx-auto text-center">
                <h2 class="text-4xl font-extrabold text-mce-primary mb-4">Nuestros Clientes</h2>
                <p class="text-xl text-gray-600 mb-12">Empresas que confían en nuestra tecnología y soporte.</p>
                <div class="flex justify-center items-center flex-wrap gap-x-12 gap-y-8">
                    <!-- Logos de ejemplo. Reemplaza con los de tus clientes. -->
                    <img src="https://www.limchile.cl/wp-content/uploads/2022/12/Foto-portada.png" alt="Logo Cliente Transistor" class="h-10 w-auto grayscale hover:grayscale-0 transition duration-300">
                    <img src="https://landportal.org/sites/default/files/2024-03/Coca-Cola-Logo-2.jpg" alt="Logo Cliente Reform" class="h-10 w-auto grayscale hover:grayscale-0 transition duration-300">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRnEZqpd0Yv6O06ZDxo0YHST2XezQ2Gf6eeBw&s" alt="Logo Cliente Tuple" class="h-10 w-auto grayscale hover:grayscale-0 transition duration-300">
                </div>
            </div>
        </section>

        <!-- Formulario de Contacto -->
        <section id="contact" class="py-16 px-4 sm:px-6 lg:px-8 bg-white">
            <div class="max-w-3xl mx-auto">
                <h2 class="text-4xl font-extrabold text-mce-primary text-center mb-4">Ponte en Contacto</h2>
                <p class="text-xl text-gray-600 text-center mb-12">¿Tienes alguna pregunta? Envíanos un mensaje y te responderemos a la brevedad.</p>
                
                <form action="<?php echo Flight::get('base_url'); ?>/contact" method="POST" class="space-y-6">
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre y Apellidos</label>
                        <div class="mt-1">
                            <input type="text" name="nombre" id="nombre" required class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-mce-secondary focus:border-mce-secondary transition">
                        </div>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                        <div class="mt-1">
                            <input type="email" name="email" id="email" required class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-mce-secondary focus:border-mce-secondary transition">
                        </div>
                    </div>
                    <div>
                        <label for="mensaje" class="block text-sm font-medium text-gray-700">Mensaje</label>
                        <div class="mt-1">
                            <textarea id="mensaje" name="mensaje" rows="4" required class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-mce-secondary focus:border-mce-secondary transition"></textarea>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="bg-mce-secondary hover:bg-teal-600 text-white font-bold py-3 px-10 rounded-lg text-lg shadow-xl transition duration-300 transform hover:scale-105">Enviar Mensaje</button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-mce-primary text-white py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h4 class="text-2xl font-extrabold text-white mb-3">MCE</h4>
                    <p class="text-gray-400 text-sm">Tu solución definitiva en Mantenimiento y Soporte TI.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-mce-secondary mb-4">Datos de Contacto</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><span class="font-bold">Dirección:</span> Calle Ficticia #123, Ciudad Digital</li>
                        <li><span class="font-bold">Teléfono:</span> +56 9 1234 5678 (Soporte 24/7)</li>
                        <li><span class="font-bold">Email:</span> contacto@mce-ti.com</li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-mce-secondary mb-4">Información Rápida</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="#" class="hover:text-white transition duration-150">Política de Privacidad</a></li>
                        <li><a href="#" class="hover:text-white transition duration-150">Términos del Servicio</a></li>
                        <li><a href="#" class="hover:text-white transition duration-150">Preguntas Frecuentes</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-6 border-t border-gray-700 text-center">
                <p class="text-gray-500 text-xs">&copy; <?= date('Y') ?> MCE - Mantenimientos Computacionales Especializados. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="<?php echo Flight::get('base_url'); ?>/js/landingpage.js"></script>
</body>
</html>