<?php 

session_start();
require '../conexion_db.php'; 

if (!isset($_SESSION['usuarioAutenticado']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: login_usuarios.php");
    exit();
}

$nombreUsuario = $_SESSION['usuarioAutenticado'];
$mensaje_exito_redireccion = null;
$errores = []; 

if (isset($_GET['solicitud_ok'])) {
    $mensaje_exito_redireccion = "📩 Solicitud registrada. El aviso ha sido enviado al panel del administrador. Tu cartilla veterinaria oficial aparecerá al final de tu sección hasta que sea verificada.";
}
if (isset($_GET['registro_ok'])) {
    $mensaje_exito_redireccion = "✨ ¡Animal y Cartilla Veterinaria registrados con éxito!";
}
if (isset($_GET['perfil_ok'])) {
    $mensaje_exito_redireccion = "✨ Datos personales actualizados correctamente.";
}
if (isset($_GET['borrado_ok'])) {
    $mensaje_exito_redireccion = "🗑️ Anuncio eliminado correctamente.";
}


if (isset($_POST['eliminar_animal']) && isset($_POST['id_animal_borrar'])) {
    $id_animal_a_borrar = intval($_POST['id_animal_borrar']);
    $usuario_actual = isset($nombreUsuario) ? trim($nombreUsuario) : '';
    
    if ($id_animal_a_borrar > 0 && !empty($usuario_actual)) {
        try {
            
            $sql_verificar = "SELECT foto, usuario_email FROM tablon_de_adopciones WHERE id = :id";
            $stmt_verificar = $db->prepare($sql_verificar);
            $stmt_verificar->execute([':id' => $id_animal_a_borrar]);
            $animal_existente = $stmt_verificar->fetch(PDO::FETCH_ASSOC);

            if ($animal_existente) {
                $propietario_anuncio = trim($animal_existente['usuario_email']);

   
                if ($propietario_anuncio === $usuario_actual) {

                    if (!empty($animal_existente['foto'])) {
                        $ruta_foto = __DIR__ . "/imagenes/animales/" . $animal_existente['foto'];
                        if (file_exists($ruta_foto)) {
                            unlink($ruta_foto);
                        }
                    }

            
                    $sql_borrar = "DELETE FROM tablon_de_adopciones WHERE id = :id AND usuario_email = :email";
                    $stmt_borrar = $db->prepare($sql_borrar);
                    $stmt_borrar->execute([
                        ':id' => $id_animal_a_borrar,
                        ':email' => $usuario_actual
                    ]);

                
                    header("Location: index.php?borrado_ok=1");
                    exit();
                } else {
                
                    header("Location: index.php?error_permisos=No+tienes+permiso+para+borrar+este+anuncio");
                    exit();
                }
            }
        } catch (PDOException $e) {
            $errores[] = "Error de base de datos: " . $e->getMessage();
        }
    }
}

$ruta_carpeta_perfiles = "./imagenes/perfiles/";

if (isset($_POST['actualizar_perfil_datos'])) {
    $nuevo_nombre = trim($_POST['perfil_nombre']);
    $nuevos_apellidos = trim($_POST['perfil_apellidos']);
    $nuevo_telefono = trim($_POST['perfil_telefono']);
    $nueva_direccion = trim($_POST['perfil_direccion']);

    try {
        $sql_update_perfil = "UPDATE formulario_de_compromiso_adopciones_de_animales 
                              SET nombre = :nom, apellidos = :ape, telefono = :tel, direccion = :dir 
                              WHERE usuario_email = :email";
        $stmt_up = $db->prepare($sql_update_perfil);
        $stmt_up->execute([
            ':nom' => $nuevo_nombre,
            ':ape' => $nuevos_apellidos,
            ':tel' => $nuevo_telefono,
            ':dir' => $nueva_direccion,
            ':email' => $nombreUsuario
        ]);
        header("Location: index.php?perfil_ok=1");
        exit();
    } catch (PDOException $e) {
        $errores[] = "Error al actualizar el perfil: " . $e->getMessage();
    }
}

if (isset($_POST['subir_foto']) && isset($_FILES['foto'])) {
    $nombre_original = $_FILES['foto']['name'];
    $ruta_temporal = $_FILES['foto']['tmp_name'];
    
    $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
    $nombre_archivo_final = $nombreUsuario . "." . $extension;
    $ruta_destino_completa = $ruta_carpeta_perfiles . $nombre_archivo_final;

    $formatos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    foreach ($formatos as $ext) {
        $foto_vieja = $ruta_carpeta_perfiles . $nombreUsuario . "." . $ext;
        if (file_exists($foto_vieja)) {
            unlink($foto_vieja);
        }
    }
    move_uploaded_file($ruta_temporal, $ruta_destino_completa);
    header("Location: index.php");
    exit();
}

$foto_actual = "";
$formatos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
foreach ($formatos as $ext) {
    $archivo_a_buscar = $ruta_carpeta_perfiles . $nombreUsuario . "." . $ext;
    if (file_exists($archivo_a_buscar)) {
        $foto_actual = $archivo_a_buscar;
        break;
    }
}


$ya_ha_rellenado_adopciones = false;
$ya_ha_rellenado_criadores = false;

$fila_adopcion = null;
$fila_criador = null;

try {
    $sql_adopcion = "SELECT * FROM formulario_de_compromiso_adopciones_de_animales WHERE usuario_email = :email LIMIT 1";
    $stmt_a = $db->prepare($sql_adopcion);
    $stmt_a->bindParam(':email', $nombreUsuario);
    $stmt_a->execute();
    $fila_adopcion = $stmt_a->fetch(PDO::FETCH_ASSOC);
    if ($fila_adopcion) {
        $ya_ha_rellenado_adopciones = true;
    }
} catch (PDOException $e) {}

try {
    $sql_criador = "SELECT * FROM formulario_de_compromiso_de_criadores_de_animales WHERE usuario_email = :email LIMIT 1";
    $stmt_c = $db->prepare($sql_criador);
    $stmt_c->bindParam(':email', $nombreUsuario);
    $stmt_c->execute();
    $fila_criador = $stmt_c->fetch(PDO::FETCH_ASSOC);
    if ($fila_criador) {
        $ya_ha_rellenado_criadores = true;
    }
} catch (PDOException $e) {}


if (isset($_POST['solicitar_adopcion'])) {
    $id_animal = intval($_POST['id_animal']);
    try {
        $stmt_obs = $db->prepare("SELECT observaciones_medicas FROM tablon_de_adopciones WHERE id = :id");
        $stmt_obs->execute([':id' => $id_animal]);
        $res_obs = $stmt_obs->fetch(PDO::FETCH_ASSOC);
        
        $nueva_marca = "SOLICITUD_DE:" . $nombreUsuario . " | " . ($res_obs['observaciones_medicas'] ?? '');

        $sql_solicitud = "UPDATE tablon_de_adopciones SET observaciones_medicas = :obs, verificado = 0 WHERE id = :id";
        $stmt_sol = $db->prepare($sql_solicitud);
        $stmt_sol->execute([':obs' => $nueva_marca, ':id' => $id_animal]);

        header("Location: index.php?solicitud_ok=1");
        exit();
    } catch (PDOException $e) {}
}


if (isset($_POST['guardar_animal_tablon'])) {
    $nombre = trim($_POST['nombre']);
    $especie = trim($_POST['especie']);
    $raza = trim($_POST['raza']);
    $ubicacion = trim($_POST['ubicacion']);
    $descripcion = trim($_POST['descripcion']);
    $telefono = trim($_POST['telefono']);
    $correo_contacto = trim($_POST['correo_contacto']);
    $edad = trim($_POST['edad']);
    $tamano = trim($_POST['tamano']);
    $sexo = trim($_POST['sexo']);
    
    $num_chip = trim($_POST['num_chip']);
    $vacuna_rabia = intval($_POST['vacuna_rabia']);
    $desparasitado = intval($_POST['desparasitado']);
    $vacuna_nombre = trim($_POST['vacuna_nombre']);
    $vacuna_fecha = trim($_POST['vacuna_fecha']);
    $vacuna_lote = trim($_POST['vacuna_lote']);
    $observaciones_medicas = trim($_POST['observaciones_medicas']);
    
    $usuario_email = $nombreUsuario; 
    $verificado = 0; 
    $nombre_foto_final = NULL;

    $especie_minuscula = mb_strtolower($especie, 'UTF-8'); 
    $valores_permitidos = ['perro', 'perra', 'perrito', 'perrita', 'gato', 'gata', 'gatito', 'gatita'];
    if (!in_array($especie_minuscula, $valores_permitidos)) {
        $errores[] = "La especie debe ser perro o gato.";
    }

    if (!empty($telefono) && (!is_numeric($telefono) || strlen($telefono) !== 9)) {
        $errores[] = "El número de teléfono debe contener 9 dígitos.";
    }

    if (empty($errores)) {
        try {
            if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $nombre_foto_final = "animal_" . time() . "_" . uniqid() . "." . $extension;
                
                $directorio_destino = "./imagenes/animales/";
                if (!is_dir($directorio_destino)) {
                    mkdir($directorio_destino, 0777, true);
                }
                
                move_uploaded_file($_FILES['foto']['tmp_name'], $directorio_destino . $nombre_foto_final);
            }

            $sql_insert = "INSERT INTO tablon_de_adopciones 
                    (nombre, especie, raza, ubicacion, descripcion, telefono, correo_contacto, edad, tamano, sexo, usuario_email, verificado, foto, num_chip, vacuna_rabia, desparasitado, vacuna_nombre, vacuna_fecha, vacuna_lote, observaciones_medicas, fecha_publicacion) 
                    VALUES 
                    (:nombre, :especie, :raza, :ubicacion, :descripcion, :telefono, :correo_contacto, :edad, :tamano, :sexo, :usuario_email, :verificado, :foto, :num_chip, :vacuna_rabia, :desparasitado, :vacuna_nombre, :vacuna_fecha, :vacuna_lote, :observaciones_medicas, NOW())";
            
            $stmt = $db->prepare($sql_insert);
            $stmt->execute([
                ':nombre' => $nombre, ':especie' => $especie, ':raza' => $raza, ':ubicacion' => $ubicacion,
                ':descripcion' => $descripcion, ':telefono' => $telefono, ':correo_contacto' => $correo_contacto,
                ':edad' => $edad, ':tamano' => $tamano, ':sexo' => $sexo, ':usuario_email' => $usuario_email, ':verificado' => $verificado,
                ':foto' => $nombre_foto_final,
                ':num_chip' => $num_chip, ':vacuna_rabia' => $vacuna_rabia,
                ':desparasitado' => $desparasitado, ':vacuna_nombre' => $vacuna_nombre,
                ':vacuna_fecha' => !empty($vacuna_fecha) ? $vacuna_fecha : NULL, ':vacuna_lote' => $vacuna_lote,
                ':observaciones_medicas' => $observaciones_medicas
            ]);

            header("Location: index.php?registro_ok=1");
            exit();
        } catch (PDOException $e) {
            $errores[] = "Error al guardar el animal: " . $e->getMessage();
        }
    }
}


