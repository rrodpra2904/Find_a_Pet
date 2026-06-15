<?php 
// 1. Control de seguridad: Verifico que solo los administradores puedan ver estos informes.
include 'seguridad.php'; 

// 2. Bloque general para estadísticas de todo el sistema
try {
    // Cuenta total de Animales (página web adopción de animales)
    include '../adopciones_de_animales/conexion.php'; 
    $consulta_anim = $db->query("SELECT COUNT(*) FROM animales");
    $total_animales = $consulta_anim->fetchColumn();
    
    // Cuenta total de Criadores
    include '../criadores_de_animales/conexion.php'; 
    $consulta_criad = $db->query("SELECT COUNT(*) FROM criadores_de_animales");
    $total_criadores = $consulta_criad->fetchColumn();

    // Cuenta total de Anuncios en el Tablón (Pendientes + Verificados)
    include '../conexion_db.php'; 
    $consulta_tablon_tot = $db->query("SELECT COUNT(*) FROM tablon_de_adopciones");
    $total_tablon = $consulta_tablon_tot->fetchColumn();

    // Cuenta total de Usuarios registrados en el sistema
    $consulta_user = $db->query("SELECT COUNT(*) FROM usuarios"); 
    $total_usuarios = $consulta_user->fetchColumn();

} catch (PDOException $e) {
    $total_animales = isset($total_animales) ? $total_animales : 0;
    $total_criadores = isset($total_criadores) ? $total_criadores : 0;
    $total_tablon = 0;
    $total_usuarios = 0;
}

// ====================================================================
// GESTIÓN DEL TABLÓN DE ADOPCIONES: VERIFICAR, BORRAR Y EDITAR
// ====================================================================
$mensaje_tablon = "";
$animalesTablon = [];
$animal_a_editar = null;

include '../conexion_db.php';

// A. Procesar la actualización de los datos editados (Formulario POST con soporte de Imagen)
if (isset($_POST['guardar_edicion'])) {
    $id_editar = intval($_POST['id_anuncio']);
    $nuevo_nombre = trim($_POST['nombre']);
    $nueva_especie = trim($_POST['especie']);
    $nueva_raza = trim($_POST['raza']);
    $nueva_ubicacion = trim($_POST['ubicacion']);
    $nueva_descripcion = trim($_POST['descripcion']);
    $nuevo_telefono = trim($_POST['telefono']);
    $nuevo_correo = trim($_POST['correo_contacto']);
    $nueva_edad = trim($_POST['edad']);
    $nuevo_tamano = trim($_POST['tamano']);

    try {
        // Primero vemos si se ha subido una nueva foto
        if (!empty($_FILES['nueva_foto']['name'])) {
            $nombre_foto = time() . "_" . $_FILES['nueva_foto']['name'];
            $ruta_destino = "../usuarios/imagenes/animales/" . $nombre_foto;
            
            // Mover el archivo subido
            if (move_uploaded_file($_FILES['nueva_foto']['tmp_tmp_name'] ?? $_FILES['nueva_foto']['tmp_name'], $ruta_destino)) {
                // Actualizamos datos incluyendo la nueva foto
                $stmtUpdate = $db->prepare("UPDATE tablon_de_adopciones SET nombre = :nombre, especie = :especie, raza = :raza, ubicacion = :ubicacion, descripcion = :descripcion, telefono = :telefono, correo_contacto = :correo_contacto, edad = :edad, tamano = :tamano, foto = :foto WHERE id = :id");
                $stmtUpdate->execute([
                    ':nombre'          => $nuevo_nombre,
                    ':especie'         => $nueva_especie,
                    ':raza'            => $nueva_raza,
                    ':ubicacion'       => $nueva_ubicacion,
                    ':descripcion'     => $nueva_descripcion,
                    ':telefono'        => $nuevo_telefono,
                    ':correo_contacto' => $nuevo_correo,
                    ':edad'            => $nueva_edad,
                    ':tamano'          => $nuevo_tamano,
                    ':foto'            => $nombre_foto,
                    ':id'              => $id_editar
                ]);
            }
        } else {
            // Si no se cambia la foto, actualizamos el resto de campos normales
            $stmtUpdate = $db->prepare("UPDATE tablon_de_adopciones SET nombre = :nombre, especie = :especie, raza = :raza, ubicacion = :ubicacion, descripcion = :descripcion, telefono = :telefono, correo_contacto = :correo_contacto, edad = :edad, tamano = :tamano WHERE id = :id");
            $stmtUpdate->execute([
                ':nombre'          => $nuevo_nombre,
                ':especie'         => $nueva_especie,
                ':raza'            => $nueva_raza,
                ':ubicacion'       => $nueva_ubicacion,
                ':descripcion'     => $nueva_descripcion,
                ':telefono'        => $nuevo_telefono,
                ':correo_contacto' => $nuevo_correo,
                ':edad'            => $nueva_edad,
                ':tamano'          => $nuevo_tamano,
                ':id'              => $id_editar
            ]);
        }
        $mensaje_tablon = "<p style='color: #000000; font-weight: bold; font-family: \"Poppins\", sans-serif; margin-bottom: 20px; font-size: 16px; letter-spacing: 0.3px;'>📝 Datos del anuncio modificados correctamente.</p>";
    } catch (PDOException $e) {
        $mensaje_tablon = "<p style='color: red; font-family: \"Poppins\", sans-serif; margin-bottom: 20px;'>Error al actualizar: " . $e->getMessage() . "</p>";
    }
}

