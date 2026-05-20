<?php
// 1. Lo primero que hago es iniciar la sesión para que el servidor sepa exactamente cuál es la que vamos a cerrar.
// Sin esta línea, PHP no sabría qué sesión tiene que destruir.
session_start();

/* 2. Liempiaza de datos:
   Borro todas las variables que se habían guardado en la sesión (como la de 'admin', el rol o los datos temporales).
   Uso session_unset() para dejar la sesión limpia y sin ningún dato personal del usuario.
*/
session_unset();

/* 3. Destrucción total:
   Ahora destruyo la sesión por completo en el servidor para que el acceso quede totalmente cerrado.
   Esto invalida la "llave" que tenía el navegador para entrar a las zonas privadas.
*/
session_destroy();

/* 4. Redirección de salida:
   Finalmente, mando al usuario de vuelta a la página web de login.php que está en la carpeta de administrador.
   Así me aseguro de que, si quiere volver a entrar, tenga que identificarse de nuevo obligatoriamente.
*/
header("Location: ../administrador/login.php");

/* 5. Cierre de seguridad:
   Pongo el exit() para asegurar que el código se pare aquí y no se ejecute absolutamente nada más 
   de la página web del panel del administrador por debajo de este script.
*/
exit();
?>