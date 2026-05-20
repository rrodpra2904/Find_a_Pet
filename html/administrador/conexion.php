<?php

/* Archivo de conexión específico para administrador. 

   Este script se encarga de establecer la conexión con la base de datos central.
   Usa 'require_once' para asegurar que el archivo maestro de configuración 
   sea cargado obligatoriamente antes de continuar.

*/

// 1. Cargo los datos del archivo maestro (host, user y password).

// En Docker, el host suele ser el nombre del servicio definido en el archivo yaml.
require_once("../conexion_db.php");

// 2. Defino el nombre de la base de datos principal para evitar confusiones con otros módulos.
$dbname = "findapet_db"; 

try {

    /* --- Conexión mediante PDO --- 

       Utilizo PDO para permitir el uso de sentencias preparadas y bindParam,
       lo cual protege la aplicación web contra ataques de inyección SQL.
       
    */
    
    // Creo la conexión usando las variables que heredo de ../conexion_db.php
    $db = new PDO("mysql:host=$server;dbname=$dbname;charset=utf8", $user, $password);
    
    // Configuro el driver para que, en caso de error, lance una excepción controlada.
    // Esto es fundamental para depurar errores durante el desarrollo en local.
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Si la conexión falla, detengo la ejecución y muestro el error.
    die("Error de conexión con PDO: " . $e->getMessage());
}
?>