// B. Procesar acciones por URL (GET)
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id_modulo = intval($_GET['id']);
    
    // Acción: Verificar
    if ($_GET['accion'] === 'verificar') {
        try {
            // 1. Cambiar estado a verificado en la tabla del tablón
            $stmt = $db->prepare("UPDATE tablon_de_adopciones SET verificado = 1 WHERE id = :id");
            $stmt->execute([':id' => $id_modulo]);

            // 2. Obtener los datos completos de ese anuncio
            $stmtData = $db->prepare("SELECT * FROM tablon_de_adopciones WHERE id = :id");
            $stmtData->execute([':id' => $id_modulo]);
            $datos_animal = $stmtData->fetch(PDO::FETCH_ASSOC);

            if ($datos_animal) {
                // 3. Conectar explícitamente a la base de datos de la web pública de adopciones
                include '../adopciones_de_animales/conexion.php';
                
                // Evitar duplicados comprobando si ya existe por nombre y raza
                $stmtCheck = $db->prepare("SELECT COUNT(*) FROM animales WHERE nombre = :nombre AND raza = :raza");
                $stmtCheck->execute([
                    ':nombre' => $datos_animal['nombre'],
                    ':raza'   => $datos_animal['raza']
                ]);
                
                if ($stmtCheck->fetchColumn() == 0) {
                    // Insertar con todos los campos correspondientes de tu tabla 'animales'
                    $stmtAdopcion = $db->prepare("INSERT INTO animales (nombre, especie, raza, ubicacion, foto, descripcion, telefono, correo_contacto, edad, tamano) VALUES (:nombre, :especie, :raza, :ubicacion, :foto, :descripcion, :telefono, :correo_contacto, :edad, :tamano)");
                    $stmtAdopcion->execute([
                        ':nombre'          => $datos_animal['nombre'],
                        ':especie'         => $datos_animal['especie'],
                        ':raza'            => !empty($datos_animal['raza']) ? $datos_animal['raza'] : 'Mestizo',
                        ':ubicacion'       => $datos_animal['ubicacion'],
                        ':foto'            => !empty($datos_animal['foto']) ? $datos_animal['foto'] : '',
                        ':descripcion'     => !empty($datos_animal['descripcion']) ? $datos_animal['descripcion'] : '',
                        ':telefono'        => !empty($datos_animal['telefono']) ? $datos_animal['telefono'] : '',
                        ':correo_contacto' => !empty($datos_animal['correo_contacto']) ? $datos_animal['correo_contacto'] : '',
                        ':edad'            => !empty($datos_animal['edad']) ? $datos_animal['edad'] : '',
                        ':tamano'          => !empty($datos_animal['tamano']) ? $datos_animal['tamano'] : ''
                    ]);
                }
            }

            // Restablecer la conexión original del tablón por si acaso
            include '../conexion_db.php';
            $mensaje_tablon = "<p style='color: green; font-weight: bold; font-family: \"Poppins\", sans-serif; margin-bottom: 20px; font-size: 16px;'>✅ Anuncio verificado y publicado en la web con éxito.</p>";
        } catch (PDOException $e) {
            include '../conexion_db.php';
            $mensaje_tablon = "<p style='color: red; font-weight: bold; font-family: \"Poppins\", sans-serif; margin-bottom: 20px;'>❌ Error al subir a la web de adopciones: " . $e->getMessage() . "</p>";
        }
    }
    
    // Acción: Quitar Verificación
    if ($_GET['accion'] === 'desverificar') {
        try {
            $stmt = $db->prepare("UPDATE tablon_de_adopciones SET verificado = 0 WHERE id = :id");
            $stmt->execute([':id' => $id_modulo]);

            $stmtData = $db->prepare("SELECT nombre FROM tablon_de_adopciones WHERE id = :id");
            $stmtData->execute([':id' => $id_modulo]);
            $datos_animal = $stmtData->fetch(PDO::FETCH_ASSOC);

            if ($datos_animal) {
                include '../adopciones_de_animales/conexion.php';
                $stmtDel = $db->prepare("DELETE FROM animales WHERE nombre = :nombre");
                $stmtDel->execute([':nombre' => $datos_animal['nombre']]);
            }

            include '../conexion_db.php';
            $mensaje_tablon = "<p style='color: orange; font-weight: bold; font-family: \"Poppins\", sans-serif; margin-bottom: 20px; font-size: 16px;'>⏳ Anuncio quitado de la web pública.</p>";
        } catch (PDOException $e) {
            include '../conexion_db.php';
            $mensaje_tablon = "<p style='color: red; font-family: \"Poppins\", sans-serif; margin-bottom: 20px;'>Error: " . $e->getMessage() . "</p>";
        }
    }

    // Acción: Borrar
    if ($_GET['accion'] === 'borrar') {
        try {
            $stmtFoto = $db->prepare("SELECT foto, nombre FROM tablon_de_adopciones WHERE id = :id");
            $stmtFoto->execute([':id' => $id_modulo]);
            $animal_foto = $stmtFoto->fetch(PDO::FETCH_ASSOC);
            
            if ($animal_foto) {
                if (!empty($animal_foto['foto'])) {
                    $ruta_foto = "../usuarios/imagenes/animales/" . $animal_foto['foto'];
                    if (file_exists($ruta_foto)) {
                        unlink($ruta_foto);
                    }
                }
                include '../adopciones_de_animales/conexion.php';
                $stmtDel = $db->prepare("DELETE FROM animales WHERE nombre = :nombre");
                $stmtDel->execute([':nombre' => $animal_foto['nombre']]);
            }

            include '../conexion_db.php';
            $stmtBorrar = $db->prepare("DELETE FROM tablon_de_adopciones WHERE id = :id");
            $stmtBorrar->execute([':id' => $id_modulo]);
            $mensaje_tablon = "<p style='color: red; font-weight: bold; font-family: \"Poppins\", sans-serif; margin-bottom: 20px; font-size: 16px;'>🗑️ Anuncio eliminado por completo.</p>";
        } catch (PDOException $e) {
            include '../conexion_db.php';
            $mensaje_tablon = "<p style='color: red; font-family: \"Poppins\", sans-serif; margin-bottom: 20px;'>Error al borrar: " . $e->getMessage() . "</p>";
        }
    }

    if ($_GET['accion'] === 'editar') {
        $stmtBuscar = $db->prepare("SELECT * FROM tablon_de_adopciones WHERE id = :id");
        $stmtBuscar->execute([':id' => $id_modulo]);
        $animal_a_editar = $stmtBuscar->fetch(PDO::FETCH_ASSOC);
    }
}

