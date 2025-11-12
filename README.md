# Desarrollo-Capstone-Equipo2-802D

Sistema de Gestión de Tickets de Soporte Técnico. Este proyecto es una aplicación web diseñada para administrar tickets de soporte, clientes, usuarios y facturación.

## Características Principales

- **Gestión de Tickets:** Creación, asignación, seguimiento y cierre de tickets de soporte.
- **Panel de Control (Dashboard):** Visualización de estadísticas clave sobre el estado del sistema.
- **Administración de Usuarios:** Roles para Administradores, Supervisores, Técnicos y Clientes.
- **Gestión de Clientes:** Módulo para administrar la información de los clientes.
- **Facturación:** Seguimiento de costos asociados a los tickets.
- **Sistema de Limpieza y Backup:** Herramientas para el mantenimiento de la base de datos.

## Requisitos

- PHP 8.0 o superior
- Servidor web (Apache, Nginx)
- MySQL o MariaDB
- Composer

## Instalación

1.  **Clonar el repositorio:**
    ```bash
    git clone [URL-del-repositorio]
    cd Desarrollo-Capstone-Equipo2-802D
    ```
2.  **Configurar la base de datos:**
    - Importa el archivo `.sql` inicial en tu gestor de base de datos.
    - Renombra `config/credentials.example.php` a `config/credentials.php`.
    - Edita `config/credentials.php` con tus credenciales de base de datos y la ruta a `mysqldump.exe`.

3.  **Instalar dependencias (si usas Composer):**
    ```bash
    composer install
    ```

4.  **Configurar el servidor web:**
    - Apunta la raíz de tu servidor web al directorio `public/`.
    - Asegúrate de que `mod_rewrite` (en Apache) esté habilitado para que las rutas amigables funcionen.
