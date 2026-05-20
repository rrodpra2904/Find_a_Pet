<?php

/* Archivo de gestión dináminca de la base de datos.

   El archivo que se llama conexion_db.php es el encargado de detectar automáticamente si la página web 
   se está ejecutando en mi entorno local (Docker) o en el servidor real (Hosting).
   De esta forma, aplico la configuración correcta en cada caso sin cambiar el código.
*/

// 1. Verifico si el nombre del servidor coincide con 'localhost'.
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    
    /* Configuración para entornos de desarrollo (local/Docker).

       Al ser datos genéricos de mi contenedor local, estos parámetros son seguros 
       para subirse al repositorio de GitHub sin comprometer la seguridad.

    */

    $server   = "db"; 
    $user     = "root";
    $password = "password";
    $dbname   = "findapet_db";

    try {
        // Creo el objeto PDO para la conexión en local.
        $db = new PDO("mysql:host=$server;dbname=$dbname;charset=utf8", $user, $password);
        
        // Habilito las excepciones para poder ver los errores durante la fase de programación.
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
    } catch (PDOException $e) {
        // Si hay un error en local, muestro el mensaje detallado para depurar el Docker.
        echo "Error de conexión local (Docker): " . $e->getMessage();
        exit();
    }

} else {
    
    /* Configuración para entornos de producción (hosting en Byethost)

       Cuando la página web está subida al hosting, por seguridad extrema no escribo
       las contraseñas aquí, sino que las cargo desde un archivo secreto externo (que esta subido directamente
       hosting).
    */
    
    // 2. Compruebo si el archivo de credenciales privadas existe en la carpeta del servidor.
    if (file_exists('conexion_hosting.php')) {
        // Si existe, lo incluye para que la página web funcione con la contraseña real.
        require_once 'conexion_hosting.php';
    } else {
        // Si el archivo no está presente, muestro un aviso controlado por seguridad.
        echo "Error: El archivo de configuración de producción no se ha encontrado en este servidor.";
        exit();
    }
}
?>