$animalesTablon = [];
try {
    $sql_unificada = "
        SELECT 
            id, nombre, raza, edad, descripcion, foto, 
            especie, sexo, verificado, ubicacion, num_chip, 
            vacuna_rabia, desparasitado, vacuna_nombre, observaciones_medicas, usuario_email 
        FROM tablon_de_adopciones
        
        UNION ALL
        
        SELECT 
            a.id, a.nombre, a.raza, a.edad, a.descripcion, a.imagen AS foto, 
            IFNULL(f.especie, 'Desconocida') AS especie, 
            IFNULL(s.sexo, '-') AS sexo,
            1 AS verificado, 'Sede Oficial Protectora' AS ubicacion, 'Interno' AS num_chip, 
            1 AS vacuna_rabia, 1 AS desparasitado, 'Completa Oficial' AS vacuna_nombre, 'Animal oficial listo en las instalaciones para adopción directa.' AS observaciones_medicas, 'admin@findapet.com' AS usuario_email
        FROM animales a
        LEFT JOIN filtro_animales f ON a.id = f.id_animal
        LEFT JOIN filtro_sexo_animales s ON a.id = s.id_animal
        WHERE a.adoptado = 'No' OR a.adoptado IS NULL OR a.adoptado = '' OR a.adoptado = 'no'
        ORDER BY id DESC";

    $stmt_t = $db->query($sql_unificada);
    $animalesTablon = $stmt_t->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($animalesTablon as $key => $animal) {
        if ($animal['usuario_email'] === 'admin@findapet.com') {
            $animalesTablon[$key]['ruta_foto_final'] = "./imagenes/adopciones_de_animales/" . $animal['foto'];
        } else {
            $animalesTablon[$key]['ruta_foto_final'] = "./imagenes/animales/" . $animal['foto'];
        }
    }

} catch (PDOException $e) {
    $stmt_t = $db->query("SELECT *, './imagenes/animales/' AS ruta_foto_final FROM tablon_de_adopciones ORDER BY id DESC");
    $animalesTablon = $stmt_t->fetchAll(PDO::FETCH_ASSOC);
}

