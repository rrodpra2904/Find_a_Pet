<?php
/* SEGURIDAD DE ACCESO:
   Incluyo el archivo que se llama seguridad.php para evitar que cualquier persona pueda borrar un criador 
   escribiendo la URL directamente. Solo un administrador con sesión activa puede ejecutar este archivo. */
include 'seguridad.php'; 
?>

<?php
/* 1. CONEXIÓN A LA BASE DE DATOS:
   Cargo la configuración de PDO para interactuar con las tablas. */
include 'conexion.php';

/* 2. CAPTURA DEL PARÁMETRO URL:
   Recojo el ID del criador mediante el método $_GET, que es el que se envía 
   cuando el administrador pulsa el botón "Eliminar" en la tabla de gestión. */
if (isset($_GET['id'])) {
    
    $id = $_GET['id'];

    /* 3. BORRADO SEGURO CON PDO:
       Preparo la consulta DELETE usando marcadores (:id). Esto es fundamental 
       para proteger el sistema contra ataques de Inyección SQL. */
    $sql = "DELETE FROM criadores_de_animales WHERE id = :id";
    $sentencia = $db->prepare($sql);

    /* Vinculo la variable PHP con el marcador de la consulta para asegurar 
       que el ID sea tratado únicamente como un dato y no como código malicioso. */
    $sentencia->bindParam(':id', $id);

    /* 4. EJECUCIÓN Y REDIRECCIÓN:
       Lanzo la orden de borrado en la base de datos. */
    if ($sentencia->execute()) {
        
        /* Si el registro se borra correctamente, devuelvo al administrador 
           al panel de gestión para que vea la lista actualizada. */
        header("Location: ../administrador/gestion_de_criadores_de_animales.php");
        
        /* Uso exit() para asegurar que el script se detenga inmediatamente 
           después de enviar la instrucción de redirección. */
        exit(); 
        
    } else {
        
  
        echo "Lo siento, no se ha podido eliminar el registro en este momento.";
    }
}
?>