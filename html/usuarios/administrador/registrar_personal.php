<?php
// ==========================================
// 1. CONFIGURACIÓN INICIAL Y CONEXIÓN
// ==========================================
session_start();
require '../../conexion_db.php'; 

// Variables de control para la interfaz
$mensaje = "";
$exito = false;
$usuarioTemporal = "";
$rolSeleccionadoTemporal = "empleado"; // Rol por defecto en el formulario

// ==========================================
// 2. PROCESAMIENTO DEL FORMULARIO 
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recojo los datos del formulario
    $usuario = $_POST['user'] ?? "";
    $password = $_POST['password'] ?? "";
    $rolElegido = $_POST['rol'] ?? "empleado"; // Recojo el rol que han elegido ellos
    
    // Guardo los datos temporales por si hay algún error
    $usuarioTemporal = $usuario;
    $rolSeleccionadoTemporal = $rolElegido;

    // VALIDACIÓN 1: Longitud de la contraseña usando strlen()
    if (strlen($password) < 8) {
        $mensaje = "La contraseña debe tener al menos 8 caracteres.";
    } else {
        try {
            // VALIDACIÓN 2: Compruebo que el usuario no exista ya
            $sqlCheck = "SELECT COUNT(*) FROM usuarios WHERE user = :user";
            $sentenciaCheck = $db->prepare($sqlCheck);
            $sentenciaCheck->bindParam(':user', $usuario);
            $sentenciaCheck->execute();
            
            if ($sentenciaCheck->fetchColumn() > 0) {
                $mensaje = "Lo sentimos, ese nombre de usuario ya está registrado.";
            } else {
                
                // SEGURIDAD: Encriptación BCRYPT para la contraseña
                $passwordEncriptada = password_hash($password, PASSWORD_BCRYPT);

                // INSERCIÓN: Guardo el usuario con el ROL DINÁMICO que han elegido ellos
                $sqlInsert = "INSERT INTO usuarios (user, password, rol) VALUES (:user, :password, :rol)";
                $sentenciaInsert = $db->prepare($sqlInsert);
                
                $sentenciaInsert->bindParam(':user', $usuario);
                $sentenciaInsert->bindParam(':password', $passwordEncriptada);
                $sentenciaInsert->bindParam(':rol', $rolElegido); // Aquí se mete 'administrador' o 'empleado'
                
                $sentenciaInsert->execute();

                // CONTROL DE ÉXITO
                $mensaje = "¡Personal registrado con éxito! El usuario ya tiene asignado el rol de " . $rolElegido . ".";
                $exito = true;
                
                // Limpio los campos al acabar con éxito
                $usuarioTemporal = ""; 
                $rolSeleccionadoTemporal = "empleado";
            }
        } catch (PDOException $e) {
            $mensaje = "Error al registrar: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Personal - Find a Pet</title>
    <link rel="stylesheet" href="styles_registrar_personal.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;900&display=swap" rel="stylesheet">
</head>
<body>

<div class="login-card">
    
    <div style="text-align: left; margin-bottom: 20px;">
        <a href="login_usuarios.php" style="text-decoration: none; color: #777676; font-size: 14px; font-weight: 600;">← Volver al Login</a>
    </div>
    
    <h2>Registro de Personal</h2>
    
    <?php if (!empty($mensaje)): ?>
        <div class="error-msg" style="background-color: <?php echo $exito ? '#f0fff4' : '#fff5f5'; ?>; color: <?php echo $exito ? '#38a169' : '#e53e3e'; ?>; padding: 12px 15px; border-left: 4px solid <?php echo $exito ? '#38a169' : '#e53e3e'; ?>; border-radius: 4px; margin-bottom: 20px; font-size: 14px; font-weight: 600; text-align: left; line-height: 1.4;">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        
        <label for="user">Nombre de usuario</label>
        <input type="text" id="user" name="user" value="<?php echo $usuarioTemporal; ?>" required>

        <label for="password">Contraseña (Mínimo 8 caracteres)</label>
        <input type="password" id="password" name="password" required>

        <label for="rol">Tipo de Personal (Rol)</label>
        <select id="rol" name="rol" style="width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px; font-family: 'Poppins', sans-serif; font-size: 14px; background-color: white;" required>
            <option value="empleado" <?php echo ($rolSeleccionadoTemporal === 'empleado') ? 'selected' : ''; ?>>Empleado</option>
            <option value="administrador" <?php echo ($rolSeleccionadoTemporal === 'administrador') ? 'selected' : ''; ?>>Administrador</option>
        </select>

        <button type="submit" class="boton-acceder">Registrar Personal</button>
        
    </form>
</div>

</body>
</html>