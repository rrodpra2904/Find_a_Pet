<?php
session_start(); // Lanzo se sesión para que me salgan los mensajes de error.
include 'conexion.php'; // Cargo la conexión a la base de datos.

// Aquí pillo los errores que vienen de validar_login.php si algo ha salido mal
// Uso operadores ? para que si no hay error, la variable se quede vacía
$errorU = isset($_SESSION['errorUsuarioNoExiste']) ? $_SESSION['errorUsuarioNoExiste'] : "";
$errorP = isset($_SESSION['errorPasswordIncorrecto']) ? $_SESSION['errorPasswordIncorrecto'] : "";

// Esto es para recuperar el nombre que el usuario ya escribió de esta forma el usuario
// no tiene que volver a ponerlo si solo se equivoca en la contraseña.
$nombreRecuperado = isset($_SESSION['nombreUsuarioTemporal']) ? $_SESSION['nombreUsuarioTemporal'] : "";

// Limpio las variables de sesión justo después de leerlas de esta forma, si el usuario refresca 
// la página web, los mensajes de error desaparecen.
$_SESSION['errorUsuarioNoExiste'] = "";
$_SESSION['errorPasswordIncorrecto'] = "";
$_SESSION['nombreUsuarioTemporal'] = "";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles_login.css">
</head>
<body>

<div class="login-card">
    <h2>Acceso Panel</h2>
    <form action="validar_login.php" method="POST">
        
        <label>Usuario:</label>
        <?php 
        // Meto el nombre recuperado en el value para mejorar la experiencia del usuario de esta forma si
        // el usuario ya estaba había metido bien los datos en los campos del formulario, se queda escrito 
        // y le ahorro tiempo al usuario porque no lo tiene que volver a escribir.
        ?>
        <input type="text" name="user" value="<?php echo $nombreRecuperado; ?>">

        <label>Contraseña:</label>
        <input type="password" name="password">
        
        <?php 
        // Si la variable de error de password me llega con un "si", saldrá un mensaje de error en rojo.
        if ($errorP == "si") { ?>
            <p class = "error-msg";>Te has equivocado en la contraseña o en el usuario.</p>
        <?php } ?>

        <button type="submit" class="boton-acceder">ENTRAR</button>
    </form>
</div>

</body>
</html>