// C. Control de Filtros
$buscar_nombre = isset($_POST['b_nombre']) ? trim($_POST['b_nombre']) : "";
$buscar_raza = isset($_POST['b_raza']) ? trim($_POST['b_raza']) : "";
$buscar_ubicacion = isset($_POST['b_ubicacion']) ? trim($_POST['b_ubicacion']) : "";
$buscar_usuario = isset($_POST['b_usuario']) ? trim($_POST['b_usuario']) : "";
$buscar_estado = isset($_POST['b_estado']) ? $_POST['b_estado'] : "";

try {
    $sql = "SELECT * FROM tablon_de_adopciones WHERE 1=1";
    $params = [];

    if (!empty($buscar_nombre)) { $sql .= " AND nombre LIKE :nombre"; $params[':nombre'] = '%' . $buscar_nombre . '%'; }
    if (!empty($buscar_raza)) { $sql .= " AND raza LIKE :raza"; $params[':raza'] = '%' . $buscar_raza . '%'; }
    if (!empty($buscar_ubicacion)) { $sql .= " AND ubicacion LIKE :ubicacion"; $params[':ubicacion'] = '%' . $buscar_ubicacion . '%'; }
    if (!empty($buscar_usuario)) { $sql .= " AND usuario_email LIKE :usuario"; $params[':usuario'] = '%' . $buscar_usuario . '%'; }
    if ($buscar_estado !== "") { $sql .= " AND verificado = :verificado"; $params[':verificado'] = intval($buscar_estado); }

    $sql .= " ORDER BY id DESC";
    $stmtTablon = $db->prepare($sql);
    $stmtTablon->execute($params);
    $animalesTablon = $stmtTablon->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $animalesTablon = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informes - FindAPet</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles_panel.css">
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
            <a href="gestion_de_usuarios.php">Usuarios</a>
        </div>

        <div style="flex: 1; display: flex; justify-content: flex-end; align-items: center;">
            <a href="salir.php" class="btn-salir">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="contenedor-panel">
        
        <h1 class="titulo-principal" style="text-align: center; margin-bottom: 35px; color: #000000; font-weight: 600; font-size: 28px; letter-spacing: 0.5px;">Informe de Actividad del Sistema</h1>

        <div class="grid-informes">
            <div class="tarjeta"><div class="icono">🐶</div><div class="numero"><?php echo $total_animales; ?></div><h3>Animales</h3></div>
            <div class="tarjeta"><div class="icono">🏠</div><div class="numero"><?php echo $total_criadores; ?></div><h3>Criadores</h3></div>
            <div class="tarjeta"><div class="icono">📋</div><div class="numero"><?php echo $total_tablon; ?></div><h3>Avisos</h3></div>
            <div class="tarjeta"><div class="icono">👥</div><div class="numero"><?php echo $total_usuarios; ?></div><h3>Usuarios</h3></div>
        </div>

        <div class="seccion-tablon">
            <h2>📋 Moderación del Tablón de Adopciones</h2>
            <p class="subtitulo">Gestiona las publicaciones: puedes verificar los datos, corregir erratas o eliminarlas.</p>
            
            <div class="bloque-buscador">
                <form method="POST" action="panel.php">
                    <input type="text" name="b_nombre" placeholder="Nombre..." value="<?php echo $buscar_nombre; ?>">
                    <input type="text" name="b_raza" placeholder="Raza..." value="<?php echo $buscar_raza; ?>">
                    <input type="text" name="b_ubicacion" placeholder="📍 Ubicación..." value="<?php echo $buscar_ubicacion; ?>">
                    <input type="text" name="b_usuario" placeholder="✉️ Email..." value="<?php echo $buscar_usuario; ?>">
                    
                    <select name="b_estado">
                        <option value="">-- Todos los estados --</option>
                        <option value="1" <?php if($buscar_estado === "1") echo "selected"; ?>>Verificados</option>
                        <option value="0" <?php if($buscar_estado === "0") echo "selected"; ?>>Pendientes</option>
                    </select>
                    
                    <button type="submit" class="btn-lupa">🔍 Buscar</button>
                </form>
            </div>
            
            <?php echo $mensaje_tablon; ?>

            <?php if ($animal_a_editar): ?>
                <div class="form-edicion-rapida">
                    <h3 style="margin-top:0; font-weight:600; margin-bottom: 12px; color:#3498db; font-size: 19px;">✏️ Corregir anuncio de: <?php echo $animal_a_editar['nombre']; ?></h3>
                    <form method="POST" action="panel.php" enctype="multipart/form-data">
                        <input type="hidden" name="id_anuncio" value="<?php echo $animal_a_editar['id']; ?>">
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 12px;">
                            <div class="form-group"><label>Nombre</label><input type="text" name="nombre" value="<?php echo $animal_a_editar['nombre']; ?>" required></div>
                            <div class="form-group"><label>Especie</label><input type="text" name="especie" value="<?php echo $animal_a_editar['especie']; ?>" required></div>
                            <div class="form-group"><label>Raza</label><input type="text" name="raza" value="<?php echo $animal_a_editar['raza']; ?>"></div>
                            <div class="form-group"><label>📍 Ubicación</label><input type="text" name="ubicacion" value="<?php echo $animal_a_editar['ubicacion']; ?>" required></div>
                            <div class="form-group"><label>📞 Teléfono</label><input type="text" name="telefono" value="<?php echo $animal_a_editar['telefono'] ?? ''; ?>"></div>
                            <div class="form-group"><label>✉️ Correo</label><input type="email" name="correo_contacto" value="<?php echo $animal_a_editar['correo_contacto'] ?? ''; ?>"></div>
                            <div class="form-group"><label>⏳ Edad</label><input type="text" name="edad" value="<?php echo $animal_a_editar['edad'] ?? ''; ?>"></div>
                            <div class="form-group"><label>⚖️ Tamaño</label><input type="text" name="tamano" value="<?php echo $animal_a_editar['tamano'] ?? ''; ?>"></div>
                            <div class="form-group"><label>📷 Cambiar Foto (Opcional)</label><input type="file" name="nueva_foto" accept="image/*"></div>
                        </div>

                        <div style="margin-bottom: 12px;">
                            <label style="display: block; font-weight:600; margin-bottom:4px; font-size:15px;">📝 Historia / Descripción:</label>
                            <textarea name="descripcion" rows="3" required><?php echo $animal_a_editar['descripcion'] ?? ''; ?></textarea>
                        </div>

                        <button type="submit" name="guardar_edicion" class="btn-guardar">Guardar cambios</button>
                        <a href="panel.php" style="font-size: 15px; color: #666; margin-left: 12px; text-decoration: none;">Cancelar</a>
                    </form>
                </div>
            <?php endif; ?>

            <?php if (empty($animalesTablon)): ?>
                <p style="font-size: 16px; color: #000000; padding: 10px 0;">No hay avisos registrados en el tablón.</p>
            <?php else: ?>
                <table class="tabla-moderacion">
                    <thead>
                        <tr>
                            <th style="width: 7%;">Foto</th>
                            <th style="width: 18%; text-align: left;">Mascota</th>
                            <th style="width: 12%;">Ubicación</th>
                            <th style="width: 18%;">Contacto</th>
                            <th style="width: 13%;">Usuario</th>
                            <th style="width: 12%;">Estado</th>
                            <th style="width: 20%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($animalesTablon as $animal): ?>
                            <tr class="fila-datos">
                                <td style="text-align: center;">
                                    <?php 
                                    $ruta_foto_cliente = "../usuarios/imagenes/animales/" . $animal['foto'];
                                    if (!empty($animal['foto']) && file_exists($ruta_foto_cliente)): ?>
                                        <img src="<?php echo $ruta_foto_cliente; ?>" class="img-miniatura" alt="Mascota">
                                    <?php else: ?>
                                        <div class="no-foto-icono">🐾</div>
                                    <?php endif; ?>
                                </td>
                                
                                <td style="text-align: left;">
                                    <div class="nombre-mascota"><?php echo $animal['nombre']; ?></div>
                                    <div class="detalles-mascota">
                                        <b>Esp:</b> <?php echo $animal['especie']; ?> <br>
                                        <b>Raza:</b> <?php echo !empty($animal['raza']) ? $animal['raza'] : 'Mestizo'; ?><br>
                                        <b>Edad:</b> <?php echo !empty($animal['edad']) ? $animal['edad'] : '-'; ?> | 
                                        <b>Tam:</b> <?php echo !empty($animal['tamano']) ? $animal['tamano'] : '-'; ?>
                                    </div>
                                </td>
                                
                                <td style="font-weight: 500; color: #2c3e50;">
                                    <?php echo $animal['ubicacion']; ?>
                                </td>
                                
                                <td style="font-size: 14px; line-height: 1.5; word-break: break-all;">
                                    <b>Tlf:</b> <?php echo !empty($animal['telefono']) ? $animal['telefono'] : 'Sin tlf'; ?><br>
                                    <span style="color:#7f8c8d; font-size:13px;"><?php echo $animal['correo_contacto'] ?? 'Sin correo'; ?></span>
                                </td>

                                <td style="font-size: 14px; color: #555555; word-break: break-all;">
                                    <?php echo $animal['usuario_email']; ?>
                                </td>
                                
                                <td>
                                    <?php if ($animal['verificado'] == 1): ?>
                                        <span class="badge-estado estado-activo">Publicado</span>
                                    <?php else: ?>
                                        <span class="badge-estado estado-pendiente">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td>
                                    <div class="contenedor-acciones">
                                        <?php if ($animal['verificado'] == 0): ?>
                                            <a href="panel.php?accion=verificar&id=<?php echo $animal['id']; ?>" class="btn-acc btn-v">Publicar</a>
                                        <?php else: ?>
                                            <a href="panel.php?accion=desverificar&id=<?php echo $animal['id']; ?>" class="btn-acc btn-qv">Bajar</a>
                                        <?php endif; ?>
                                        
                                        <a href="panel.php?accion=editar&id=<?php echo $animal['id']; ?>" class="btn-acc btn-e">Editar</a>
                                        <a href="panel.php?accion=borrar&id=<?php echo $animal['id']; ?>" class="btn-acc btn-b" onclick="return confirm('¿Borrar anuncio?');">Borrar</a>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr class="fila-descripcion">
                                <td colspan="7">
                                    <div class="caja-subtabla-desc">
                                        <strong>📝 Descripción:</strong> 
                                        <?php echo !empty($animal['descripcion']) ? $animal['descripcion'] : '<i>Sin descripción escrita.</i>'; ?>
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