<?php
// 1. Lo primero que hago es comprobar si la sesión ya está activada en el navegador.
// Uso session_status() para no intentar abrir la sesión dos veces, lo que daría un error en PHP.
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Si la sesión no está activada, la inicio ahora mismo.
}

/* 2. Control de flujo del formulario:
   Ahora compruebo si el usuario no ha pasado por el formulario de compromiso de adopciones de animales.
   Si la variable de sesión 'formulario_completado' no existe o su valor no es "si", 
   significa que el usuario está intentando "colarse" por la URL sin rellenar los datos previos.
*/
if (!isset($_SESSION['formulario_completado']) || $_SESSION['formulario_completado'] !== "si") {
    
    /* Como ha intentado entrar directamente sin permiso, lo mando de vuelta 
       al formulario de compromiso de adopciones de animales.
       De esta forma protejo la página  web de adopciones de animales y me aseguro de que todos acepten 
       las condiciones primero.
    */
    header("Location: ../formulario_adopciones_de_animales.php"); 

    // Pongo el exit() para que el código se pare en seco y no se llegue a cargar 
    // ni un solo dato de la página web de adopciones de animales si el usuario no tiene permiso.
    exit();
}

/* Si el código pasa de aquí, es que el usuario ha rellenado el formulario correctamente 
   y puede ver el contenido de la página web de adopciones de animales. */
?>