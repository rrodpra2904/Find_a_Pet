<?php
session_start();
require '../conexion_db.php'; 

// Variables para controlar los mensajes de error en la pantalla
$errorMensaje = "";
$usuarioTemporal = "";

// Cuando el usuario pulsa el botón de "Entrar a mi cuenta"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['user'] ?? "";
    $password = $_POST['password'] ?? "";
    $usuarioTemporal = $usuario;

    // 1. Si la contraseña tiene menos de 8 caracteres, da error
    if (strlen($password) < 8) {
        $errorMensaje = "La contraseña debe tener al menos 8 caracteres.";
    } else {
        try {
            // Busco si existe el usuario en la base de datos
            $sql = "SELECT * FROM usuarios WHERE user = :user";
            $sentencia = $db->prepare($sql); 
            $sentencia->bindParam(':user', $usuario);
            $sentencia->execute();
            $fila = $sentencia->fetch(PDO::FETCH_ASSOC);

            // Verifico si los datos no coinciden
            if (!$fila || !password_verify($password, $fila['password'])) {
                $errorMensaje = "El usuario o la contraseña no son correctos.";
            } 
            // 2. Si es ADMINISTRADOR o EMPLEADO, bloqueamos el paso con el mensaje limpio
            else if ($fila['rol'] === 'administrador' || $fila['rol'] === 'empleado') {
                $errorMensaje = "Acceso denegado. Este formulario es exclusivo para usuarios registrados. Tu cuenta actual tiene el rol de " . $fila['rol'] . ".";
            } 
            // 3. Si todo está bien y es CLIENTE (usuario normal), entra
            else if ($fila['rol'] === 'cliente') {
                $_SESSION['id_usuario'] = $fila['id'];
                $_SESSION['usuarioAutenticado'] = $fila['user'];
                $_SESSION['rol'] = $fila['rol'];
                
                header("Location: index.php");
                exit();
            }
        } catch (PDOException $e) {
            $errorMensaje = "Error de conexión: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="styles_login_usuarios.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;900&display=swap" rel="stylesheet">
</head>
<body>

<div class="login-card">
    
    <div style="text-align: left; margin-bottom: 20px;">
        <a href="../index.php" style="text-decoration: none; color: #777676; font-size: 14px; font-weight: 600;">← Volver al inicio</a>
    </div>
    
    <h2>Acceso Usuarios</h2>
    
    <?php if (!empty($errorMensaje)): ?>
        <div class="error-msg" style="background-color: #fff5f5; color: #333333; padding: 12px 15px; border-left: 4px solid #e53e3e; border-radius: 4px; margin-bottom: 20px; font-size: 14px; font-weight: 600; text-align: left; line-height: 1.4; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <?php echo $errorMensaje; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        
        <label for="user">Nombre de usuario</label>
        <input type="text" id="user" name="user" value="<?php echo $usuarioTemporal; ?>" required>

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" class="boton-acceder">Entrar a mi cuenta</button>
        
    </form>
</div>

</body>
</html>