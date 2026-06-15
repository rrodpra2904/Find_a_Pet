<?php
session_start();
session_unset();   // Libera todas las variables de sesión actuales
session_destroy(); // Destruye por completo la sesión en el servidor

// Redirijo al usuario de vuelta al login
header("Location: login_usuarios.php");
exit();
?>