<?php
// views/partials/flash_messages.php

if (isset($_SESSION['mensaje_exito'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['mensaje_exito']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['mensaje_exito']);
}
if (isset($_SESSION['mensaje_error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['mensaje_error']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['mensaje_error']);
}