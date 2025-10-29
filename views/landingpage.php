<?php
// Mensaje flash vía URL, para nuevos clientes
$status = $_GET['status'] ?? '';
?>
<?php if(isset($_SESSION['mensaje_exito'])): ?>
    <div class="alert-exito">
        <?php 
            echo $_SESSION['mensaje_exito']; 
            unset($_SESSION['mensaje_exito']);
        ?>
        <span class="close-alert" onclick="this.parentElement.style.display='none'">&times;</span>
    </div>
<?php endif; ?>
<?php if(isset($_SESSION['mensaje_error'])): ?>
    <div class="alert-error">
        <?php 
            echo $_SESSION['mensaje_error']; 
            unset($_SESSION['mensaje_error']);
        ?>
        <span class="close-alert" onclick="this.parentElement.style.display='none'">&times;</span>
    </div>
<?php endif; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCE | Sistema de Soporte TI Especializado</title>
    <link rel="stylesheet" href="<?php echo Flight::get('base_url'); ?>/css/mensaje.css?v=1.1">
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
    <style>
        .carousel-slide {
            transition: opacity 0.5s ease-in-out;
        }
        .text-shadow-strong {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        }
    </style>
</head>
<body class="font-sans bg-mce-bg text-gray-800">

    <!-- Navbar -->
    <nav class="bg-mce-primary shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <a href="#" class="flex items-center space-x-2">
                    <span class="text-3xl font-extrabold text-white tracking-wider">MCE</span>
                    <span class="text-sm text-gray-300 hidden md:inline">Mantenimientos Computacionales Especializados</span>
                </a>
                <div class="flex items-center space-x-2 md:space-x-4">
                    <a href="<?php echo Flight::get('base_url'); ?>/" class="text-white hover:text-mce-secondary px-3 py-2 rounded-md text-base font-medium transition duration-150 ease-in-out">Inicio</a>
                    <a href="#services" class="text-white hover:text-mce-secondary px-3 py-2 rounded-md text-base font-medium transition duration-150 ease-in-out">Servicios</a>
                    <a href="#contact" class="text-white hover:text-mce-secondary px-3 py-2 rounded-md text-base font-medium transition duration-150 ease-in-out">Contacto</a>
                    <!-- Botón de Registrarse (sin funcionalidad de momento) -->
                    <?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>

                    <?php if (!isset($_SESSION['id_usuario'])): ?>
                        <!-- Si NO está logueado -->
                        <a href="<?php echo Flight::get('base_url'); ?>/registro_cliente" 
                            title="Registrar nuevo cliente" 
                            class="bg-mce-secondary hover:bg-teal-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                            Registrarse
                        </a>

                        <?php if ($status === 'success'): ?>
                            <div class="max-w-3xl mx-auto mt-4 px-6 py-3 bg-green-500 text-white font-semibold rounded-lg shadow-md text-center">
                                ¡Registro exitoso! Ya puedes iniciar sesión.
                            </div>
                        <?php elseif ($status === 'error'): ?>
                            <div class="max-w-3xl mx-auto mt-4 px-6 py-3 bg-red-500 text-white font-semibold rounded-lg shadow-md text-center">
                                Ocurrió un error al registrarte. Intenta nuevamente.
                            </div>
                        <?php endif; ?>

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
                <div class="carousel-slide absolute inset-0 opacity-100" data-index="0" style="background-image: url('https://placehold.co/1200x450/1A2E44/ffffff?text=Servicio+de+Soporte+TI'); background-size: cover; background-position: center;">
                    <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                        <div class="text-center p-6 rounded-xl bg-black bg-opacity-20">
                            <h2 class="text-4xl md:text-6xl font-bold text-white mb-2 text-shadow-strong">Soporte TI Especializado</h2>
                            <p class="text-xl md:text-2xl text-gray-200 text-shadow-strong">Máxima eficiencia y seguridad para tu infraestructura.</p>
                        </div>
                    </div>
                </div>
                <div class="carousel-slide absolute inset-0 opacity-0" data-index="1" style="background-image: url('https://placehold.co/1200x450/38B2AC/ffffff?text=Mantenimiento+Preventivo'); background-size: cover; background-position: center;">
                    <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                        <div class="text-center p-6 rounded-xl bg-black bg-opacity-20">
                            <h2 class="text-4xl md:text-6xl font-bold text-white mb-2 text-shadow-strong">Mantenimiento Preventivo</h2>
                            <p class="text-xl md:text-2xl text-gray-200 text-shadow-strong">Evita problemas antes de que ocurran.</p>
                        </div>
                    </div>
                </div>
                <div class="carousel-slide absolute inset-0 opacity-0" data-index="2" style="background-image: url('https://placehold.co/1200x450/4B5563/ffffff?text=Asistencia+Inmediata'); background-size: cover; background-position: center;">
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
                    <button class="bg-mce-primary hover:bg-gray-700 text-white font-bold py-3 px-8 rounded-lg text-lg shadow-xl transition duration-300 transform hover:scale-105">
                        Ver Planes de Soporte Completos
                    </button>
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