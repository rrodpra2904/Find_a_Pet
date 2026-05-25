<?php

/* Archivo de conexión específico para adopciones.

   El archivo que se llama conexion.php utiliza el archivo 'conexion_db.php' para obtener las credenciales
   de forma dinámica y establecer la conexión para la base de datos de adopciones.
*/

// 1. Cargo la configuración de conexion_db.php (detecta automáticamente si es local o hosting).

// Uso "../" para acceder al archivo de conexión principal que está en la carpeta superior.
require_once("../../conexion_db.php");

// 2. Defino el nombre de la base de datos específica para el sistema de adopciones.
$dbname_adopciones = "adopciones_de_animales";

/* --- Conexión PDO ---

   Configuro esta conexión para que funcionen correctamente los archivos de gestión 
   (como el de eliminar registros) que utilizan sentencias preparadas.

*/
try {
    // Creo la conexión PDO usando el servidor, usuario y contraseña que vienen del archivo conexion_db.php
    $db = new PDO("mysql:host=$server;dbname=$dbname_adopciones;charset=utf8", $user, $password);
    
    // Configuro PDO para que lance excepciones en caso de error. 

    // Esto evita que el código se detenga de forma inesperada y ayuda a depurar.
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Si la conexión PDO falla, muestro el error y detengo la ejecución.
    echo "Error de conexión PDO: " . $e->getMessage();
    exit();
}

/* --- Conexión MySQLI ---

   Mantengo esta conexión tradicional para asegurar la compatibilidad con el resto 
   de funciones de la página web de adopciones que no han sido migradas a PDO.

*/

// Establezco la conexión MySQLi para que los listados y formularios antiguos sigan operativos.
$conexion = mysqli_connect($server, $user, $password, $dbname_adopciones);

// Verifico si hay algún error en la conexión MySQLi.
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Configuro el charset UTF-8 para que los nombres de los animales con acentos o ñ se lean bien.
mysqli_set_charset($conexion, "utf8");

?>