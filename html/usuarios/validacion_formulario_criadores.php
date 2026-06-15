<?php
// Inicia la sesión al principio para que no de errores con la base de datos.
session_start();

// Incluyo el archivo de conexión de la base de datos que ya tiene configurado el PDO y el driver.
include("../conexion_db.php");

// 1. Recojo los datos del formulario y uso trim para limpiar espacios en blanco.

// Así me aseguro de que si el usuario mete un espacio sin querer al final, no de error.
$nombre      = trim($_POST['nombre']);
$apellidos   = trim($_POST['apellidos']);
$telefono    = trim($_POST['telefono']);
$email       = trim($_POST['email']);
$direccion   = trim($_POST['direccion']);
$tipo_animal = trim($_POST['tipo_animal']);

// Variable para ir guardando los códigos de error si algo sale mal.
$errores = "";

// 2. Empiezan las validaciones.

// Compruebo si algún campo del formulario está vacío. Si falta algo, le sumo un "1" a los errores.
if ($nombre=="" || $apellidos=="" || $email=="" || $telefono=="" || $direccion=="" || $tipo_animal=="") {
    $errores .= "1"; 
} 

// Compruebo que el teléfono tenga exactamente 9 números usando expresiones regulares (Regex)
// (Solo si no está vacío para no duplicar errores)
if ($telefono != "" && !preg_match('/^[0-9]{9}$/', $telefono)) {
    $errores .= "2"; 
} 

// Validación de tipo de animal con conversión a minúsculas y aceptación de sinónimos.
$animal_limpio = mb_strtolower($tipo_animal, 'UTF-8');
$sinonimos_perro = ['perro', 'perra', 'perrito', 'perrita'];
$sinonimos_gato  = ['gato', 'gata', 'gatito', 'gatita'];

if ($tipo_animal != "") {
    if (in_array($animal_limpio, $sinonimos_perro)) {
        $tipo_animal = "perro"; // Estandarizamos el valor para la base de datos
    } elseif (in_array($animal_limpio, $sinonimos_gato)) {
        $tipo_animal = "gato";  // Estandarizamos el valor para la base de datos
    } else {
        $errores .= "3";        // Si escribe otra cosa, salta el error
    }
}

// Busco la posición del arroba y del último punto para obligar a que el correo esté completo.
if ($email != "") {
    $posicion_arroba = strpos($email, '@');
    $ultimo_punto    = strrpos($email, '.');

    if ($posicion_arroba === false || $ultimo_punto === false || $ultimo_punto < $posicion_arroba || $ultimo_punto > strlen($email) - 3) {
        $errores .= "4";
    }
}

// 3. Gestión de errores.

// Si hay errores, redirijo al formulario pasando los datos por la URL para que no tengan que volver a escribir todo.
if ($errores != "") {
    
    // Lo pongo así para que la url no sea difícil de leer y se vea todo ordenado.
    $url = "formulario_criadores_de_animales.php?";
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
    
    $sql = "INSERT INTO formulario_de_compromiso_de_criadores_de_animales (usuario_email, nombre, apellidos, telefono, email, direccion, tipo_animal) 
            VALUES (:usuario_email, :nombre, :apellidos, :telefono, :email, :direccion, :tipo_animal)";

    // Uso la variable $db que viene del include para preparar la consulta.
    $sentencia = $db->prepare($sql);

    $sentencia->bindParam(':usuario_email', $_SESSION['usuarioAutenticado']);
    
    // Vinculo mis variables a los marcadores. Esto es lo que hace que el código sea seguro.
    $sentencia->bindParam(':nombre', $nombre);
    $sentencia->bindParam(':apellidos', $apellidos);
    $sentencia->bindParam(':telefono', $telefono);
    $sentencia->bindParam(':email', $email);
    $sentencia->bindParam(':direccion', $direccion);
    $sentencia->bindParam(':tipo_animal', $tipo_animal);

    // 5. Lanzo la consulta para que se guarde todo en la base de datos.
    $sentencia->execute();

    // --- Control de acceso de la página web de criadores de animales ---

    // Guardo en la sesión que el formulario se ha rellenado correctamente.
    // Esto me sirve para que si alguien intenta entrar directamente a la 
    // página web de criadores de animales escribiendo la URL en el navegador sin rellenar nada, 
    // el sistema sepa que no tiene permiso y lo eche.
    $_SESSION['formulario_completado'] = "si";

    // Si todo ha ido bien, redirijo a la página web de criadores de animales.
    header("Location: ./criadores_de_animales/criadores_de_animales.php");
    exit(); 
}
?>