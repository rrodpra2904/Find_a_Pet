<?php
// 1. Iniciamos la sesión si no está iniciada ya
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Si el usuario NO ha iniciado sesión, o NO es un cliente, lo echamos inmediatamente
if (!isset($_SESSION['usuarioAutenticado']) || $_SESSION['rol'] !== 'cliente') {
    // Lo redirigimos a la pantalla de login para que no pueda ver nada
    header("Location: ../usuarios/login_usuarios.php");
    exit(); // Detenemos por completo la ejecución de la página
}
?>