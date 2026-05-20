<?php
session_start();
// Incluyo el archivo de conexión de la base de datos que ya tiene configurado el PDO y el driver.
include("conexion_db.php");

// 1. Recojo los datos del formulario y uso trim para limpiar espacios en blanco.

// Así me aseguro de que si el usuario mete un espacio sin querer al final, no de error.
$nombre      = trim($_POST['nombre']);
$apellidos   = trim($_POST['apellidos']);
$telefono    = trim($_POST['telefono']);
$email       = trim($_POST['email']);
$direccion   = trim($_POST['direccion']);
$tipo_animal = trim($_POST['tipo_animal']);

// Creo la variable del rol de cliente para que haya un rol cliente ---
$rol = "cliente"; 

// Variable para ir guardando los códigos de error si algo sale mal.
$errores = "";

// 2. Empiezan las validaciones.

// Compruebo si algún campo del formulario está vacío. Si falta algo, le sumo un "1" a los errores.
if ($nombre=="" || $apellidos=="" || $email=="" || $telefono=="" || $direccion=="" || $tipo_animal=="") {
    $errores .= "1"; 
} 

// Compruebo que los números de teléfonos tengan más de 9 ni menos de 6 números.
if (strlen($telefono) > 9 || strlen($telefono) < 6) {
    $errores .= "2"; 
} 

// Validación de tipo de animal: permito perro o gato (he puesto varias opciones por si acaso).
if ($tipo_animal != "perro" && $tipo_animal != "gato" && $tipo_animal != "Perro" && $tipo_animal != "Gato") {
    $errores .= "3";
}

// Busco la posición del arroba y del último punto para obligar a que el correo esté completo.
$posicion_arroba = strpos($email, '@');
$ultimo_punto    = strrpos($email, '.');

if ($posicion_arroba === false || $ultimo_punto === false || $ultimo_punto < $posicion_arroba || $ultimo_punto > strlen($email) - 3) {
    $errores .= "4";
}

// 3. Gestión de errores.

// Si hay errores, redirijo al formulario pasando los datos por la URL para que no tengan que volver a escribir todo.
if ($errores != "") {
    
    // Lo pongo así para que la url no sea difícil de leer y se vea todo ordenado.
    $url = "formulario_adopciones_de_animales.php?";
    $url .= "e=$errores";
    $url .= "&nom=$nombre";
    $url .= "&ape=$apellidos";
    $url .= "&tel=$telefono";
    $url .= "&ema=$email";
    $url .= "&dir=$direccion";
    $url .= "&ani=$tipo_animal";

    header("Location: $url");
    exit();

} else {
    
    // 4. Inserción segura con PDO (Sentencias preparadas).
    
    $sql = "INSERT INTO formulario_de_compromiso_adopciones_de_animales (nombre, apellidos, telefono, email, direccion, tipo_animal, rol) 
            VALUES (:nombre, :apellidos, :telefono, :email, :direccion, :tipo_animal, :rol)";

    // Uso la variable $db que viene del include para preparar la consulta.
    $sentencia = $db->prepare($sql);

    // Vinculo mis variables a los marcadores. Esto es lo que hace que el código sea seguro.
    $sentencia->bindParam(':nombre', $nombre);
    $sentencia->bindParam(':apellidos', $apellidos);
    $sentencia->bindParam(':telefono', $telefono);
    $sentencia->bindParam(':email', $email);
    $sentencia->bindParam(':direccion', $direccion);
    $sentencia->bindParam(':tipo_animal', $tipo_animal);
    // --- Vinculo la variable $rol ---
    $sentencia->bindParam(':rol', $rol);

    // 5. Lanzo la consulta para que se guarde todo en la base de datos.
    $sentencia->execute();

    // --- Control de acceso  de la página web de adopción de animales ---

    // Guardo en la sesión que el formulario se ha rellenado correctamente.
    // Esto me sirve para que si alguien intenta entrar directamente a la 
    // página web de criadores de animales escribiendo la URL en el navegador sin rellenar nada, 
    // el sistema sepa que no tiene permiso y lo eche.
    $_SESSION['formulario_completado'] = "si";

    // Si todo ha ido bien, redirijo a la página web de adopciones animales.
    header("Location: ../adopciones_de_animales/adopciones_de_animales.php");
    exit(); 
}
?>