<?php
/* Este archivo es de redirección de seguridad. 
   Lo utilizo para proteger carpetas o directorios que no deben ser accesibles 
   directamente por el usuario a través de la URL.
*/

// 1. Con 'header Location' obligo al navegador a que se mueva automáticamente a la página web de login.php.
// Si alguien intenta entrar aquí sin inicar sesión, el servidor lo manda al login.
header("Location: login.php");

/* 2. Pongo el exit() justo después de header("Location: login.php"); para que el código se pare en seco aquí. 
   Así me aseguro de que el servidor no siga leyendo ni ejecutando nada más de esta página web por seguridad.
   
   Sin el exit(), aunque el navegador se vaya a otra página web, el servidor podría seguir procesando 
   código invisible que yo haya puesto debajo, y con esto evito cualquier riesgo.
*/
exit();
?>