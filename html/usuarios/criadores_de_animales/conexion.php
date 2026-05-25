<?php

/* Archivo de conexión especifico para criadores de animales.

   Este archivo que se llama conexion.php utiliza el archivo 'conexion_db.php' para obtener las credenciales
   y establecer la conexión específica para el módulo de criadores de animales.
*/

// 1. Cargo la configuración dinámica (Local o Hosting) desde el archivo maestro.

// Al usar "../", subo un nivel de carpeta para encontrar el archivo principal.
require_once("../../conexion_db.php");

// 2. Defino el nombre de la base de datos específica para este apartado.
$dbname_criadores = "criadores_de_animales";

/* --- Conexión PDO (Para nuevas consultas seguras) --- */
try {
    // Creo la conexión PDO usando el servidor, usuario y contraseña que vienen del archivo conexion_db.php
    $db = new PDO("mysql:host=$server;dbname=$dbname_criadores;charset=utf8", $user, $password);
    
    // Configuro PDO para que lance excepciones en caso de error. 

    // Esto evita que el código se detenga de forma inesperada y ayuda a depurar.
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Si la conexión PDO falla, muestro el error y detengo la ejecución.
    echo "Error de conexión PDO: " . $e->getMessage();
    exit();
}

/* --- Conexión MySQLI (Compatibilidad con el resto de la página web) --- 

   Mantengo esta conexión para que las funciones antiguas que usan mysqli 
   sigan funcionando correctamente sin tener que cambiar todo el código.

*/

// Creo la conexión tradicional usando las variables heredadas del archivo conexion_db.php.
$conexion = mysqli_connect($server, $user, $password, $dbname_criadores);

// Verifico si la conexión MySQLi ha tenido éxito.
if (!$conexion) {
    // Si falla, muestro el error específico de MySQLi.
    die("Error en la conexión de criadores: " . mysqli_connect_error());
}

// Fuerzo la codificación de caracteres UTF-8 para evitar problemas con tildes y eñes.
mysqli_set_charset($conexion, "utf8");

?>