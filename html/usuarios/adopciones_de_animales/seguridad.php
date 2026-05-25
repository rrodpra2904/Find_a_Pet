<?php

// 1. Inicio de sesion

// Arranco la sesión (o la recupero si ya existe) para poder acceder a las variables 
// que guardo cuando el usuario puso su usuario y contraseña correctamente.
session_start();

// 2. Filtro de seguridad (control de acceso)

/* Hago la comprobación clave:
   - !isset: Compruebo si ni siquiera existe la variable de sesión.
   - empty: Compruebo si existe pero está vacía.

   Si se cumple cualquiera de las dos, significa que el usuario está intentando 
   "colarse" en el panel de administración sin haber pasado por el login. 
*/
if (!isset($_SESSION['usuarioAutenticado']) || empty($_SESSION['usuarioAutenticado'])) {
    
    // Si no tiene permiso, lo mando automáticamente de vuelta a la página web de login.
    header("Location: ../administrador/login.php");
    
    // El exit() es fundamental por seguridad: obliga al servidor a detener 
    // la ejecución del script en este punto, impidiendo que el navegador 
    // llegue a mostrar ni un solo dato de la página web de adopciones que esta protegida.
    exit(); 
}

// Si el código llega hasta aquí, significa que el usuario está logueado correctamente
// y puede ver el contenido de la página web de adociones de animales.
?>