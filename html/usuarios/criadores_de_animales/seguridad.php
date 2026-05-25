<?php
// 1. ARRANCAMOS LA SESIÓN:
// Es obligatorio poner session_start() al principio de todo para poder 
// leer las variables que guardamos cuando el usuario hizo login.
session_start();

/* 2. CONTROL DE ACCESO:
   
   Comprobamos dos cosas:
   - !isset: Si ni siquiera existe la "llave" (la variable de sesión).
   - empty: Si la llave existe pero está vacía.
   
   Si se cumple cualquiera de las dos, significa que el usuario no ha iniciado sesión.
*/
if (!isset($_SESSION['usuarioAutenticado']) || empty($_SESSION['usuarioAutenticado'])) {
    
    // 3. REDIRECCIÓN FORZADA:
    // Como el usuario no tiene permiso para estar aquí, lo mando "fuera",
    // concretamente de vuelta a la página  web de login para que se identifique.
    header("Location: ../administrador/login.php");
    
    // 4. BLOQUEO DE EJECUCIÓN:
    // El exit() es vital. Sin él, aunque el navegador intente redirigir, 
    // el servidor podría seguir leyendo y ejecutando el código privado de debajo.
    exit(); 
}
?>