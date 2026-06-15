<?php
session_start();

include("conexion_db.php");


if (isset($_SESSION['usuarioAutenticado'])) {
    $email_sesion = $_SESSION['usuarioAutenticado'];

    $sql_comprobar = "SELECT id FROM formulario_de_compromiso_adopciones_de_animales WHERE usuario_email = :usuario_email LIMIT 1";
    $stmt_comprobar = $db->prepare($sql_comprobar);
    $stmt_comprobar->bindParam(':usuario_email', $email_sesion);
    $stmt_comprobar->execute();

    // Si ya existe el registro, le damos paso directo y saltamos el formulario
    if ($stmt_comprobar->fetch()) {
        $_SESSION['formulario_completado'] = "si";
        header("Location: ./adopciones_de_animales/adopciones_de_animales.php");
        exit();
    }
}

$nombre      = trim($_POST['nombre']);
$apellidos   = trim($_POST['apellidos']);
$telefono    = trim($_POST['telefono']);
$email       = trim($_POST['email']);
$direccion   = trim($_POST['direccion']);
$tipo_animal = trim($_POST['tipo_animal']);


$rol = "cliente"; 


$errores = "";


if ($nombre=="" || $apellidos=="" || $email=="" || $telefono=="" || $direccion=="" || $tipo_animal=="") {
    $errores .= "1"; 
} 

if (!preg_match('/^[0-9]{9}$/', $telefono)) {
    $errores .= "2"; 
} 

$animal_limpio = mb_strtolower($tipo_animal, 'UTF-8');
$sinonimos_perro = ['perro', 'perra', 'perrito', 'perrita'];
$sinonimos_gato  = ['gato', 'gata', 'gatito', 'gatita'];

if (in_array($animal_limpio, $sinonimos_perro)) {
    $tipo_animal = "perro"; 
} elseif (in_array($animal_limpio, $sinonimos_gato)) {
    $tipo_animal = "gato"; 
} else {
    $errores .= "3";  
}

$posicion_arroba = strpos($email, '@');
$ultimo_punto    = strrpos($email, '.');

if ($posicion_arroba === false || $ultimo_punto === false || $ultimo_punto < $posicion_arroba || $ultimo_punto > strlen($email) - 3) {
    $errores .= "4";
}


if ($errores != "") {
    
    
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
    
  
    
    $sql = "INSERT INTO formulario_de_compromiso_adopciones_de_animales 
    (usuario_email, nombre, apellidos, telefono, email, direccion, tipo_animal, rol)
    VALUES 
    (:usuario_email, :nombre, :apellidos, :telefono, :email, :direccion, :tipo_animal, :rol)";

    $sentencia = $db->prepare($sql);

    $email_sesion = $_SESSION['usuarioAutenticado'];

    $sentencia->bindParam(':usuario_email', $email_sesion);
    $sentencia->bindParam(':nombre', $nombre);
    $sentencia->bindParam(':apellidos', $apellidos);
    $sentencia->bindParam(':telefono', $telefono);
    $sentencia->bindParam(':email', $email);
    $sentencia->bindParam(':direccion', $direccion);
    $sentencia->bindParam(':tipo_animal', $tipo_animal);
    $sentencia->bindParam(':rol', $rol);

    $sentencia->execute();


    $_SESSION['formulario_completado'] = "si";

    header("Location: ./adopciones_de_animales/adopciones_de_animales.php");
    exit(); 
}
?>