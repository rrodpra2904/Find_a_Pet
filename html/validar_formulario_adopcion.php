<?php
// Aseguro que el inicio de sesión de forma limpia para evitar duplicados que rompan las cookies
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Compruebo que el teléfono tenga exactamente 9 números usando expresiones regulares (Regex)
if (!preg_match('/^[0-9]{9}$/', $telefono)) {
    $errores .= "2"; 
} 

// Validación de tipo de animal con conversión a minúsculas y aceptación de sinónimos.
$animal_limpio = mb_strtolower($tipo_animal, 'UTF-8');
$sinonimos_perro = ['perro', 'perra', 'perrito', 'perrita'];
$sinonimos_gato  = ['gato', 'gata', 'gatito', 'gatita'];

if (in_array($animal_limpio, $sinonimos_perro)) {
    $tipo_animal = "perro"; // Estandarizo el valor para la base de datos
} elseif (in_array($animal_limpio, $sinonimos_gato)) {
    $tipo_animal = "gato";  // Estandarizao el valor para la base de datos
} else {
    $errores .= "3";        // Si escribe otra cosa, salta el error
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
    $url .= "e=" . urlencode($errores);
    $url .= "&nom=" . urlencode($nombre);
    $url .= "&ape=" . urlencode($apellidos);
    $url .= "&tel=$telefono";
    $url .= "&ema=" . urlencode($email);
    $url .= "&dir=" . urlencode($direccion);
    $url .= "&ani=" . urlencode($tipo_animal);

    header("Location: $url");
    exit();

} else {
    
    // --- CONTROL DE SEGURIDAD INTELIGENTE PARA LA SESIÓN ---
    // Compruebo si el email existe en alguna de las variables de sesión
    $email_sesion = null;

    if (isset($_SESSION['email'])) {
        $email_sesion = $_SESSION['email'];
    } elseif (isset($_SESSION['usuarioAutenticado'])) {
        $email_sesion = $_SESSION['usuarioAutenticado'];
    }

    // Si no se encuentra ningún usuario logueado en la sesión, evito el error de la base de datos
    if (empty($email_sesion)) {
        echo "<script>
                alert('Debes iniciar sesión para poder tramitar el formulario de adopción.');
                window.location.href='../usuarios/login_usuarios.php';
              </script>";
        exit();
    }
    
    // 4. Inserción segura con PDO (Sentencias preparadas).
    
    $sql = "INSERT INTO formulario_de_compromiso_adopciones_de_animales 
    (usuario_email, nombre, apellidos, telefono, email, direccion, tipo_animal, rol)
    VALUES 
    (:usuario_email, :nombre, :apellidos, :telefono, :email, :direccion, :tipo_animal, :rol)";

    $sentencia = $db->prepare($sql);

    $sentencia->bindParam(':usuario_email', $email_sesion);
    $sentencia->bindParam(':nombre', $nombre);
    $sentencia->bindParam(':apellidos', $apellidos);
    $sentencia->bindParam(':telefono', $telefono);
    $sentencia->bindParam(':email', $email);
    $sentencia->bindParam(':direccion', $direccion);
    $sentencia->bindParam(':tipo_animal', $tipo_animal);
    $sentencia->bindParam(':rol', $rol);

    $sentencia->execute();

    // --- Control de acceso  de la página web de adopción de animales ---

    // Guardo en la sesión que el formulario se ha rellenado correctamente.
    // Esto me sirve para que si alguien intenta entrar directamente a la 
    // página web de criadores de animales escribiendo la URL en el navegador sin rellenar nada, 
    // el sistema sepa que no tiene permiso y lo eche.
    $_SESSION['formulario_completado'] = "si";
    
    header("Location: ../usuarios/adopciones_de_animales/adopciones_de_animales.php");
    exit(); 
}
?>