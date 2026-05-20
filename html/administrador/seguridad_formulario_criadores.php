<?php
// 1. Lo primero que hago es comprobar si la sesión ya está activada en el navegador.
// Uso session_status() para evitar errores si la sesión ya se había iniciado anteriormente.
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Si la sesión no está activada, la inicio ahora mismo.
}

/* 2. Control de flujo del formulario de criadores:
   Aquí compruebo si el usuario NO ha pasado por el formulario de compromiso de criadores.
   Si la variable de sesión 'formulario_completado' no existe o no es "si", 
   bloqueo el acceso porque significa que están intentando entrar directamente por la URL.
*/
if (!isset($_SESSION['formulario_completado']) || $_SESSION['formulario_completado'] !== "si") {
    
    /* Como ha intentado acceder sin permiso, lo mando de vuelta al formulario inicial.
       De esta forma protejo la página web de criadores de animales y me aseguro 
       de que todos rellenen sus datos y acepten las condiciones primero.
    */
    header("Location: ../formulario_criadores_de_animales.php"); 

    // Pongo el exit() para que el servidor deje de leer código inmediatamente.
    // Así evito que se cargue cualquier dato de la página web de criadores por seguridad.
    exit();
}

/* Si el código llega aquí, el usuario tiene el permiso de sesión activo y puede 
   navegar por la sección de criadores de animales. */
?>