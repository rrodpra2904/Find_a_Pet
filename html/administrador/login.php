<?php
session_start(); 
include 'conexion.php'; 


$errorU = isset($_SESSION['errorUsuarioNoExiste']) ? $_SESSION['errorUsuarioNoExiste'] : "";
$errorP = isset($_SESSION['errorPasswordIncorrecto']) ? $_SESSION['errorPasswordIncorrecto'] : "";


$nombreRecuperado = isset($_SESSION['nombreUsuarioTemporal']) ? $_SESSION['nombreUsuarioTemporal'] : "";


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
    <style>
     
        .boton-registrar {
            display: block;
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            background-color: #e8daef; 
            color: #000000; 
            border: none;
            border-radius: 50px; 
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        .boton-registrar:hover {
            background-color: #d5bee8; 
            transform: scale(1.01); 
        }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Acceso Panel</h2>
    <form action="validar_login.php" method="POST">
        
        <label>Usuario:</label>
        <?php 

        ?>
        <input type="text" name="user" value="<?php echo htmlspecialchars($nombreRecuperado ?? ''); ?>">

        <label>Contraseña:</label>
        <input type="password" name="password">
        
        <?php 
      
        if ($errorP == "si") { ?>
            <p class="error-msg">Te has equivocado en la contraseña o en el usuario.</p>
        <?php } ?>

        <button type="submit" class="boton-acceder">ENTRAR</button>
        
        <a href="registrar_personal.php" class="boton-registrar">REGISTRAR PERSONAL</a>
    </form>
</div>

</body>
</html>