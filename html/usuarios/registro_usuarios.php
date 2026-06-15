<?php
// ==========================================
// 1. CONFIGURACIÓN INICIAL Y CONEXIÓN
// ==========================================
session_start();
require '../conexion_db.php'; 

$mensaje = "";
$exito = false;

$nombreTemporal = "";
$emailTemporal = "";
$telefonoTemporal = "";
$usuarioTemporal = "";

// ==========================================
// 2. PROCESAMIENTO DEL FORMULARIO (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recojo los datos aplicando trim() para eliminar espacios en blanco al inicio y al final
    $nombre = trim($_POST['nombre_completo'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $telefono = trim($_POST['telefono'] ?? "");
    $usuario = trim($_POST['user'] ?? "");
    $password = $_POST['password'] ?? ""; // A la contraseña no le hago trim por si qse uiere usar espacios
    
    $nombreTemporal = $nombre;
    $emailTemporal = $email;
    $telefonoTemporal = $telefono;
    $usuarioTemporal = $usuario;

    // ==========================================
    // CAPA DE VALIDACIÓN EN SERVIDOR
    // ==========================================
    
    // VALIDACIÓN 1: Comprobar campos vacíos
    if (empty($nombre) || empty($email) || empty($telefono) || empty('user') || empty($password)) {
        $mensaje = "Todos los campos son obligatorios y no pueden contener solo espacios.";
    }
    // VALIDACIÓN 2: Formato de Email correcto
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "El formato del correo electrónico no es válido.";
    }
    // VALIDACIÓN 3: Formato de Teléfono (Solo números, entre 9 y 15 dígitos)
    else if (!preg_match('/^[0-9]{9,15}$/', $telefono)) {
        $mensaje = "El teléfono debe contener solo números (entre 9 y 15 dígitos).";
    }
    // VALIDACIÓN 4: Longitud de contraseña
    else if (strlen($password) < 8) {
        $mensaje = "La contraseña debe tener al menos 8 caracteres.";
    } 
    // Si paso todas las validaciones, procedo con la Base de Datos
    else {
        try {
            // VALIDACIÓN 5: Comprobar si el usuario ya existe
            $sqlCheck = "SELECT COUNT(*) FROM usuarios WHERE user = :user";
            $sentenciaCheck = $db->prepare($sqlCheck);
            $sentenciaCheck->bindParam(':user', $usuario);
            $sentenciaCheck->execute();
            
            if ($sentenciaCheck->fetchColumn() > 0) {
                $mensaje = "Lo sentimos, ese nombre de usuario ya está registrado.";
            } else {
                
                $passwordEncriptada = password_hash($password, PASSWORD_BCRYPT);
                $rolPorDefecto = "cliente"; 

                // INSERCIÓN SEGURA
                $sqlInsert = "INSERT INTO usuarios (nombre_completo, email, telefono, user, password, rol) 
                              VALUES (:nombre, :email, :telefono, :user, :password, :rol)";
                
                $sentenciaInsert = $db->prepare($sqlInsert);
                
                $sentenciaInsert->bindParam(':nombre', $nombre);
                $sentenciaInsert->bindParam(':email', $email);
                $sentenciaInsert->bindParam(':telefono', $telefono);
                $sentenciaInsert->bindParam(':user', $usuario);
                $sentenciaInsert->bindParam(':password', $passwordEncriptada);
                $sentenciaInsert->bindParam(':rol', $rolPorDefecto);
                
                $sentenciaInsert->execute();

                $mensaje = "¡Cuenta creada con éxito! Ya puedes iniciar sesión.";
                $exito = true;
                
                // Vacio el formulario
                $nombreTemporal = $emailTemporal = $telefonoTemporal = $usuarioTemporal = "";
            }
        } catch (PDOException $e) {
            $mensaje = "Error al registrar el usuario: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="styles_login_usuarios.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;900&display=swap" rel="stylesheet">
</head>
<body>

<div class="login-card">
    
    <div style="text-align: left; margin-bottom: 20px;">
        <a href="login_usuarios.php" style="text-decoration: none; color: #777676; font-size: 14px; font-weight: 600;">← Volver al Login</a>
    </div>
    
    <h2>Crear Cuenta</h2>
    
    <?php if (!empty($mensaje)): ?>
        <div class="error-msg" style="background-color: <?php echo $exito ? '#f0fff4' : '#fff5f5'; ?>; color: <?php echo $exito ? '#38a169' : '#e53e3e'; ?>; padding: 12px 15px; border-left: 4px solid <?php echo $exito ? '#38a169' : '#e53e3e'; ?>; border-radius: 4px; margin-bottom: 20px; font-size: 14px; font-weight: 600; text-align: left; line-height: 1.4;">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        
        <label for="nombre_completo">Nombre y Apellidos</label>
        <input type="text" id="nombre_completo" name="nombre_completo" value="<?php echo $nombreTemporal; ?>" required>

        <label for="email">Correo Electrónico</label>
        <input type="text" id="email" name="email" value="<?php echo $emailTemporal; ?>" required>

        <label for="telefono">Teléfono de contacto</label>
        <input type="text" id="telefono" name="telefono" value="<?php echo $telefonoTemporal; ?>" required>

        <label for="user">Elige tu nombre de usuario</label>
        <input type="text" id="user" name="user" value="<?php echo $usuarioTemporal; ?>" required>

        <label for="password">Contraseña (Mínimo 8 caracteres)</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" class="boton-acceder">Registrarme ahora</button>
        
    </form>
</div>

</body>
</html>