<?php
// 1. Primero inicio la sesión para poder acceder a las variables globales de usuario.
// Es obligatorio ponerlo al principio de todo para que el navegador sepa quién es el usuario.
session_start();

// 2. Compruebo si existe la variable de sesión 'admin'. 
// Esta variable solo se crea si el usuario ha puesto correctamente su contraseña en el login.
if (!isset($_SESSION['admin'])) {
    
    /* Si el usuario no es administrador (es decir, no existe esa sesión), 
       lo mando directamente de vuelta a la página web de login.php.
       
       Hago esto para proteger el panel de control y que nadie pueda entrar 
       escribiendo la URL directamente en el buscador sin haberse identificado.
    */
    header("Location: login.php");
    
    // Pongo el exit() por seguridad. Esto asegura que el servidor deje de leer el archivo
    // inmediatamente y no muestre ni un solo dato del panel privado a un intruso.
    exit();
}

/* Si el código llega hasta aquí, significa que la sesión sí existe y el usuario es administrador.
   Ahora ya puede ver todo el contenido del panel de control de Find a Pet.
*/
?>