$misSolicitudes = [];
try {
    $stmt_solic = $db->prepare("SELECT * FROM tablon_de_adopciones WHERE observaciones_medicas LIKE :marca ORDER BY id DESC");
    $stmt_solic->execute([':marca' => "SOLICITUD_DE:" . $nombreUsuario . "%"]);
    $misSolicitudes = $stmt_solic->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de usuarios</title>
    
    <link rel="stylesheet" href="styles_login_usuarios.css">
    <link rel="stylesheet" href="../styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Poppins', sans-serif !important;
            box-sizing: border-box;
        }
        body {
            color: #1e293b;
        }
        
        h1, h2, h3, h4, h5, h6, 
        .section-header h2, .card-manual h3, 
        .cartilla-cabecera h3, .cartilla-seccion-titulo, 
        .caja-formulario h2, .caja-formulario h3,
        .caja-donacion h3 {
            color: #1e293b !important;
        }

        label, p, strong, span, input, select, textarea {
            color: #1e293b;
        }
        .section-header {
            margin-bottom: 30px;
        }
        .section-header h2 {
            margin-bottom: 15px;
        }
        .section-header p {
            margin-bottom: 15px;
            line-height: 1.6;
            color: #475569;
        }
        
        .notificacion-fluida-top {
            background: #f0fdf4;
            border-left: 5px solid #22c55e;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: left;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }
        .notificacion-fluida-top.info {
            background: #e0f2fe;
            border-left: 5px solid #0284c7;
        }
        .notificacion-fluida-top p {
            margin: 0;
            font-size: 14px;
            font-weight: 500;
        }

        .box-formulario-subida {
            background: #ffffff;
            border: 1px solid #edf2f7;
            padding: 35px;
            border-radius: 16px;
            max-width: 800px;
            margin: 20px auto;
            text-align: left;
            box-shadow: 0 4px 15px rgba(0,0,0,0.01);
        }
        .formulario-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        .campo-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 15px;
        }
        .campo-form label {
            font-size: 13.5px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .campo-form input, .campo-form select, .campo-form textarea {
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: all 0.2s;
        }
        .campo-form input:focus, .campo-form select:focus, .campo-form textarea:focus {
            border-color: #9b59b6;
            box-shadow: 0 0 6px rgba(155, 89, 182, 0.2);
        }
        .bloque-cartilla-formulario {
            background-color: #faf6fc;
            border: 2px dashed #9b59b6;
            padding: 25px;
            border-radius: 12px;
            margin-top: 20px;
            margin-bottom: 25px;
        }
        .alerta-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .btn-action-primary, button, input[type="submit"] {
            padding: 12px 24px;
            font-size: 14px;
            border-radius: 6px;
            min-height: 44px;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
        }
        .btn-action-primary:active, button:active {
            transform: scale(0.98);
        }
        .filtro-busqueda-contenedor {
            margin-bottom: 25px;
            text-align: left;
        }
        .input-filtro-busqueda {
            width: 100%;
            max-width: 350px;
            padding: 12px;
            font-size: 14px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }

        .btn-lila-claro {
            background-color: #f3e8ff !important;
            color: #1e293b !important;
            border: 1px solid #e9d5ff !important;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .btn-lila-claro:hover {
            background-color: #e9d5ff !important;
        }

        .cartilla-digital-contenedor {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
        }
        .cartilla-cabecera {
            background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #d8b4fe;
            flex-wrap: wrap;
            gap: 10px;
        }
        .cartilla-cabecera h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .cartilla-badge-estado {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        .cartilla-cuerpo-grid {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 0;
        }
        @media (max-width: 768px) {
            .cartilla-cuerpo-grid {
                grid-template-columns: 1fr;
            }
        }
        .cartilla-lateral-foto {
            background: #f8fafc;
            padding: 25px;
            display: flex;
            flex-direction: column;
            align-items: center;
            border-right: 1px solid #e2e8f0;
        }
        @media (max-width: 768px) {
            .cartilla-lateral-foto {
                border-right: none;
                border-bottom: 1px solid #e2e8f0;
            }
        }
        .cartilla-marco-foto {
            width: 100%;
            height: 220px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
            background: #cbd5e1;
        }
        .cartilla-marco-foto img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .cartilla-nombre-mascota {
            font-size: 22px;
            font-weight: 700;
            margin: 15px 0 5px 0;
            text-align: center;
            word-wrap: break-word;
            width: 100%;
        }
        .cartilla-contenido-principal {
            padding: 30px;
            overflow: hidden;
        }
        @media (max-width: 480px) {
            .cartilla-contenido-principal {
                padding: 15px;
            }
        }
        .cartilla-seccion-titulo {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
            margin-top: 20px;
            border-bottom: 1px dashed #e9d5ff;
            padding-bottom: 6px;
        }
        .cartilla-seccion-titulo:first-of-type {
            margin-top: 0;
        }
        
        .cartilla-datos-basicos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .cartilla-dato-bloque {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 12px 16px;
            border-radius: 8px;
            min-width: 0;
            word-wrap: break-word;
        }
        .cartilla-dato-bloque span {
            display: block;
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .cartilla-dato-bloque strong {
            font-size: 14.5px;
            display: block;
        }
        
        .cartilla-clinica-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        .cartilla-clinica-bloque {
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .cartilla-clinica-bloque .icono-salud {
            font-size: 20px;
            flex-shrink: 0;
        }
        .cartilla-clinica-bloque div {
            min-width: 0;
            display: flex;
            flex-direction: column;
        }
        .cartilla-clinica-bloque div span {
            display: block;
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
        }
        .cartilla-clinica-bloque div strong {
            font-size: 14px;
            word-break: break-word;
        }
        .cartilla-vacuna-historial {
            background: #faf8fc;
            border: 1px solid #f3e8ff;
            border-left: 4px solid #9b59b6;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            word-wrap: break-word;
        }
        .cartilla-vacuna-historial p {
            margin: 4px 0;
            font-size: 13.5px;
        }
        .cartilla-notes-medicas, .cartilla-notas-medicas {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 16px 20px;
            border-radius: 8px;
            font-size: 13.5px;
            line-height: 1.6;
            word-wrap: break-word;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="navbar-brand">
        <img src="../imagenes/findapet.jpeg" alt="Logo Find a Pet">
    </div>
    <div class="menu-enlaces">
        <button id="btn-instrucciones" class="activo" onclick="cambiarPestana(event, 'instrucciones')">Instrucciones</button>
        <button id="btn-tablon" onclick="cambiarPestana(event, 'tablon')">Tablón de Adopciones</button>
        <button id="btn-adopciones" onclick="cambiarPestana(event, 'adopciones')">Adopciones</button>
        <button id="btn-formularios" onclick="cambiarPestana(event, 'formularios')">Mis Formularios</button>
        <button id="btn-cartilla" onclick="cambiarPestana(event, 'cartilla')">Cartilla Veterinaria</button>
        <button id="btn-perfil" onclick="cambiarPestana(event, 'perfil')">Mi Perfil</button>
    </div>
    <div class="navbar-spacer"></div>
</nav>

<div class="main-wrapper">

    <?php if ($mensaje_exito_redireccion): ?>
        <?php $clase_tipo = (strpos($mensaje_exito_redireccion, '📩') !== false) ? 'info' : ''; ?>
        <div class="notificacion-fluida-top <?php echo $clase_tipo; ?>">
            <p><?php echo $mensaje_exito_redireccion; ?></p>
        </div>
    <?php endif; ?>

    <div id="instrucciones" class="contenido-pestana activo">
        <div class="section-header">
            <h2>¡Hola, <?php echo $nombreUsuario; ?>!</h2>
            <p>Aquí tienes una pequeña guía para conocer las funciones disponibles dentro del panel.</p>
        </div>
        
        <div class="cards-grid">
            <div class="card-manual">
                <div class="card-icon">📋</div>
                <div class="card-text">
                    <h3>Instrucciones</h3>
                    <p>En esta sección encontrarás información básica sobre el funcionamiento del panel y sus apartados.</p>
                </div>
            </div>

            <div class="card-manual">
                <div class="card-icon">📢</div>
                <div class="card-text">
                    <h3>Tablón de Adopciones</h3>
                    <p>Aquí puedes consultar animales disponibles antes de que aparezcan en la web pública o subir un aviso si has encontrado un animal perdido o no puedes hacerte cargo de él. Los animales verificados aparecerán marcados en <strong>color verde</strong>.</p>
                </div>
            </div>

            <div class="card-manual">
                <div class="card-icon">📝</div>
                <div class="card-text">
                    <h3>Adopciones</h3>
                    <p>Para poder adoptar, primero tendrás que rellenar el formulario de compromiso. Solo es necesario hacerlo una vez. Después podrás acceder al catálogo de animales disponibles.</p>
                </div>
            </div>

            <div class="card-manual">
                <div class="card-icon">📂</div>
                <div class="card-text">
                    <h3>Mis Formularios</h3>
                    <p>Aquí podrás consultar los formularios enviados anteriormente y descargarlos en PDF cuando lo necesites.</p>
                </div>
            </div>

            <div class="card-manual">
                <div class="card-icon">🐾🩺</div>
                <div class="card-text">
                    <h3>Cartilla Veterinaria</h3>
                    <p>En esta sección podrás consultar la información veterinaria de tus mascotas, como vacunas, raza, edad, desparasitaciones, etc.</p>
                </div>
            </div>

            <div class="card-manual">
                <div class="card-icon">⚙️</div>
                <div class="card-text">
                    <h3>Mi Perfil</h3>
                    <p>Desde aquí podrás modificar tus datos personales, cambiar la imagen de perfil, cerrar sesión o eliminar tu cuenta.</p>
                </div>
            </div>
        </div>

        <div style="margin-top: 40px; padding: 25px; background-color: #ffffff; border: 1px solid #edf2f7; border-left: 5px solid #9b59b6; border-radius: 12px; text-align: left; box-shadow: 0 4px 12px rgba(0,0,0,0.02);">
            <h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600;"><span style="font-size: 22px; vertical-align: middle;">🏆</span> Sección de Criadores Verificados</h3>
            <p style="margin: 0 0 25px 0; font-size: 13.5px; color: #7f8c8d; line-height: 1.6;">Si estás buscando contactar con un criador profesional verificado para conocer ejemplares específicos, recuerda que puedes acceder al formulario de solicitud correspondiente para tramitar tu documentation de manera segura.</p>
            
            <?php if ($ya_ha_rellenado_criadores): ?>
                <a href="./criadores_de_animales/criadores_de_animales.php" class="btn-action-primary" style="display: inline-block; padding: 12px 20px; font-size: 13px; text-decoration: none; background-color: #27ae60; color: white; border-radius: 6px;">
                    🏆 Acceder a la página web de criadores de animales
                </a>
            <?php else: ?>
                <a href="./formulario_criadores_de_animales.php" class="btn-action-primary" style="display: inline-block; padding: 12px 20px; font-size: 13px; text-decoration: none; border-radius: 6px;">
                    📋 Acceder al formulario de criadores de animales
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div id="tablon" class="contenido-pestana">
        <?php if (!$ya_ha_rellenado_adopciones): ?>
            <div class="section-header">
                <h2>Formulario de Compromiso Requerido</h2>
                <p>Para poder visualizar y gestionar las mascotas dentro del Tablón de Adopciones, necesitas rellenar el formulario de compromiso reglamentario primero.</p>
            </div>
            <div style="background: #ffffff; border: 1px solid #edf2f7; padding: 35px; border-radius: 16px; text-align: left; box-shadow: 0 4px 15px rgba(0,0,0,0.01);">
                <h4 style="margin: 0 0 15px 0; font-size: 17px; font-weight: 600;">📋 ¿Qué ocurre al enviarlo?</h4>
                <p style="font-size: 13.5px; color: #6c757d; line-height: 1.6; margin: 0 0 25px 0;">
                   Cuando completes el formulario, guardaremos tus datos y desbloquearás el tablón de adopciones de animales completo de manera inmediata.
                </p>
                <hr style="border: 0; border-top: 1px solid #e0e0e0; margin: 30px 0;">
                <h4 style="margin: 0 0 15px 0; font-size: 17px; font-weight: 600;">📝 Rellena tus datos aquí:</h4>
                <div class="fondo-formulario" style="margin-top: 20px;">
                   <?php include './formulario_adopciones_de_animales.php'; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="section-header">
                <h2>Tablón de adopciones de animales</h2>
                <p>Échale un vistazo a los animales disponibles antes de que aparezcan en la página pública de adopciones. También puedes subir tú mismo un animal perdido que hayas encontrado o uno del que no puedas hacerte cargo por cualquier motivo. Antes de poder adoptarlos, primero tendremos que revisarlos y darles el visto bueno (<strong>los verás destacados en color verde</strong>).</p>
                <p>Si después de una semana nadie solicita la adopción, el anuncio pasará automáticamente a la web pública.</p>

                <div style="margin-top: 25px; background: #e8f5e9; border-left: 4px solid #2e7d32; padding: 20px; border-radius: 4px; font-size: 13.5px; color: #1b5e20; line-height: 1.6; margin-bottom: 25px;">
                    <strong>🐾 ¿Cómo funciona el proceso para adoptar desde aquí?</strong><br><br>
                    1. Elige uno de los animales que ya estén verificados (los marcados en color verde).<br><br>
                    2. Ya dispones de tu <strong>Formulario de Compromiso</strong> cumplimentado correctamente.<br><br>
                    3. Al solicitar la adopción de un elemento verificado, te facilitaremos el contacto para remitir la documentación complementaria.
                </div>
            </div>
            
  <div style="text-align: left; margin-bottom: 30px; font-family: 'Poppins', sans-serif;">
                 <?php if (!$ya_ha_rellenado_adopciones): ?>
                     <button onclick="alert('📋 Para poder subir un anuncio al tablón, primero debes rellenar el Formulario de Compromiso reglamentario.'); cambiarPestana(event, 'adopciones')" class="btn-action-primary btn-lila-claro" style="font-family: 'Poppins', sans-serif;">➕ Subir un animal con su Cartilla Veterinaria</button>
                 <?php else: ?>
                     <button onclick="cambiarPestana(null, 'pestana-subida')" class="btn-action-primary btn-lila-claro" style="font-family: 'Poppins', sans-serif;">➕ Subir un animal con su Cartilla Veterinaria</button>
                 <?php endif; ?>
            </div>

            <?php if (empty($animalesTablon)): ?>
                <div class="status-box-empty" style="padding: 30px 10px; font-family: 'Poppins', sans-serif;">
                    <span class="icon" style="font-size: 48px; display: block; margin-bottom: 15px;">📭</span>
                    <h4>El tablón está vacío ahora mismo</h4>
                    <p>No hay ninguna mascota publicada en este momento. Cuando un usuario suba un anuncio, aparecerá aquí.</p>
                </div>
            <?php else: ?>
                <div class="contenedor-animales-tablon" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; text-align: left; font-family: 'Poppins', sans-serif;">
                    <?php foreach ($animalesTablon as $animal): ?>
                        <?php 
                            $estiloCard = ($animal['verificado'] == 1) ? 'border: 2px solid #2ecc71; background-color: #e8f8f5;' : 'border: 1px solid #edf2f7; background: white;';
                            
                            $js_chip = !empty($animal['num_chip']) ? addslashes($animal['num_chip']) : 'No registrado';
                            $js_sexo = !empty($animal['sexo']) ? addslashes($animal['sexo']) : '-';
                            $js_rabia = ($animal['vacuna_rabia'] == 1) ? 'Sí' : 'No';
                            $js_desparasitado = ($animal['desparasitado'] == 1) ? 'Sí' : 'No';
                            $js_vacuna = !empty($animal['vacuna_nombre']) ? addslashes($animal['vacuna_nombre']) : 'Ninguna';
                            
                            $obs_limpias = !empty($animal['observaciones_medicas']) ? str_replace(array('SOLICITUD_DE:', '|'), '', $animal['observaciones_medicas']) : 'Ninguna';
                            $js_obs = str_replace(array("\r", "\n"), ' ', addslashes($obs_limpias));
                        ?>
                        <div class="card-animal-tablon" style="<?php echo $estiloCard; ?> padding: 0; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.01); overflow: hidden; display: flex; flex-direction: column; margin-bottom: 15px; font-family: 'Poppins', sans-serif;">
                            
                            <div style="width: 100%; height: 180px; background-color: #f1f5f9; overflow: hidden; position: relative;">
                                <?php if (!empty($animal['foto'])): ?>
                                    <?php 
                                    if (empty($animal['usuario_email']) || $animal['usuario_email'] === 'admin@findapet.com'): ?>
                                        <img src="../imagenes/adopciones_de_animales/<?php echo $animal['foto']; ?>" alt="Foto de <?php echo $animal['nombre']; ?>" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                                    <?php else: ?>
                                        <img src="./imagenes/animales/<?php echo $animal['foto']; ?>" alt="Foto de <?php echo $animal['nombre']; ?>" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                                    <?php endif; ?>
                                <?php else: ?>
                                    <img src="../imagenes/findapet.jpeg" alt="Imagen por defecto" style="width: 100%; height: 100%; object-fit: cover; display: block; opacity: 0.5;">
                                <?php endif; ?>
                            </div>

                            <div style="padding: 20px;">
                                <h4 style="margin: 0 0 12px 0; font-size: 17px; font-weight: 600; font-family: 'Poppins', sans-serif;"><?php echo $animal['nombre']; ?></h4>
                                <p style="font-size: 13px; margin: 0 0 8px 0; color: #475569; font-family: 'Poppins', sans-serif;"><strong>Especie:</strong> <?php echo $animal['especie']; ?></p>
                                <p style="font-size: 13px; margin: 0 0 8px 0; color: #475569; font-family: 'Poppins', sans-serif;"><strong>Raza:</strong> <?php echo $animal['raza']; ?></p>
                                <p style="font-size: 13px; margin: 0 0 8px 0; color: #475569; font-family: 'Poppins', sans-serif;"><strong>Sexo:</strong> <?php echo (!empty($animal['sexo']) && $animal['sexo'] !== '-') ? ucfirst($animal['sexo']) : '-'; ?></p>
                                <p style="font-size: 12px; color: #7f8c8d; margin: 0 0 15px 0; font-family: 'Poppins', sans-serif;">📍 Ubicación: <?php echo !empty($animal['ubicacion']) ? $animal['ubicacion'] : 'Sede Oficial Protectora'; ?></p>
                                
                                <?php if ($animal['verificado'] == 1): ?>
                                    <span style="color: #323332; font-weight: bold; font-size: 12px; display: inline-block; background: #d4edda; padding: 6px 10px; border-radius: 4px; margin-bottom: 15px; font-family: 'Poppins', sans-serif;">✅ Verificado por Admin</span>
                                <?php else: ?>
                                    <span style="color: #424242; font-weight: bold; font-size: 12px; display: inline-block; background: #fff3cd; padding: 6px 10px; border-radius: 4px; margin-bottom: 15px; font-family: 'Poppins', sans-serif;">⏳ Pendiente de revisión</span>
                                <?php endif; ?>

                                <button onclick="abrirCartilla('<?php echo addslashes($animal['nombre']); ?>', '<?php echo $js_chip; ?>', '<?php echo $js_sexo; ?>', '<?php echo $js_rabia; ?>', '<?php echo $js_desparasitado; ?>', '<?php echo $js_vacuna; ?>', '<?php echo $js_obs; ?>')" class="btn-lila-claro" style="width: 100%; padding: 10px; font-size: 12px; border: none; cursor: pointer; border-radius: 6px; margin-bottom: 10px; font-family: 'Poppins', sans-serif;">🩺 Ver Ficha Clínica</button>

                                <form method="POST" action="" style="margin-top: 5px;">
                                    <input type="hidden" name="id_animal" value="<?php echo $animal['id']; ?>">
                                    <button type="submit" name="solicitar_adopcion" class="btn-action-primary btn-solicitar-hover" style="width: 100%; padding: 10px; font-size: 12px; background-color: #27ae60; border: none; cursor: pointer; color: white; border-radius: 6px; font-family: 'Poppins', sans-serif;">📩 Solicitar Adopción</button>
                                </form>

                                <?php if (!empty($animal['usuario_email']) && $animal['usuario_email'] === $nombreUsuario): ?>
                                    <form method="POST" action="" style="margin-top: 8px;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar definitivamente el anuncio de <?php echo addslashes($animal['nombre']); ?>?');">
                                        <input type="hidden" name="id_animal_borrar" value="<?php echo $animal['id']; ?>">
                                        <button type="submit" name="eliminar_animal" class="btn-eliminar-hover" style="width: 100%; padding: 10px; font-size: 12px; background-color: #e74c3c; border: none; cursor: pointer; color: white; border-radius: 6px; font-weight: 600; font-family: 'Poppins', sans-serif;">🗑️  Eliminar mi Anuncio</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div id="modalCartilla" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center; backdrop-filter: blur(4px); font-family: 'Poppins', sans-serif;">
        <div style="background: white; width: 90%; max-width: 500px; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.15); animation: fadeScale 0.3s ease-out; text-align: left;">
            
            <div style="background: #8e44ad; color: white !important; padding: 22px 25px; display: flex; justify-content: space-between; align-items: center; font-family: 'Poppins', sans-serif;">
                <h3 style="margin: 0; font-size: 19px; font-weight: 700; color: white !important; font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 8px; letter-spacing: 0.5px;">🩺 Cartilla de <span id="cartillaNombre" style="color: white !important; font-weight: 700;"></span></h3>
                <span onclick="cerrarCartilla()" style="cursor: pointer; font-size: 26px; font-weight: bold; line-height: 1; color: white !important;">&times;</span>
            </div>
            
            <div style="padding: 25px; color: #334155; font-size: 14px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div style="background: #f8fafc; padding: 12px; border-radius: 8px; border-left: 4px solid #a29bfe;">
                        <strong style="color: #64748b; font-size: 11px; display: block; text-transform: uppercase;">ID / Chip</strong>
                        <span id="cartillaChip" style="font-weight: 600;"></span>
                    </div>
                    <div style="background: #f8fafc; padding: 12px; border-radius: 8px; border-left: 4px solid #a29bfe;">
                        <strong style="color: #64748b; font-size: 11px; display: block; text-transform: uppercase;">Sexo</strong>
                        <span id="cartillaSexo" style="font-weight: 600;"></span>
                    </div>
                    <div style="background: #f8fafc; padding: 12px; border-radius: 8px; border-left: 4px solid #2ecc71;">
                        <strong style="color: #64748b; font-size: 11px; display: block; text-transform: uppercase;">Vacuna Rabia</strong>
                        <span id="cartillaRabia" style="font-weight: 600;"></span>
                    </div>
                    <div style="background: #f8fafc; padding: 12px; border-radius: 8px; border-left: 4px solid #2ecc71;">
                        <strong style="color: #64748b; font-size: 11px; display: block; text-transform: uppercase;">Desparasitado</strong>
                        <span id="cartillaDesparasitado" style="font-weight: 600;"></span>
                    </div>
                </div>
                
                <div style="background: #f1f5f9; padding: 12px; border-radius: 8px; margin-bottom: 25px;">
                    <strong style="color: #475569; font-size: 12px; display: block; margin-bottom: 4px;">🦠 Última Vacuna Registrada:</strong>
                    <span id="cartillaVacuna" style="font-weight: 600; color: #1e293b;"></span>
                </div>

                <div style="background: #fff9db; padding: 18px 20px; border-radius: 8px; border: 1px solid #ffe066; font-family: 'Poppins', sans-serif; color: #000000 !important; box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);">
                    <strong style="color: #000000 !important; font-weight: 700; font-size: 13px; display: block; margin-bottom: 8px; font-family: 'Poppins', sans-serif; letter-spacing: 0.3px;">📋 Diagnóstico y Notas Clínicas:</strong>
                    <p id="cartillaObs" style="margin: 0; color: #000000 !important; line-height: 1.6; font-family: 'Poppins', sans-serif; font-size: 13.5px;"></p>
                </div>
            </div>
            
            <div style="background: #f8fafc; padding: 15px 25px; text-align: right; border-top: 1px solid #e2e8f0;">
                <button onclick="cerrarCartilla()" style="background: #8e44ad; color: white; border: none; padding: 9px 22px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px; font-family: 'Poppins', sans-serif; margin-right: 5px;">Entendido</button>
            </div>
        </div>
    </div>

    <script>
    function abrirCartilla(nombre, chip, sexo, rabia, desparasitado, vacuna, obs) {
        document.getElementById('cartillaNombre').innerText = nombre;
        document.getElementById('cartillaChip').innerText = chip;
        document.getElementById('cartillaSexo').innerText = sexo;
        document.getElementById('cartillaRabia').innerText = rabia;
        document.getElementById('cartillaDesparasitado').innerText = desparasitado;
        document.getElementById('cartillaVacuna').innerText = vacuna;
        document.getElementById('cartillaObs').innerText = obs;
        
        const modal = document.getElementById('modalCartilla');
        modal.style.display = 'flex';
    }

    function cerrarCartilla() {
        document.getElementById('modalCartilla').style.display = 'none';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('modalCartilla');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
    </script>

    <style>
    @keyframes fadeScale {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }

    .btn-solicitar-hover, .btn-eliminar-hover {
        transition: all 0.25s ease-in-out !important;
    }

    .btn-solicitar-hover:hover {
        background-color: #1e8449 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(39, 174, 96, 0.25);
    }

    
    .btn-eliminar-hover:hover {
        background-color: #c0392b !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(231, 76, 60, 0.25);
    }
    </style>


    <div id="pestana-subida" class="contenido-pestana" style="font-family: 'Poppins', sans-serif;">
        <div class="box-formulario-subida">
            <h2 style="font-weight: 700; margin-top: 0; margin-bottom: 10px; font-family: 'Poppins', sans-serif;">🐾 Añadir nuevo animal y su Cartilla Veterinaria</h2>
            <p style="color: #7f8c8d; font-size: 14px; margin-bottom: 30px; font-family: 'Poppins', sans-serif;">Rellena los datos generales del animal y su expediente de salud para guardarlo en el sistema.</p>

            <?php if (!empty($errores)): ?>
                <div class="alerta-error" style="font-family: 'Poppins', sans-serif;">
                    <strong>Por favor, corrige los siguientes campos:</strong>
                    <ul style="margin: 8px 0 0 20px; padding: 0;">
                        <?php foreach ($errores as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <script>
                    window.addEventListener('DOMContentLoaded', function() {
                        cambiarPestana(null, 'pestana-subida');
                    });
                </script>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="formulario-grid">
                    <div class="campo-form">
                        <label>Nombre de la mascota *</label>
                        <input type="text" name="nombre" placeholder="Ej: Lala, Toby" required style="font-family: 'Poppins', sans-serif;">
                    </div>
                    <div class="campo-form">
                        <label>Especie *</label>
                        <input type="text" name="especie" placeholder="Ej: Perro / Gato" required style="font-family: 'Poppins', sans-serif;">
                    </div>
                    <div class="campo-form">
                        <label>Raza</label>
                        <input type="text" name="raza" placeholder="Ej: Pomerania, Siamés" style="font-family: 'Poppins', sans-serif;">
                    </div>
                    <div class="campo-form">
                        <label>📍 Ubicación / Localidad *</label>
                        <input type="text" name="ubicacion" placeholder="Ej: Motril" required style="font-family: 'Poppins', sans-serif;">
                    </div>
                    <div class="campo-form">
                        <label>⏳ Edad </label>
                        <input type="text" name="edad" placeholder="Ej: 2 años" style="font-family: 'Poppins', sans-serif;">
                    </div>
                    <div class="campo-form">
                        <label>⚥ Sexo </label>
                        <select name="sexo" style="font-family: 'Poppins', sans-serif;">
                            <option value="-">-</option>
                            <option value="Macho">Macho</option>
                            <option value="Hembra">Hembra</option>
                        </select>
                    </div>
                    <div class="campo-form">
                        <label>⚖️ Tamaño</label>
                        <input type="text" name="tamano" placeholder="Ej: Mediano, Pequeño" style="font-family: 'Poppins', sans-serif;">
                    </div>
                    <div class="campo-form">
                        <label>📞 Teléfono de contacto</label>
                        <input type="text" name="telefono" placeholder="Ej: 600123456" style="font-family: 'Poppins', sans-serif;">
                    </div>
                    <div class="campo-form">
                        <label>✉️ Correo electrónico de contacto</label>
                        <input type="email" name="correo_contacto" placeholder="Ej: contacto@correo.com" style="font-family: 'Poppins', sans-serif;">
                    </div>
                </div>

                <div class="bloque-cartilla-formulario">
                    <h3 style="margin: 0 0 20px 0; font-size: 15px; font-weight: 700; font-family: 'Poppins', sans-serif;">🩺 Expediente Médico / Ficha de la Cartilla Veterinaria</h3>
                    <div class="formulario-grid">
                        <div class="campo-form">
                            <label>Identificador Numérico / Chip</label>
                            <input type="text" name="num_chip" placeholder="Número de Chip oficial" style="font-family: 'Poppins', sans-serif;">
                        </div>
                        <div class="campo-form">
                            <label>¿Vacuna de la Rabia?</label>
                            <select name="vacuna_rabia" style="font-family: 'Poppins', sans-serif;">
                                <option value="0">No</option>
                                <option value="1">Sí</option>
                            </select>
                        </div>
                        <div class="campo-form">
                            <label>¿Se encuentra Desparasitado?</label>
                            <select name="desparasitado" style="font-family: 'Poppins', sans-serif;">
                                <option value="0">No</option>
                                <option value="1">Sí</option>
                            </select>
                        </div>
                        <div class="campo-form">
                            <label>Nombre de la última Vacuna administrada</label>
                            <input type="text" name="vacuna_nombre" placeholder="Ej: Pentavalente, Trivalente" style="font-family: 'Poppins', sans-serif;">
                        </div>
                        <div class="campo-form">
                            <label>Fecha de la Vacunación</label>
                            <input type="date" name="vacuna_fecha" style="font-family: 'Poppins', sans-serif;">
                        </div>
                        <div class="campo-form">
                            <label>Código de lote de la Vacuna</label>
                            <input type="text" name="vacuna_lote" placeholder="Ej: LOT-2026X" style="font-family: 'Poppins', sans-serif;">
                        </div>
                    </div>
                    <div class="campo-form" style="margin-top: 15px;">
                        <label>Diagnóstico General / Observaciones Clínicas *</label>
                        <textarea name="observaciones_medicas" rows="3" placeholder="Describe el estado de salud, tratamientos o alergias del animal..." required style="font-family: 'Poppins', sans-serif;"></textarea>
                    </div>
                </div>

                <div class="campo-form" style="margin-bottom: 25px;">
                    <label>📷 Foto del animal *</label>
                    <input type="file" name="foto" accept="image/*" required style="border: none; padding: 0; font-family: 'Poppins', sans-serif;">
                </div>
                <div class="campo-form" style="margin-bottom: 30px;">
                    <label>📝 Descripción / Su historia</label>
                    <textarea name="descripcion" rows="4" placeholder="Cuéntanos la situación del animal..." style="font-family: 'Poppins', sans-serif;"></textarea>
                </div>
                <div style="display: flex; gap: 20px;">
                    <button type="submit" name="guardar_animal_tablon" class="btn-action-primary" style="padding: 12px 24px; border:none; cursor: pointer; border-radius: 6px; background-color: #22ac5c; color: white; font-family: 'Poppins', sans-serif;">Subir Anuncio completo</button>
                    <button type="button" onclick="cambiarPestana(null, 'tablon')" class="btn-action-primary" style="padding: 12px 24px; background-color: #7f8c8d; border:none; cursor: pointer; border-radius: 6px; color: white; font-family: 'Poppins', sans-serif;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="adopciones" class="contenido-pestana">
        <div class="section-header">
            <h2>Formulario de Compromiso</h2>
            <p>Necesitamos que rellenes este formulario para poder gestionar cualquier adopción en la plataforma.</p>
        </div>

        <?php if ($ya_ha_rellenado_adopciones): ?>
                    <div style="background: #ffffff; border: 1px solid #edf2f7; padding: 45px; border-radius: 166px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.01);">
                        <span style="font-size: 48px; display: block; margin-bottom: 15px;">✅</span>
                        <h3 style="margin-top: 15px; margin-bottom: 15px;">¡Ya has completado tu compromiso de adopción!</h3>
                        <p style="color: #7f8c8d; font-size: 14px; margin-bottom: 30px;">No necesitas volver a rellenar los datos. Puedes ir directamente a ver la página web de adopciones.</p>
                        
                        <a href="./adopciones_de_animales/adopciones_de_animales.php" style="text-decoration: none;">
                            <button type="button" class="btn-action-primary" style="display: inline-block; padding: 14px 28px; background-color: #27ae60; color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: 600;">
                                🐾 Acceder al tablón de adopciones de animales
                            </button>
                        </a>
                    </div>
                <?php else: ?>
            <div style="background: #ffffff; border: 1px solid #edf2f7; padding: 35px; border-radius: 16px; text-align: left; box-shadow: 0 4px 15px rgba(0,0,0,0.01);">
                <h4 style="margin: 0 0 15px 0; font-size: 17px; font-weight: 600;">📋 ¿Qué ocurre al enviarlo?</h4>
                <p style="font-size: 13.5px; color: #6c757d; line-height: 1.6; margin: 0 0 25px 0;">
                   Cuando completes el formulario, guardaremos tus datos y no tendrás que volver a introducirlos. Después serás redirigido de nuevo al panel para continuar con el proceso.
                </p>
                <hr style="border: 0; border-top: 1px solid #e0e0e0; margin: 30px 0;">
                <h4 style="margin: 0 0 15px 0; font-size: 17px; font-weight: 600;">📝 Rellena tus datos aquí:</h4>
                <div class="fondo-formulario" style="margin-top: 20px;">
                   <?php include './formulario_adopciones_de_animales.php'; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div id="formularios" class="contenido-pestana">
        <div class="section-header">
            <h2>Mis Formularios</h2>
            <p>Aquí se guardan las copias de los documentos que vayas tramitando en la plataforma. <strong>Puedes imprimir o guardar tus formularios en formato PDF pulsando el botón correspondiente.</strong></p>
        </div>

        <?php if ($ya_ha_rellenado_adopciones || $ya_ha_rellenado_criadores): ?>
            <div style="text-align: right; margin-top: 25px; margin-bottom: 35px; padding-right: 10px;">
                <button onclick="window.print()" class="btn-action-primary btn-lila-claro" style="font-size: 13.5px; padding: 12px 26px;">🖨️ Imprimir todo en PDF</button>
            </div>
        <?php endif; ?>
        
        <?php if (!$ya_ha_rellenado_adopciones && !$ya_ha_rellenado_criadores): ?>
            <div class="status-box-empty" style="padding: 30px 10px;">
                <span class="icon" style="font-size: 48px; display: block; margin-bottom: 15px;">📂</span>
                <h4>Todavía no hay ningún formulario rellenado.</h4>
                <p>Cuando completes el formulario por primera vez en la sección "Adopciones", se guardará aquí automáticamente para que puedas consultarlo o descargarlo en PDF cuando lo necesites.</p>
            </div>
        <?php else: ?>

            <?php if ($ya_ha_rellenado_adopciones && $fila_adopcion): ?>
                <div class="contenedor" style="margin-bottom: 30px;">
                    <div class="caja-formulario" style="padding: 25px; background: white; border: 1px solid #edf2f7; border-radius: 12px; text-align: left;">
                        <h2 style="margin-bottom: 15px; font-size: 20px; font-weight: 700;">Formulario de compromiso para la adopción</h2>
                        <p class="intro-principal" style="margin-bottom: 20px; font-size: 14px; color: #475569;">
                            Para garantizar una adopción segura y responsable, te pedimos que completes este breve compromiso. Tu futuro compañero te está esperando.
                        </p>
                        
                        <div class="bloque" style="margin-bottom: 25px;">
                            <h3 style="margin-bottom: 15px; font-size: 16px; font-weight: 600;">Datos del interesado</h3>
                            <div class="celda-input" style="margin-bottom: 12px; display: flex; flex-direction: column; gap: 6px;">
                                <label style="font-size: 13px; font-weight: 600;">Nombre</label>
                                <input type="text" value="<?php echo htmlspecialchars($fila_adopcion['nombre'] ?? ''); ?>" disabled style="padding: 10px; background-color: #f8f9fa; border: 1px solid #e2e8f0; border-radius: 6px;">
                            </div>
                            <div class="celda-input" style="margin-bottom: 12px; display: flex; flex-direction: column; gap: 6px;">
                                <label style="font-size: 13px; font-weight: 600;">Apellidos</label>
                                <input type="text" value="<?php echo htmlspecialchars($fila_adopcion['apellidos'] ?? ''); ?>" disabled style="padding: 10px; background-color: #f8f9fa; border: 1px solid #e2e8f0; border-radius: 6px;">
                            </div>
                            <div class="celda-input" style="margin-bottom: 12px; display: flex; flex-direction: column; gap: 6px;">
                                <label style="font-size: 13px; font-weight: 600;">Teléfono</label>
                                <input type="tel" value="<?php echo htmlspecialchars($fila_adopcion['telefono'] ?? ''); ?>" disabled style="padding: 10px; background-color: #f8f9fa; border: 1px solid #e2e8f0; border-radius: 6px;">
                            </div>
                            <div class="celda-input" style="margin-bottom: 12px; display: flex; flex-direction: column; gap: 6px;">
                                <label style="font-size: 13px; font-weight: 600;">Correo electrónico</label>
                                <input type="email" value="<?php echo htmlspecialchars($fila_adopcion['usuario_email'] ?? ''); ?>" disabled style="padding: 10px; background-color: #f8f9fa; border: 1px solid #e2e8f0; border-radius: 6px;">
                            </div>
                            <div class="celda-input" style="margin-bottom: 12px; display: flex; flex-direction: column; gap: 6px;">
                                <label style="font-size: 13px; font-weight: 600;">Dirección completa</label>
                                <input type="text" value="<?php echo htmlspecialchars($fila_adopcion['direccion'] ?? ''); ?>" disabled style="padding: 10px; background-color: #f8f9fa; border: 1px solid #e2e8f0; border-radius: 6px;">
                            </div>
                            <div class="celda-input" style="margin-bottom: 12px; display: flex; flex-direction: column; gap: 6px;">
                                <label style="font-size: 13px; font-weight: 600;">¿Qué tipo de animal quieres adoptar?</label>
                                <input type="text" value="<?php echo htmlspecialchars($fila_adopcion['tipo_animal'] ?? ''); ?>" disabled style="padding: 10px; background-color: #f8f9fa; border: 1px solid #e2e8f0; border-radius: 6px;">
                            </div>
                        </div>

                        <div class="bloque" style="margin-bottom: 25px;">
                            <h3 style="margin-bottom: 15px; font-size: 16px; font-weight: 600;">Aceptación de requisitos</h3>
                            <p class="intro-requisitos" style="margin-bottom: 15px; font-size: 13.5px; color: #475569;">
                                En Find a Pet nos tomamos muy en serio el bienestar y el futuro de los animales. Por favor, confirma que estás de acuerdo con estos compromisos básicos para asegurar que tendrá una vida estupenda a tu lado:
                            </p>
                            <div class="contenedor-checkbox" style="margin-bottom: 10px; display: flex; align-items: flex-start; gap: 8px;">
                                <input type="checkbox" checked disabled class="checkbox-morado-historial" style="margin-top: 4px;">
                                <label style="font-size: 13.5px;"> Me comprometo a garantizar su bienestar, cubriendo sus necesidades de alimentación, salud y revisiones veterinarias.</label>
                            </div>
                            <div class="contenedor-checkbox" style="margin-bottom: 10px; display: flex; align-items: flex-start; gap: 8px;">
                                <input type="checkbox" checked disabled class="checkbox-morado-historial" style="margin-top: 4px;">
                                <label style="font-size: 13.5px;">Dispondrá de un espacio adecuado y adaptado a su tamaño, así como del tiempo de atención, juego o paseos diarios que requiera su especie.</label>
                            </div>
                            <div class="contenedor-checkbox" style="margin-bottom: 10px; display: flex; align-items: flex-start; gap: 8px;">
                                <input type="checkbox" checked disabled class="checkbox-morado-historial" style="margin-top: 4px;">
                                <label style="font-size: 13.5px;">El animal convivirá con el dueño y las personas que estén viviendo con él/ella como un miembro más de la familia y bajo ningún concepto lo utilizaré para la cría o comercio.</label>
                            </div>
                            <div class="contenedor-checkbox" style="margin-bottom: 10px; display: flex; align-items: flex-start; gap: 8px;">
                                <input type="checkbox" checked disabled class="checkbox-morado-historial" style="margin-top: 4px;">
                                <label style="font-size: 13.5px;">Si por causas mayores no pudiera seguir haciéndome cargo de él, contactaré con Find a Pet o un refugio autorizado para buscarle un hogar responsable.</label>
                            </div>
                            <div class="contenedor-checkbox" style="margin-bottom: 10px; display: flex; align-items: flex-start; gap: 8px;">
                                <input type="checkbox" checked disabled class="checkbox-morado-historial" style="margin-top: 4px;">
                                <label style="font-size: 13.5px;">Confirmo que he leído y acepto todos los requisitos del formulario de compromiso.</label>
                            </div>
                        </div>

                        <div class="caja-donacion" style="padding: 20px; margin-top: 20px; background: #faf6fc; border-radius: 8px; border: 1px solid #f3e8ff;">
                            <h3 style="margin-bottom: 10px; font-size: 15px; font-weight: 600;">Donación voluntaria</h3>
                            <p style="margin-bottom: 12px; font-size: 13px;">Apartado para colaboraciones voluntarias destinadas al mantenimiento de la página web y ayuda a refugios de animales.</p>
                            <span class="telefono-donacion" style="display: block; margin-bottom: 6px; font-size: 13px; font-weight: 500;">Mantenimiento de la página web: 600 189 240</span>
                            <span class="telefono-donacion" style="display: block; font-size: 13px; font-weight: 500;">Refugios de animales: 600 378 446</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($ya_ha_rellenado_adopciones && $ya_ha_rellenado_criadores): ?>
                <div class="separador-historial" style="height: 35px;"></div>
            <?php endif; ?>

            <?php if ($ya_ha_rellenado_criadores && $fila_criador): ?>
                <div class="contenedor">
                    <div class="caja-formulario" style="padding: 25px; background: white; border: 1px solid #edf2f7; border-radius: 12px; text-align: left;">
                        <h2 style="margin-bottom: 15px; font-size: 20px; font-weight: 700;">Formulario de compromiso para criadores</h2>
                        <p class="intro-principal" style="margin-bottom: 20px; font-size: 14px; color: #475569;">
                            Para garantizar una adopción segura y responsable, te pedimos que completes este breve compromiso. Tu futuro compañero te está esperando.
                        </p>
                        
                        <div class="bloque" style="margin-bottom: 25px;">
                            <h3 style="margin-bottom: 15px; font-size: 16px; font-weight: 600;">Datos del interesado</h3>
                            <div class="celda-input" style="margin-bottom: 12px; display: flex; flex-direction: column; gap: 6px;">
                                <label style="font-size: 13px; font-weight: 600;">Nombre</label>
                                <input type="text" value="<?php echo htmlspecialchars($fila_criador['nombre'] ?? ''); ?>" disabled style="padding: 10px; background-color: #f8f9fa; border: 1px solid #e2e8f0; border-radius: 6px;">
                            </div>
                            <div class="celda-input" style="margin-bottom: 12px; display: flex; flex-direction: column; gap: 6px;">
                                <label style="font-size: 13px; font-weight: 600;">Apellidos</label>
                                <input type="text" value="<?php echo htmlspecialchars($fila_criador['apellidos'] ?? ''); ?>" disabled style="padding: 10px; background-color: #f8f9fa; border: 1px solid #e2e8f0; border-radius: 6px;">
                            </div>
                            <div class="celda-input" style="margin-bottom: 12px; display: flex; flex-direction: column; gap: 6px;">
                                <label style="font-size: 13px; font-weight: 600;">Teléfono</label>
                                <input type="tel" value="<?php echo htmlspecialchars($fila_criador['telefono'] ?? ''); ?>" disabled style="padding: 10px; background-color: #f8f9fa; border: 1px solid #e2e8f0; border-radius: 6px;">
                            </div>
                            <div class="celda-input" style="margin-bottom: 12px; display: flex; flex-direction: column; gap: 6px;">
                                <label style="font-size: 13px; font-weight: 600;">Correo electrónico</label>
                                <input type="email" value="<?php echo htmlspecialchars($fila_criador['usuario_email'] ?? ''); ?>" disabled style="padding: 10px; background-color: #f8f9fa; border: 1px solid #e2e8f0; border-radius: 6px;">
                            </div>
                            <div class="celda-input" style="margin-bottom: 12px; display: flex; flex-direction: column; gap: 6px;">
                                <label style="font-size: 13px; font-weight: 600;">Dirección completa</label>
                                <input type="text" value="<?php echo htmlspecialchars($fila_criador['direccion'] ?? ''); ?>" disabled style="padding: 10px; background-color: #f8f9fa; border: 1px solid #e2e8f0; border-radius: 6px;">
                            </div>
                            <div class="celda-input" style="margin-bottom: 12px; display: flex; flex-direction: column; gap: 6px;">
                                <label style="font-size: 13px; font-weight: 600;">¿Qué tipo de animal quieres adoptar?</label>
                                <input type="text" value="<?php echo htmlspecialchars($fila_criador['tipo_animal'] ?? ''); ?>" disabled style="padding: 10px; background-color: #f8f9fa; border: 1px solid #e2e8f0; border-radius: 6px;">
                            </div>
                        </div>

                        <div class="bloque" style="margin-bottom: 25px;">
                            <h3 style="margin-bottom: 15px; font-size: 16px; font-weight: 600;">Aceptación de requisitos</h3>
                            <p class="intro-requisitos" style="margin-bottom: 15px; font-size: 13.5px; color: #475569;">
                                En Find a Pet nos tomamos muy en serio el bienestar y el futuro de los animales. Por favor, confirma que estás de acuerdo con estos compromisos básicos para asegurar que tendrá una vida estupenda a tu lado:
                            </p>
                            <div class="contenedor-checkbox" style="margin-bottom: 10px; display: flex; align-items: flex-start; gap: 8px;">
                                <input type="checkbox" checked disabled style="margin-top: 4px;">
                                <label style="font-size: 13.5px;">Me comprometo a garantizar su bienestar, cubriendo sus necesidades de alimentación, salud y revisiones veterinarias.</label>
                            </div>
                            <div class="contenedor-checkbox" style="margin-bottom: 10px; display: flex; align-items: flex-start; gap: 8px;">
                                <input type="checkbox" checked disabled style="margin-top: 4px;">
                                <label style="font-size: 13.5px;">Dispondrá de un espacio adecuado y adaptado a su tamaño, así como del tiempo de atención, juego o paseos diarios que requiera su especie.</label>
                            </div>
                            <div class="contenedor-checkbox" style="margin-bottom: 10px; display: flex; align-items: flex-start; gap: 8px;">
                                <input type="checkbox" checked disabled style="margin-top: 4px;">
                                <label style="font-size: 13.5px;">El animal convivirá con el dueño y las personas que estén viviendo con él/ella como un miembro más de la familia y bajo ningún concepto lo utilizaré para la cría o comercio.</label>
                            </div>
                            <div class="contenedor-checkbox" style="margin-bottom: 10px; display: flex; align-items: flex-start; gap: 8px;">
                                <input type="checkbox" checked disabled style="margin-top: 4px;">
                                <label style="font-size: 13.5px;">Si por causas mayores no pudiera seguir haciéndome cargo de él, contactaré con Find a Pet o un refugio autorizado para buscarle un hogar responsable.</label>
                            </div>
                        </div>

                        <div class="bloque">
                            <div class="contenedor-checkbox" style="margin-bottom: 15px; display: flex; align-items: flex-start; gap: 8px;">
                                <input type="checkbox" checked disabled style="margin-top: 4px;">
                                <label style="font-size: 13.5px;">Confirmo que he leído y acepto todos los requisitos del formulario de compromiso.</label>
                            </div>
                            <div class="caja-donacion" style="padding: 20px; background: #faf6fc; border-radius: 8px; border: 1px solid #f3e8ff;">
                                <h3 style="margin-bottom: 10px; font-size: 15px; font-weight: 600;">Donación voluntaria</h3>
                                <p style="margin-bottom: 12px; font-size: 13px;">Apartado para colaboraciones voluntarias destinadas al mantenimiento de la página web y ayuda a refugios de animales.</p>
                                <span class="telefono-donacion" style="display: block; margin-bottom: 6px; font-size: 13px; font-weight: 500;">Mantenimiento de la página web: 600 189 240</span>
                                <span class="telefono-donacion" style="display: block; font-size: 13px; font-weight: 500;">Refugios de animales: 600 378 446</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>

    <div id="cartilla" class="contenido-pestana">
        <div class="section-header">
            <h2>Cartilla Veterinaria Digital</h2>
            <p>Consulta el historial clínico completo, estado de vacunación, datos médicos y control oficial de las mascotas asociadas a tu cuenta:</p>
        </div>
        
        <?php if (empty($misSolicitudes)): ?>
            <div class="status-box-empty" style="padding: 30px 10px;">
                <span class="icon" style="font-size: 48px; display: block; margin-bottom: 15px;">🐾🩺</span>
                <h4>No tienes ningún animal asignado todavía.</h4>
                <p>En esta sección podrás consultar el documento clínico de tu mascota de forma clara en cuanto inicies un proceso de adopción en el tablón.</p>
            </div>
        <?php else: ?>
            <div class="filtro-busqueda-contenedor">
                <input type="text" id="filtroNombreMascota" onkeyup="buscarPorNombreMascota()" placeholder="🔍 Buscar cartilla por el nombre de la mascota..." class="input-filtro-busqueda">
            </div>

            <div id="contenedorCartillasLista">
                <?php foreach ($misSolicitudes as $solicitud): ?>
                    <div class="tarjeta-cartilla-item cartilla-digital-contenedor" data-nombre="<?php echo strtolower($solicitud['nombre']); ?>">
                        
                        <div class="cartilla-cabecera">
                            <h3>
                                <span>🩺</span> Cartilla veterinaria del animal
                            </h3>
                            <?php if ($solicitud['verificado'] == 0): ?>
                                <span class="cartilla-badge-estado" style="background: #fff3cd; color: #000000; border: 1px solid #ffeeba;">
                                    ⏳ Pendiente de Validación
                                </span>
                            <?php else: ?>
                                <span class="cartilla-badge-estado" style="background: #d4edda; color: #000000; border: 1px solid #c3e6cb;">
                                    ✅ Cartilla Verificada / Adoptado
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="cartilla-cuerpo-grid">
                            
                            <div class="cartilla-lateral-foto">
                                <div class="cartilla-marco-foto">
                                    <?php if (!empty($solicitud['foto']) && file_exists("./imagenes/animales/" . $solicitud['foto'])): ?>
                                        <img src="./imagenes/animales/<?php echo $solicitud['foto']; ?>" alt="Foto de <?php echo $solicitud['nombre']; ?>">
                                    <?php else: ?>
                                        <img src="../imagenes/findapet.jpeg" alt="Imagen por defecto" style="opacity: 0.6;">
                                    <?php endif; ?>
                                </div>
                                <div class="cartilla-nombre-mascota"><?php echo $solicitud['nombre']; ?></div>
                            </div>

                            <div class="cartilla-contenido-principal">
                                
                                <h4 class="cartilla-seccion-titulo">📋 Datos Básicos del Animal</h4>
                                <div class="cartilla-datos-basicos-grid">
                                    <div class="cartilla-dato-bloque">
                                        <span>Especie / Categoría</span>
                                        <strong><?php echo !empty($solicitud['especie']) ? $solicitud['especie'] : '-'; ?></strong>
                                    </div>
                                    <div class="cartilla-dato-bloque">
                                        <span>Raza</span>
                                        <strong><?php echo !empty($solicitud['raza']) ? $solicitud['raza'] : 'Sin especificar'; ?></strong>
                                    </div>
                                    <div class="cartilla-dato-bloque">
                                        <span>Edad Estimada</span>
                                        <strong><?php echo !empty($solicitud['edad']) ? $solicitud['edad'] : 'No detallada'; ?></strong>
                                    </div>
                                    <div class="cartilla-dato-bloque">
                                        <span>Sexo</span>
                                        <strong><?php echo !empty($solicitud['sexo']) ? $solicitud['sexo'] : '-'; ?></strong>
                                    </div>
                                    <div class="cartilla-dato-bloque">
                                        <span>Tamaño</span>
                                        <strong><?php echo !empty($solicitud['tamano']) ? $solicitud['tamano'] : 'No detallado'; ?></strong>
                                    </div>
                                </div>

                                <h4 class="cartilla-seccion-titulo">🛡️ Identificación Sanitaria Obligatoria</h4>
                                <div class="cartilla-clinica-grid">
                                    <div class="cartilla-clinica-bloque">
                                        <span class="icono-salud">🆔</span>
                                        <div>
                                            <span>Código Microchip</span>
                                            <strong><?php echo !empty($solicitud['num_chip']) ? $solicitud['num_chip'] : 'No Registrado'; ?></strong>
                                        </div>
                                    </div>
                                    <div class="cartilla-clinica-bloque">
                                        <span class="icono-salud">💉</span>
                                        <div>
                                            <span>Vacuna de Rabia</span>
                                            <strong style="color: <?php echo ($solicitud['vacuna_rabia'] == 1) ? '#000000' : '#000000'; ?>;">
                                                <?php echo ($solicitud['vacuna_rabia'] == 1) ? 'Inmunizado (Sí)' : 'No Administrada'; ?>
                                            </strong>
                                        </div>
                                    </div>
                                    <div class="cartilla-clinica-bloque">
                                        <span class="icono-salud">🐛</span>
                                        <div>
                                            <span>Desparasitación</span>
                                            <strong style="color: <?php echo ($solicitud['desparasitado'] == 1) ? '#000000' : '#000000'; ?>;">
                                                <?php echo ($solicitud['desparasitado'] == 1) ? 'Al día (Sí)' : 'Pendiente'; ?>
                                            </strong>
                                        </div>
                                    </div>
                                </div>

                                <h4 class="cartilla-seccion-titulo">🦠 Último Lote de Vacunación Registrado</h4>
                                <div class="cartilla-vacuna-historial">
                                    <p><b>Fármaco / Vacuna administrada:</b> <?php echo !empty($solicitud['vacuna_nombre']) ? $solicitud['vacuna_nombre'] : 'Ninguna registrada'; ?></p>
                                    <p><b>Fecha de aplicación:</b> <?php echo !empty($solicitud['vacuna_fecha']) ? $solicitud['vacuna_fecha'] : 'Sin fecha'; ?></p>
                                    <p><b>Código de lote de fabricación:</b> <?php echo !empty($solicitud['vacuna_lote']) ? $solicitud['vacuna_lote'] : '-'; ?></p>
                                </div>

                                <h4 class="cartilla-seccion-titulo">📝 Notas Veterinarias y Observaciones Clínicas</h4>
                                <div class="cartilla-notas-medicas">
                                    <?php 
                                        $texto_limpio = str_replace("SOLICITUD_DE:" . $nombreUsuario . " | ", "", $solicitud['observaciones_medicas']);
                                        echo !empty($texto_limpio) ? nl2br(htmlspecialchars($texto_limpio)) : 'No hay observaciones clínicas adicionales registradas para este animal.';
                                    ?>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="perfil" class="contenido-pestana">
        <div class="section-header">
            <h2>Mi Perfil</h2>
            <p>Aquí puedes consultar y modificar los datos de tu cuenta cuando lo necesites.</p>
        </div>
        
        <div style="background: white; border: 1px solid #edf2f7; border-radius: 14px; padding: 35px; text-align: left; box-shadow: 0 4px 12px rgba(0,0,0,0.01);">
            
            <div style="display: flex; align-items: center; gap: 25px; margin-bottom: 35px; border-bottom: 1px solid #edf2f7; padding-bottom: 25px; flex-wrap: wrap;">
                <div>
                    <?php if (!empty($foto_actual)): ?>
                        <img src="<?php echo $foto_actual; ?>" alt="Foto de perfil" style="width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid #9b59b6; display: block;">
                    <?php else: ?>
                        <div style="width: 90px; height: 90px; background: #f5edfa; border: 2px dashed #9b59b6; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px;">👤</div>
                    <?php endif; ?>
                </div>
                <div>
                    <h3 style="margin: 0 0 15px 0; font-size: 20px; font-weight: 600;"><?php echo $nombreUsuario; ?></h3>
                    
                    <form action="" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 10px;">
                        <input type="file" name="foto" style="font-size: 13px; color: #7f8c8d;" required>
                        <button type="submit" name="subir_foto" style="background-color: #9b59b6; color: white; border: none; padding: 8px 16px; font-size: 12px; border-radius: 4px; cursor: pointer; max-width: 150px;">Actualizar foto</button>
                    </form>
                </div>
            </div>
            
            <form action="" method="POST" style="margin-bottom: 30px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px;">
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 12.5px; font-weight: 600; text-transform: uppercase;">Nombre de Cuenta (Email)</label>
                        <input type="text" value="<?php echo $nombreUsuario; ?>" disabled style="padding: 12px; background-color: #f8f9fa; border: 1px solid #e2e8f0; border-radius: 6px; color: #7f8c8d;">
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 12.5px; font-weight: 600; text-transform: uppercase;">Nombre</label>
                        <input type="text" name="perfil_nombre" value="<?php echo htmlspecialchars($fila_adopcion['nombre'] ?? ''); ?>" style="padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px;" placeholder="Tu nombre">
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 12.5px; font-weight: 600; text-transform: uppercase;">Apellidos</label>
                        <input type="text" name="perfil_apellidos" value="<?php echo htmlspecialchars($fila_adopcion['apellidos'] ?? ''); ?>" style="padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px;" placeholder="Tu apellido">
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 12.5px; font-weight: 600; text-transform: uppercase;">Teléfono</label>
                        <input type="tel" name="perfil_telefono" value="<?php echo htmlspecialchars($fila_adopcion['telefono'] ?? ''); ?>" style="padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px;" placeholder="Ej: 600123456">
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 25px;">
                    <label style="font-size: 12.5px; font-weight: 600; text-transform: uppercase;">Dirección completa</label>
                    <input type="text" name="perfil_direccion" value="<?php echo htmlspecialchars($fila_adopcion['direccion'] ?? ''); ?>" style="padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; width: 100%;" placeholder="Calle, Número, Planta, Ciudad">
                </div>

                <button type="submit" name="actualizar_perfil_datos" class="btn-action-primary" style="background-color: #27ae60; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600;">💾 Guardar Cambios</button>
            </form>

            <hr style="border: 0; border-top: 1px solid #edf2f7; margin: 30px 0;">

            <div style="text-align: center; margin-top: 15px;">
                <a href="logout_usuarios.php" class="btn-action-primary" style="padding: 12px 24px; background-color: #e74c3c; color: white !important; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block;">🔒 Cerrar sesión</a>
            </div>
        </div>
    </div>

</div>

<script>
function buscarPorNombreMascota() {
    var input = document.getElementById('filtroNombreMascota');
    var filtro = input.value.toLowerCase();
    var contenedor = document.getElementById('contenedorCartillasLista');
    if (!contenedor) return;
    var tarjetas = contenedor.getElementsByClassName('tarjeta-cartilla-item');

    for (var i = 0; i < tarjetas.length; i++) {
        var nombreMascota = tarjetas[i].getAttribute('data-nombre');
        if (nombreMascota && nombreMascota.includes(filtro)) {
            tarjetas[i].style.display = "";
        } else {
            tarjetas[i].style.display = "none";
        }
    }
}

function cambiarPestana(evt, nombrePestana) {
    var i, contenidoPestana, botonesMenu;
    
    contenidoPestana = document.getElementsByClassName("contenido-pestana");
    for (i = 0; i < contenidoPestana.length; i++) {
        contenidoPestana[i].classList.remove("activo");
    }
    
    botonesMenu = document.querySelectorAll(".menu-enlaces button");
    for (i = 0; i < botonesMenu.length; i++) {
        botonesMenu[i].classList.remove("activo");
    }
    
    var pestanaDestino = document.getElementById(nombrePestana);
    if(pestanaDestino) {
        pestanaDestino.classList.add("activo");
    }
    
    if(evt) {
        evt.currentTarget.classList.add("activo");
    } else if (nombrePestana === 'pestana-subida' || nombrePestana === 'tablon') {
        var btnTablon = document.getElementById("btn-tablon");
        if(btnTablon) btnTablon.classList.add("activo");
    }
}
</script>

</body>
</html>