<?php
// 1. Lanzo la sesión para poder consultar quién es el usuario que intenta entrar a esta página.
session_start();

/* 2. Control de autenticación:
   Compruebo si la variable 'usuarioAutenticado' existe y si no está vacía.
   Si el usuario no ha pasado por el login, esta variable no existirá y el sistema lo echará fuera.
*/
if (!isset($_SESSION['usuarioAutenticado']) || empty($_SESSION['usuarioAutenticado'])) {
    
    // Si no está identificado, lo mando directamente a la página web de login.php del administrador.
    header("Location: ../administrador/login.php");
    
    // Pongo el exit() para que el resto de la página web no se cargue si el usuario no tiene permiso.
    exit(); 
}

/* 3. Contro de roles:
   Aquí compruebo que el usuario tenga un rol permitido (ya sea administrador o empleado).
   Si alguien intenta entrar con un rol que no sea uno de estos dos, le deniego el acceso.
*/
if ($_SESSION['rol'] !== 'administrador' && $_SESSION['rol'] !== 'empleado' && $_SESSION['rol'] !== 'cliente') {
    
    // Si el rol no es válido, lo mando al login con un mensaje de "error=acceso_denegado" 
    // para que sepa por qué no ha podido entrar.
    header("Location: ../login.php?error=acceso_denegado");
    
    // Detengo la ejecución por completo por seguridad.
    exit();
}

/* Si el código llega hasta aquí, significa que el usuario es quien dice ser y tiene 
   permiso para ver esta sección de FindAPet. */
?>