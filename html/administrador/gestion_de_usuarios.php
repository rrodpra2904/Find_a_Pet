<?php

include 'seguridad.php';


include '../conexion_db.php';

$mensaje = "";


if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id_usuario = intval($_GET['id']);
    

    if ($_GET['accion'] === 'eliminar') {
        try {
            $stmt = $db->prepare("DELETE FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id_usuario]);
            $mensaje = "<p style='color: red; font-weight: bold; font-family: \"Poppins\", sans-serif; margin-bottom: 20px;'>🗑️ Usuario eliminado por completo del sistema.</p>";
        } catch (PDOException $e) {
            $mensaje = "<p style='color: red; font-family: \"Poppins\", sans-serif; margin-bottom: 20px;'>Error al eliminar: " . $e->getMessage() . "</p>";
        }
    }
}


$buscar_nombre = isset($_POST['b_nombre']) ? trim($_POST['b_nombre']) : "";
$buscar_email = isset($_POST['b_email']) ? trim($_POST['b_email']) : "";

try {
    $sql = "SELECT * FROM usuarios WHERE 1=1";
    $params = [];

    if (!empty($buscar_nombre)) { 
        $sql .= " AND nombre_completo LIKE :nombre"; 
        $params[':nombre'] = '%' . $buscar_nombre . '%'; 
    }
    if (!empty($buscar_email)) { 
        $sql .= " AND email LIKE :email"; 
        $params[':email'] = '%' . $buscar_email . '%'; 
    }

    $sql .= " ORDER BY id DESC";
    $stmtUsers = $db->prepare($sql);
    $stmtUsers->execute($params);
    $usuarios_registrados = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $usuarios_registrados = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - FindAPet</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles_panel.css">
    
    <style>
        .tabla-moderacion {
            table-layout: fixed !important;
            width: 100% !important;
        }
        
        .fila-datos td {
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            word-break: break-word !important;
            white-space: normal !important;
            vertical-align: middle !important;
            padding: 12px 10px !important;
        }

        .bloque-buscador input {
            outline: none !important;
            border: 2px solid #ccc !important;
            border-radius: 6px !important;
            padding: 8px 12px;
            transition: all 0.2s ease !important;
        }

        .bloque-buscador input:focus {
            border-color: #8a2be2 !important;
            box-shadow: 0 0 8px rgba(138, 43, 226, 0.25) !important;
        }

        .badge-rol {
            display: inline-block;
            padding: 5px 12px; 
            border-radius: 6px; 
            font-size: 14px; 
            font-weight: 600;
            text-transform: capitalize;
        }
    </style>
</head>
<body>

    <nav class="navbar" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 50px; background-color: #9b59b6; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
        <div style="flex: 1; display: flex; justify-content: flex-start; align-items: center;">
            <a href="panel.php" style="display: flex; align-items: center; text-decoration: none;">
                <img src="../imagenes/findapet.jpeg" alt="FindAPet" style="height: 75px; width: auto; border-radius: 8px; object-fit: contain; background: white; padding: 4px;">
            </a>
        </div>

        <div class="nav-links" style="flex: 2; display: flex; justify-content: center; gap: 35px;">
            <a href="panel.php">Informes</a>
            <a href="gestion_de_adopciones_de_animales.php">Adopciones</a>
            
            <a href="gestion_de_criadores_de_animales.php">Criadores</a>
            
            <a href="gestion_de_usuarios.php" style="border-bottom: 2px solid white; font-weight:600;">Usuarios</a>
        </div>

        <div style="flex: 1; display: flex; justify-content: flex-end; align-items: center;">
            <a href="salir.php" class="btn-salir">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="contenedor-panel">
        
        <div class="seccion-tablon">
            <h2>👥 Control de Usuarios Registrados</h2><br>
            <p class="subtitulo">Visualiza todos los usuarios del sistema y elimina cuentas si es necesario.</p><br>
            
            <div class="bloque-buscador">
                <form method="POST" action="gestion_de_usuarios.php">
                    <input type="text" name="b_nombre" placeholder="Buscar por nombre..." value="<?php echo htmlspecialchars($buscar_nombre); ?>">
                    <input type="text" name="b_email" placeholder="Buscar por email..." value="<?php echo htmlspecialchars($buscar_email); ?>">
                    <button type="submit" class="btn-lupa">🔍 Filtrar</button>
                </form>
            </div><br>
            
            <?php echo $mensaje; ?>

            <?php if (empty($usuarios_registrados)): ?>
                <p style="font-size: 16px; color: #000000; padding: 10px 0;">No se encontraron usuarios registrados.</p>
            <?php else: ?>
                <table class="tabla-moderacion">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th style="width: 180px; text-align: left;">Nombre Completo</th>
                            <th style="width: 130px; text-align: left;">Username</th>
                            <th style="width: 220px; text-align: left;">Correo Electrónico</th>
                            <th style="width: 120px;">Teléfono</th>
                            <th style="width: 140px;">Rol</th>
                            <th style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios_registrados as $user): ?>
                            <tr class="fila-datos">
                                <td style="text-align: center; font-weight: bold; color: #7f8c8d;">
                                    #<?php echo $user['id']; ?>
                                </td>
                                
                                <td style="text-align: left; font-weight: 600; color: #000000;">
                                    <?php echo !empty($user['nombre_completo']) ? htmlspecialchars($user['nombre_completo']) : '<i style="color:#aaa;">Sin especificar</i>'; ?>
                                </td>

                                <td style="text-align: left; color: #000000; font-weight: 600;">
                                    <?php echo htmlspecialchars($user['user']); ?>
                                </td>
                                
                                <td style="text-align: left; color: #2c3e50;">
                                    <?php echo !empty($user['email']) ? htmlspecialchars($user['email']) : '<i style="color:#aaa;">Sin email</i>'; ?>
                                </td>
                                
                                <td style="text-align: center; color: #555555;">
                                    <?php echo !empty($user['telefono']) ? htmlspecialchars($user['telefono']) : '—'; ?>
                                </td>
                                
                                <td style="text-align: center;">
                                    <?php 
                                    $text_color = '#000000';

                                    if ($user['rol'] === 'administrador') {
                                        $bg_color = '#e8daef'; 
                                    } elseif ($user['rol'] === 'empleado') {
                                        $bg_color = '#d6eaf8'; 
                                    } else {
                                        $bg_color = '#d4efdf'; 
                                    }
                                    ?>
                                    <span class="badge-rol" style="background-color: <?php echo $bg_color; ?>; color: <?php echo $text_color; ?>;">
                                        <?php echo htmlspecialchars($user['rol']); ?>
                                    </span>
                                </td>
                                
                                <td style="text-align: center;">
                                    <div class="contenedor-acciones" style="justify-content: center;">
                                        <a href="gestion_de_usuarios.php?accion=eliminar&id=<?php echo $user['id']; ?>" class="btn-acc btn-b" onclick="return confirm('¿Eliminar permanentemente al usuario «<?php echo htmlspecialchars($user['user']); ?>»?');" style="margin: 0;">Eliminar</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>