<?php 
// Incluyo la seguridad para asegurarnos de que el usuario está logueado
include("../administrador/seguridad_formulario_adopciones.php"); 

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Conexión a la base de datos
include 'conexion.php'; 

// Compruebo que el usuario este logueado
$nombreUsuario = isset($_SESSION['usuarioAutenticado']) ? $_SESSION['usuarioAutenticado'] : '';

// ====================================================================
// LÓGICA PARA ELIMINAR / CANCELAR LA SOLICITUD DE ADOPCIÓN
// ====================================================================
if (isset($_GET['eliminar_cartilla']) && !empty($nombreUsuario)) {
    $id_eliminar = intval($_GET['eliminar_cartilla']);
    
    // Recupero las observaciones actuales para limpiar la marca del usuario
    $res_actual = mysqli_query($conexion, "SELECT observaciones_medicas FROM tablon_de_adopciones WHERE id = $id_eliminar");
    if ($fila_actual = mysqli_fetch_assoc($res_actual)) {
        $notas_actuales = $fila_actual['observaciones_medicas'];
        $notas_restauradas = "";

        // Si contenía notas previas separadas por la barra '|', las rescato para no borrarlas
        if (strpos($notas_actuales, '|') !== false) {
            $partes = explode('|', $notas_actuales);
            // El texto médico real suele estar en la segunda parte
            $notas_restauradas = isset($partes[1]) ? trim($partes[1]) : "";
        }
        
        $notas_safe = mysqli_real_escape_string($conexion, $notas_restauradas);
        
        // Si no quedan notas médicas reales, guardo un NULL o vacío para limpiar el registro por completo
        if (empty($notas_safe)) {
            $sql_clean = "UPDATE tablon_de_adopciones SET observaciones_medicas = NULL WHERE id = $id_eliminar";
        } else {
            $sql_clean = "UPDATE tablon_de_adopciones SET observaciones_medicas = '$notas_safe' WHERE id = $id_eliminar";
        }

        if (mysqli_query($conexion, $sql_clean)) {
            echo "<script>
                    alert('Se ha cancelado la solicitud y la cartilla se ha retirado de tu perfil.');
                    window.location.href='mis_cartillas.php';
                  </script>";
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Cartillas Veterinarias Guardadas</title>
    <link rel="stylesheet" href="styles_adopciones.css">
    <link rel="stylesheet" href="../styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Fuerza la tipografía Poppins en toda la página web*/
        * {
            font-family: 'Poppins', sans-serif !important;
        }

        body {
            background-color: #fdfdfd; 
            color: #2c3e50;
            margin: 0;
            padding: 0;
        }

        .seccion-cartillas {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* Encabezado Principal */
        .encabezado-pagina {
            text-align: center;
            margin-bottom: 40px;
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            border: 1px solid #ebdff2;
            box-shadow: 0 4px 20px rgba(155, 89, 182, 0.05);
        }

        .titulo-principal {
            color: #000000;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .subtitulo {
            color: #7f8c8d;
            font-size: 16px;
            margin: 0;
            line-height: 1.6;
        }

        /* Grid */
        .grid-cartillas {
            display: grid;
            grid-template-columns: 1fr;
            gap: 35px; 
            margin-bottom: 60px;
        }

        @media (min-width: 768px) {
            .grid-cartillas {
                grid-template-columns: repeat(2, 1fr); 
            }
        }

        /* Tarjeta */
        .caja-cartilla {
            background: #ffffff;
            border: 1px solid #ebdff2;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(155, 89, 182, 0.06);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .caja-cartilla:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(155, 89, 182, 0.12);
        }

        .caja-cartilla img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-bottom: 1px solid #ebdff2;
        }

        .info-cartilla {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* NOMBRE DEL ANIMAL EN COLOR NEGRO */
        .info-cartilla h3 {
            font-size: 22px;
            color: #000000; 
            margin: 0 0 15px 0;
            font-weight: 700;
            border-bottom: 2px dashed #e9d5ff;
            padding-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Badge Proceso Limpio */
        .badge-tramite {
            font-size: 13px;
            background: #d1fae5; 
            color: #000000 !important; 
            border: 1px solid #a7f3d0;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .datos-lista {
            margin-bottom: 20px;
        }

        /* Letras principales */
        .datos-lista p {
            margin: 12px 0;
            font-size: 15px;
            color: #334155; 
            line-height: 1.5;
        }

        .datos-lista b {
            color: #0f172a; 
            font-weight: 600;
        }

        /* Bloque de cartilla veterinaria */
        .bloque-medico-interno {
            background: #fdf8ff;
            border: 1px solid #e9d5ff;
            border-radius: 8px;
            padding: 18px;
            margin-top: 15px;
        }

        /* TÍTULO DE DATOS SANITARIOS EN COLOR NEGRO */
        .titulo-bloque-medico {
            font-weight: 700;
            color: #000000; 
            font-size: 14px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .bloque-medico-interno p {
            font-size: 14px;
            margin: 10px 0;
            color: #475569;
        }

        .observaciones-box {
            background: #ffffff;
            border-left: 4px solid #9b59b6;
            padding: 12px;
            margin-top: 12px;
            border-radius: 4px;
            font-style: italic;
            font-size: 13.5px;
            color: #475569;
            line-height: 1.6;
        }

        /* Botón de volver */
        .btn-volver {
            display: inline-block;
            background: #f3e8ff; 
            color: #000000 !important; 
            border: 1px solid #e9d5ff;
            text-decoration: none;
            padding: 12px 26px;
            border-radius: 8px;
            font-weight: 600; 
            font-size: 15px;
            margin-bottom: 30px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(155, 89, 182, 0.05);
        }

        .btn-volver:hover {
            background: #e9d5ff; 
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(155, 89, 182, 0.1);
        }

        /* Botón de Cancelar*/
        .btn-cancelar-solicitud {
            display: block;
            text-align: center;
            background: #fef2f2;
            color: #000000 !important;
            border: 1px solid #fca5a5;
            text-decoration: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14.5px;
            margin-top: 15px;
            transition: all 0.2s ease;
        }

        .btn-cancelar-solicitud:hover {
            background: #fee2e2;
            color: #000000 !important;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.08);
        }

        .sin-datos {
            text-align: center;
            padding: 50px;
            background: #ffffff;
            border: 2px dashed #ebdff2;
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <div class="top-bar">Correo electrónico: findaapet@gmail.com<br>Teléfono: 654 987 321</div>
    
    <nav class="navbar-custom">
        <div class="container nav-container">
            <a class="logo-link" href="index.php">
                <img src="../imagenes/findapet.jpeg" alt="Logo">
            </a>
            <div class="menu-links">
                <a href="../index.php" class="fw-semibold">Inicio</a>
                <a href="../adopciones_de_animales/adopciones_de_animales.php" class="navbar-link-active fw-semibold">Adoptar</a>
                <a href="../formulario_criadores_de_animales.php" class="fw-semibold">Criadores</a>
                <a href="../sobre_nosotros.html" class="fw-semibold">Sobre nosotros</a>
                <a href="../contacto.php" class="fw-semibold">Contacto</a>
            </div>
            <div class="botones-acceso">
                <?php if(isset($_SESSION['usuarioAutenticado'])): ?>
                    <a href="../logout.php" class="boton-acceso">Salir</a>
                <?php else: ?>
                    <a href="../login_usuarios.php" class="boton-acceso">Entrar</a>
                    <a href="../registro_usuarios.php" class="boton-registro">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="seccion-cartillas">
        <a href="adopciones_de_animales.php" class="btn-volver">⬅ Volver a la página web de adopciones</a>
        
        <div class="encabezado-pagina">
            <div class="titulo-principal">📋 Mi Historial de Cartillas Virtuales</div>
            <p class="subtitulo">Aquí puedes revisar las cartillas sanitarias de los animales que tienes guardados o solicitados en adopción.</p>
        </div>

        <div class="grid-cartillas">
            <?php
            $usuario_safe = mysqli_real_escape_string($conexion, $nombreUsuario);
            $sql = "SELECT * FROM tablon_de_adopciones WHERE observaciones_medicas LIKE '%SOLICITUD_DE:%$usuario_safe%' OR observaciones_medicas LIKE '%SOLICITUD_DE: %$usuario_safe%' ORDER BY id DESC";
            $res = mysqli_query($conexion, $sql);

            if (mysqli_num_rows($res) == 0) {
                echo "<div class='sin-datos'>";
                echo "    <p style='font-size: 18px; color: #555; font-weight: 500;'>🐾 No tienes ninguna cartilla guardada en este momento.</p>";
                echo "</div>";
            } else {
                while ($f = mysqli_fetch_assoc($res)) {
                    
                    $nombre_imagen = "";
                    if (!empty($f['imagen'])) {
                        $nombre_imagen = $f['imagen'];
                    } elseif (!empty($f['foto'])) {
                        $nombre_imagen = $f['foto'];
                    }
                    
                    $ruta_foto = "../../imagenes/adopciones_de_animales/" . $nombre_imagen;
                    if (empty($nombre_imagen) || !file_exists($ruta_foto)) {
                        $ruta_foto = "../imagenes/animales/" . $nombre_imagen;
                    }
                    if (empty($nombre_imagen) || !file_exists($ruta_foto)) {
                        $ruta_foto = "../imagenes/animales/defecto.png";
                    }

                    $notes_limpias = $f['observaciones_medicas'];
                    if (strpos($notes_limpias, '|') !== false) {
                        $partes = explode('|', $notes_limpias);
                        $notes_limpias = isset($partes[1]) ? trim($partes[1]) : 'Sin notas clínicas registradas.';
                    } else {
                        if (strpos($notes_limpias, 'SOLICITUD_DE') !== false) {
                            $notes_limpias = 'Sin notas clínicas registradas.';
                        }
                    }

                    $edad_num = isset($f['edad']) ? (int)$f['edad'] : 0;
                    $texto_edad = ($edad_num === 1) ? "1 año" : $edad_num . " años";
            ?>
                    <div class="caja-cartilla">
                        <img src="<?php echo htmlspecialchars($ruta_foto, ENT_QUOTES, 'UTF-8'); ?>" alt="Foto de la mascota">
                        
                        <div class="info-cartilla">
                            <div class="datos-lista">
                                <h3>
                                    <span><?php echo htmlspecialchars($f['nombre'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="badge-tramite">Adoptado</span>
                                </h3>
                                
                                <p><b>Especie:</b> <?php echo htmlspecialchars($f['especie'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p><b>Raza:</b> <?php echo htmlspecialchars($f['raza'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p><b>Edad:</b> <?php echo htmlspecialchars($texto_edad, ENT_QUOTES, 'UTF-8'); ?></p>
                                <p><b>Sexo:</b> <?php echo !empty($f['sexo']) ? htmlspecialchars($f['sexo'], ENT_QUOTES, 'UTF-8') : '-'; ?></p>
                                
                                <div class="bloque-medico-interno">
                                    <div class="titulo-bloque-medico">📋 Datos Sanitarios Oficiales</div>
                                    
                                    <p><b>Número de Chip:</b> <code style="color: #8e44ad; font-weight: bold; font-family: 'Poppins', sans-serif;"><?php echo !empty($f['num_chip']) ? htmlspecialchars($f['num_chip'], ENT_QUOTES, 'UTF-8') : 'Asignando chip oficial...'; ?></code></p>
                                    <p><b>Vacuna de la Rabia:</b> <?php echo ($f['vacuna_rabia'] == '1' || strtolower($f['vacuna_rabia']) == 'si' || strtolower($f['vacuna_rabia']) == 'sí') ? '✅ Al día' : '✅ Verificado'; ?></p>
                                    <p><b>Desparasitación:</b> <?php echo ($f['desparasitado'] == '1' || strtolower($f['desparasitado']) == 'si' || strtolower($f['desparasitado']) == 'sí') ? '✅ Al día' : '✅ Verificado'; ?></p>
                                    <p><b>Otras vacunas:</b> <?php echo !empty($f['vacuna_nombre']) ? htmlspecialchars($f['vacuna_nombre'], ENT_QUOTES, 'UTF-8') : 'Ninguna registrada'; ?></p>
                                    <p><b>Fecha de Vacunación:</b> <?php echo (!empty($f['vacuna_fecha']) && $f['vacuna_fecha'] != '0000-00-00') ? htmlspecialchars($f['vacuna_fecha'], ENT_QUOTES, 'UTF-8') : '-'; ?></p>
                                    <p><b>Lote de Vacuna:</b> <?php echo !empty($f['vacuna_lote']) ? htmlspecialchars($f['vacuna_lote'], ENT_QUOTES, 'UTF-8') : '-'; ?></p>
                                    
                                    <div class="observaciones-box">
                                        <b>Observaciones del Veterinario:</b><br>
                                        <?php echo nl2br(htmlspecialchars($notes_limpias, ENT_QUOTES, 'UTF-8')); ?>
                                    </div>
                                </div>
                                
                                <a href="?eliminar_cartilla=<?php echo intval($f['id']); ?>" 
                                   class="btn-cancelar-solicitud" 
                                   onclick="return confirm('¿Estás seguro de que quieres cancelar la solicitud de adopción de <?php echo htmlspecialchars($f['nombre'], ENT_QUOTES, 'UTF-8'); ?>? El animal volverá al tablón público.');">
                                   Cancelar Solicitud de Adopción
                                </a>
                            </div>
                        </div>
                    </div>
            <?php 
                } 
            } 
            ?>
        </div>
    </div>

    <footer class="footer-custom">
        <div class="footer-container">
            <div class="footer-col">
                <img src="../imagenes/findapet.jpeg" alt="Logo" class="footer-logo-img">
                <p class="footer-text">Plataforma para la adopción responsable y contacto con criadores.</p>
            </div>
            <div class="footer-col footer-col-center">
                <h4 class="footer-title-sub">Navegación</h4>
                <ul class="footer-list">
                    <li><a href="../index.php">Inicio</a></li>
                    <li><a href="../formulario_adopciones_de_animales.php">Adoptar</a></li>
                    <li><a href="../formulario_criadores_de_animales.php">Criadores</a></li>
                    <li><a href="../contacto.php">Contacto</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4 class="footer-title-sub">Contacto</h4>
                <p class="footer-text">📧 info@findapet.com</p>
                <p class="footer-text">📍 Motril, Granada, España</p>
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d12756.241584772183!2d-3.528481!3d36.746024!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd7190013b8398e7%3A0x403d278401349a0!2sMotril%2C%20Granada!5e0!3m2!1ses!2ses!4v1715000000000!5m2!1ses!2ses" width="350" height="300"></iframe>
            </div>
        </div>
        <div class="footer-copyright-bar">
            <p class="footer-copy-text">TODOS LOS DERECHOS RESERVADOS © FIND A PET 2026 - PROYECTO FINAL DAW | RAQUEL </p>
        </div>
    </footer>
</body>
</html>