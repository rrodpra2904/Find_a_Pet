<?php
// ====================================================================
// 1. SEGURIDAD: CONTROL DE ACCESO
// ====================================================================
session_start();
require '../conexion_db.php';

// Si no hay sesión iniciada, lo mando al login
if (!isset($_SESSION['usuarioAutenticado'])) {
    header("Location: login_usuarios.php");
    exit();
}

$nombreUsuario = $_SESSION['usuarioAutenticado'];
$mensaje_exito = "";
$mensaje_error = "";

// ====================================================================
// 2. PROCESAR EL FORMULARIO (Cuando el usuario pulsa "Guardar Cambios")
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recojo los datos (unifico nombre y apellidos en nombre_completo)
    $nuevo_nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $nuevo_telefono = trim($_POST['telefono'] ?? '');
    $nuevo_email = trim($_POST['email'] ?? '');

    // Validación: el nombre y el email son obligatorios
    if ($nuevo_nombre_completo == "" || $nuevo_email == "") {
        $mensaje_error = "Por favor, rellena los campos obligatorios (*).";
    } else {
        try {

            $sql_update = "UPDATE usuarios 
                           SET nombre_completo = :nombre_completo, telefono = :telefono, email = :email 
                           WHERE user = :user_session";
            
            $stmt_update = $db->prepare($sql_update);
            $stmt_update->bindParam(':nombre_completo', $nuevo_nombre_completo);
            $stmt_update->bindParam(':telefono', $nuevo_telefono);
            $stmt_update->bindParam(':email', $nuevo_email);
            $stmt_update->bindParam(':user_session', $nombreUsuario);
            
            if ($stmt_update->execute()) {
                $mensaje_exito = "¡Tus datos de cuenta se han actualizado correctamente!";
            } else {
                $mensaje_error = "Hubo un problema al guardar los cambios.";
            }
        } catch (PDOException $e) {
            $mensaje_error = "Error en la base de datos: " . $e->getMessage();
        }
    }
}

// ====================================================================
// 3. CARGAR DATOS ACTUALES DE LA TABLA USUARIOS
// ====================================================================
$datos_usuario = null;
try {
    $sql_select = "SELECT * FROM usuarios WHERE user = :user LIMIT 1";
    $stmt_select = $db->prepare($sql_select);
    $stmt_select->bindParam(':user', $nombreUsuario);
    $stmt_select->execute();
    $datos_usuario = $stmt_select->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar tus datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Find a Pet</title>
    <link rel="stylesheet" href="styles_login_usuarios.css">
    <link rel="stylesheet" href="../styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="background-color: #f7fafc; font-family: 'Poppins', sans-serif; padding: 40px 20px;">

<div style="max-width: 550px; margin: 0 auto; background: white; border: 1px solid #edf2f7; border-radius: 16px; padding: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.03);">
    
    <div style="text-align: center; margin-bottom: 30px;">
        <span style="font-size: 40px;">⚙️</span>
        <h2 style="color: #2c3e50; margin: 10px 0 5px 0; font-size: 24px; font-weight: 600;">Modificar mis datos</h2>
        <p style="color: #7f8c8d; font-size: 14px; margin: 0;">Actualiza la información de tu cuenta en Find a Pet.</p>
    </div>

    <?php if ($mensaje_exito !== ""): ?>
        <div style="background-color: #e8f5e9; border-left: 4px solid #2e7d32; color: #1b5e20; padding: 15px; border-radius: 6px; font-size: 14px; margin-bottom: 25px;">
            <strong>✅ Éxito:</strong> <?php echo $mensaje_exito; ?>
        </div>
    <?php endif; ?>

    <?php if ($mensaje_error !== ""): ?>
        <div style="background-color: #ffebee; border-left: 4px solid #c62828; color: #c62828; padding: 15px; border-radius: 6px; font-size: 14px; margin-bottom: 25px;">
            <strong>⚠️ Error:</strong> <?php echo $mensaje_error; ?>
        </div>
    <?php endif; ?>

    <form action="editar_perfil.php" method="POST">
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 13px; font-weight: 600; color: #4a5568; margin-bottom: 6px;">Nombre de usuario:</label>
            <input type="text" value="<?php echo htmlspecialchars($nombreUsuario); ?>" disabled style="width: 100%; padding: 10px 14px; background: #edf2f7; border: 1px solid #e2e8f0; border-radius: 8px; color: #718096; cursor: not-allowed; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 13px; font-weight: 600; color: #4a5568; margin-bottom: 6px;">Nombre Completo *:</label>
            <input type="text" name="nombre_completo" value="<?php echo htmlspecialchars($datos_usuario['nombre_completo'] ?? ''); ?>" required style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; font-family: 'Poppins', sans-serif;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 13px; font-weight: 600; color: #4a5568; margin-bottom: 6px;">Correo Electrónico *:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($datos_usuario['email'] ?? ''); ?>" required style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; font-family: 'Poppins', sans-serif;">
        </div>

        <div style="margin-bottom: 30px;">
            <label style="display: block; font-size: 13px; font-weight: 600; color: #4a5568; margin-bottom: 6px;">Teléfono:</label>
            <input type="text" name="telefono" value="<?php echo htmlspecialchars($datos_usuario['telefono'] ?? ''); ?>" style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; font-family: 'Poppins', sans-serif;">
        </div>

        <div style="display: flex; gap: 12px; border-top: 1px solid #f1f5f9; padding-top: 25px;">
            <button type="submit" class="btn-action-primary" style="padding: 12px 24px; font-size: 13.5px; border: none; border-radius: 8px; font-weight: 500; cursor: pointer; flex: 1;">
                💾 Guardar Cambios
            </button>
            <a href="index.php?tab=perfil" style="padding: 12px 24px; font-size: 13.5px; text-decoration: none; text-align: center; border-radius: 8px; font-weight: 500; background-color: #edf2f7; color: #4a5568; flex: 1;">
                ↩️ Volver al Panel
            </a>
        </div>

    </form>
</div>

</body>
</html>