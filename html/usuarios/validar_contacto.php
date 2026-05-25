<?php
// Incluyo el archivo de conexión a la base de datos que ya tiene configurado el objeto PDO ($db).
include("../conexion_db.php");

/* 1. Recogida y limpieza de datos:
   Recojo la información que viene del formulario. Uso trim() para eliminar espacios vacíos 
   innecesarios; así evito que alguien envíe el formulario simplemente pulsando la barra espaciadora. */
$nombre  = trim($_POST['nombre']);
$email   = trim($_POST['email']);
$asunto  = trim($_POST['asunto']);
$mensaje = trim($_POST['mensaje']);

// Variable auxiliar para ir guardando los códigos de error si las validaciones fallan.
$errores = "";

/* 2. Validacioes de seguridad: */

// Compruebo si algún campo obligatorio ha quedado vacío. Si es así, añado un "1" a mi cadena de errores.
if ($nombre == "" || $email == "" || $asunto == "" || $mensaje == "") {
    $errores .= "1"; 
} 

// Valido que el correo electrónico tenga un formato real y válido (ejemplo@dominio.com).
// Si el formato es incorrecto, añado un "2" a los errores.
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores .= "2";
}

/* 3. Gestión de errores y redirección:
   Si la variable $errores contiene algo, significa que el usuario ha cometido algún fallo. */
if ($errores != "") {
    
    /* Redirijo al usuario de vuelta a la página web de contacto enviando los códigos de error 
       y los datos que ya había escrito. Esto evita que el usuario pierda su mensaje y deba empezar de cero. */
    $url = "contacto.php?";
    $url .= "e=$errores";
    $url .= "&nom=$nombre";
    $url .= "&ema=$email";
    $url .= "&asu=$asunto";
    $url .= "&msg=$mensaje";

    header("Location: $url");
    exit(); // Detengo la ejecución aquí para que no se intente guardar información errónea.

} else {
    
    /* 4. Inserción segura con PDO:
       Uso sentencias preparadas con marcadores (:nombre, :email, etc.). 
       Para que no me hagan ataques de Inyección SQL. */
    $sql = "INSERT INTO mensajes_contacto (nombre, email, asunto, mensaje) 
            VALUES (:nombre, :email, :asunto, :mensaje)";

    // Preparo la consulta usando el objeto de conexión de mi archivo incluido anteriormente.
    $sentencia = $db->prepare($sql);

    /* 5. Vinculación de variables:
       Asocio de forma segura cada marcador de la consulta SQL con los datos limpios del formulario. */
    $sentencia->bindParam(':nombre', $nombre);
    $sentencia->bindParam(':email', $email);
    $sentencia->bindParam(':asunto', $asunto);
    $sentencia->bindParam(':mensaje', $mensaje);

    // Ejecuto la consulta para registrar el mensaje del usuario en la base de datos de FindAPet.
    $sentencia->execute();

    // Si el proceso finaliza con éxito, redirijo a la página web de contacto con un mensaje de confirmación.
    header("Location: contacto.php?ok=1");
    exit(); 
}
?>