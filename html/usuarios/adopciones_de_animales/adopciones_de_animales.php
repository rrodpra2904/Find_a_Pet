<?php 
// Incluyo el archivo de seguridad para comprobar que el usuario ha pasado por el formulario previo.
// Si no tiene la sesión activa, este archivo lo echará automáticamente.
include("../administrador/seguridad_formulario_adopciones.php"); 

// Iniciamos la sesión si no está activa para poder verificar si el usuario está registrado
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<?php
// 1. Conexión a la base de datos para poder consultar y actualizar los animales.
include 'conexion.php'; 

// 2. Lógica para procesar la adopción de animales cuando el usuario pulsa el botón.
if (isset($_GET['id_adoptar'])) {
    // Comprobamos la sesión real que usas en tu proyecto
    if (isset($_SESSION['usuarioAutenticado'])) {
        $id = intval($_GET['id_adoptar']);
        
        // Usamos el nombre del usuario autenticado para guardarlo o vincularlo
        $usuario_act = mysqli_real_escape_string($conexion, $_SESSION['usuarioAutenticado']);

        // Recuperamos lo que ya hubiera en observaciones médicas para no borrar vacunas previas si las hubiera
        $res_actual = mysqli_query($conexion, "SELECT observaciones_medicas FROM tablon_de_adopciones WHERE id = $id");
        $fila_actual = mysqli_fetch_assoc($res_actual);
        $notas_previas = isset($fila_actual['observaciones_medicas']) ? $fila_actual['observaciones_medicas'] : '';

        // Creamos el mensaje de adopción respetando el formato exacto que busca tu panel de cliente
        $nuevo_mensaje = "SOLICITUD_DE:" . $usuario_act;
        
        // Si ya había notas médicas antes, las juntamos con un separador claro
        if (!empty($notas_previas) && strpos($notas_previas, 'SOLICITUD_DE') === false) {
            $mensaje_final = mysqli_real_escape_string($conexion, $nuevo_mensaje . " | " . $notas_previas);
        } else {
            $mensaje_final = mysqli_real_escape_string($conexion, $nuevo_mensaje);
        }
        
        // GUARDADO EN LA CARTILLA VETERINARIA: Actualizamos las observaciones médicas en el tablón
        $sql_update = "UPDATE tablon_de_adopciones SET observaciones_medicas = '$mensaje_final' WHERE id = $id";
        
        if (mysqli_query($conexion, $sql_update)) {
            echo "<script>
                    alert('¡Felicidades! Has solicitado la adopción de este animal 🐾. Se ha registrado tu solicitud en su cartilla médica.');
                    window.location.href='../adopciones_de_animales/adopciones_de_animales.php';
                  </script>";
            exit(); 
        }
    } else {
        echo "<script>alert('Debes iniciar sesión para solicitar una adopción.');</script>";
    }
}

// ====================================================================
// CONFIGURACIÓN DE FILTROS (PRE-CARGA FORMULARIO ANTERIOR Y BUSCADOR)
// ====================================================================
$especie_inicial = isset($_SESSION['preferencia_especie']) ? $_SESSION['preferencia_especie'] : '';
$filtro_especie = isset($_POST['f_especie']) ? trim($_POST['f_especie']) : $especie_inicial;
$filtro_sexo = isset($_POST['f_sexo']) ? trim($_POST['f_sexo']) : ''; 
$filtro_texto = isset($_POST['f_texto']) ? trim($_POST['f_texto']) : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adopciones de animales</title>
    <link rel="stylesheet" href="styles_adopciones.css">
    <link rel="stylesheet" href="../styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Fuerza la tipografía Poppins en toda la página web */
        * {
            font-family: 'Poppins', sans-serif !important;
        }

        body {
            background-color: #fdfdfd;
            color: #2c3e50;
            margin: 0;
            padding: 0;
        }

        /* AJUSTADO: NUEVOS ESTILOS CON AIRE PARA LA INTRODUCCIÓN */
        .seccion-introduccion {
            max-width: 1200px;
            margin: 40px auto; 
            padding: 35px; 
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(155, 89, 182, 0.05);
            border: 1px solid #f3ebf7;
            text-align: center;
        }
        .intro-titulo {
            color: #2c3e50;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px; 
        }
        .intro-texto {
            font-size: 16px;
            color: #5a6c7d;
            line-height: 1.7;
            max-width: 900px;
            margin: 0 auto 35px auto; 
        }
        .banner-cartillas-usuario {
            background: linear-gradient(135deg, #fbf7fe 0%, #f5e6fe 100%);
            border: 1px solid #e2c2f7;
            border-radius: 10px;
            padding: 25px; 
            margin-top: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
            text-align: left;
        }
        .banner-info-texto h4 {
            color: #3b3b3b;
            font-size: 17px;
            margin: 0 0 10px 0; 
            font-weight: 700;
        }
        
        /* CORREGIDO: Color de la descripción cambiado a un negro suave/gris oscuro */
        .banner-info-texto p {
            color: #2c3e50 !important; 
            font-size: 14.5px;
            line-height: 1.6;
            margin: 0;
        }
        
        .btn-ir-cartillas {
            background: #9b59b6;
            color: white !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 15px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(155, 89, 182, 0.2);
            display: inline-block;
        }
        .btn-ir-cartillas:hover {
            background: #8e44ad;
            transform: translateY(-2px);
        }

        /* Botón grande, fondo blanco permanente, texto en negrita y hover MORADO en la letra */
        .botones-acceso .boton-acceso {
            display: inline-block;
            padding: 18px 26px !important; 
            text-decoration: none;
            font-family: 'Poppins', sans-serif !important;
            font-weight: 700 !important; 
            font-size: 16px !important; 
            background-color: #ffffff !important; 
            color: #2c3e50 !important; 
            border-radius: 50px !important; 
            border: none !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: color 0.2s ease; 
        }

        /* Hover aplicado únicamente para cambiar la letra a MORADO manteniendo el fondo blanco */
        .botones-acceso .boton-acceso:hover {
            background-color: #ffffff !important; 
            color: #9b59b6 !important; 
            transform: none !important;
        }

        /* FILTROS Y CONTENEDORES */
        .bloque-filtrado {
            max-width: 1200px;
            margin: 25px auto;
            padding: 18px 25px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(155, 89, 182, 0.08);
            border: 1px solid #ebdff2;
        }
        .form-filtro {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        .form-filtro select, .form-filtro input[type="text"] {
            padding: 10px 14px;
            border: 1px solid #d9cbd1;
            border-radius: 6px;
            font-size: 15px;
            outline: none;
            flex: 1;
            min-width: 180px;
        }
        .btn-buscar {
            background: #9b59b6;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
            transition: background 0.2s ease;
        }
        .btn-buscar:hover {
            background: #8e44ad;
        }
        
        /* MINI CARTILLA INTEGRADA */
        .bloque-cartilla-integrada {
            background: #fdf8ff;
            border: 1px solid #e9d5ff;
            border-radius: 8px;
            padding: 12px;
            margin-top: 12px;
            font-size: 13px;
            text-align: left;
        }
        .cartilla-titulo {
            font-weight: bold;
            color: #8e44ad;
            margin-bottom: 6px;
            border-bottom: 1px dashed #d8b4fe;
            padding-bottom: 2px;
        }
        .linea-medica { margin: 3px 0; color: #334155; }
        .linea-medica b { color: #0f172a; }

        .botones-candado {
            padding: 12px;
            background: #fdfaf0;
            border: 1px dashed #e74c3c;
            text-align: center;
            border-radius: 6px;
            font-size: 13px;
            color: #555;
            width: 100%;
        }
        .botones-candado a {
            color: #9b59b6;
            text-decoration: none;
            font-weight: bold;
        }
        .sin-resultados {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px;
            background: #fcf8fe;
            border: 1px dashed #9b59b6;
            border-radius: 10px;
            margin: 20px 0;
        }
        .sin-resultados p {
            font-size: 18px;
            color: #555;
            font-weight: 500;
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
                <a href="../index_inicio.php" class="fw-semibold">Inicio</a>
                <a href="../adopciones_de_animales/adopciones_de_animales.php" class="navbar-link-active fw-semibold">Adoptar</a>
                
                <?php if(isset($_SESSION['usuarioAutenticado'])): ?>
                    <a href="../formulario_criadores_de_animales.php" class="fw-semibold">Criadores</a>
                <?php endif; ?>
                
                <a href="../sobre_nosotros.html" class="fw-semibold">Sobre nosotros</a>
                <a href="../contacto.php" class="fw-semibold">Contacto</a>
            </div>
            <div class="botones-acceso">
                <?php if(isset($_SESSION['usuarioAutenticado'])): ?>
                    <a href="logout_usuarios.php" class="boton-acceso">Cerrar sesión</a>
                <?php else: ?>
                    <a href="../login_usuarios.php" class="boton-acceso">Entrar</a>
                    <a href="../registro_usuarios.php" class="boton-registro">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="seccion-introduccion">
        <div class="intro-titulo">Encuentra a tu Compañero Ideal </div><br>
        <p class="intro-texto">
            Bienvenido al tablón oficial de adopciones de <b>Find A Pet</b>. Aquí se reúnen animales extraordinarios que buscan una segunda oportunidad. Adoptar no es solo llevar un animal a casa; es salvar una vida y aceptar un compromiso de amor y protección para siempre. Explora las fichas, conoce su temperamento y descubre su historial clínico antes de dar el gran paso.
        </p><br>

        <div class="banner-cartillas-usuario">
            <div class="banner-info-texto">
                <h4>📋 ¿Sabías cómo funciona tu Cartilla Virtual?</h4><br>
                <p>Al pulsar el botón "Solicitar Adopción" de cualquier mascota, el sistema registrará tu solicitud de manera inmediata y guardará de forma segura su Cartilla Veterinaria Digital en tu espacio personal.</p><br>
            </div>
            <div>
                <?php if(isset($_SESSION['usuarioAutenticado'])): ?>
                    <a href="mis_cartillas.php" class="btn-ir-cartillas">📂 Ver mis Cartillas Guardadas</a>
                <?php else: ?>
                    <a href="../login_usuarios.php" class="btn-ir-cartillas">🔒 Inicia sesión para guardar cartillas</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="bloque-filtrado">
        <form method="POST" action="adopciones_de_animales.php" class="form-filtro">
            <select name="f_especie">
                <option value="">-- Ver todas las especies --</option>
                <option value="perro" <?php if(strtolower($filtro_especie) === 'perro') echo 'selected'; ?>>Perros</option>
                <option value="gato" <?php if(strtolower($filtro_especie) === 'gato') echo 'selected'; ?>>Gatos</option>
            </select>
            
            <select name="f_sexo">
                <option value="">-- Ver ambos sexos --</option>
                <option value="Macho" <?php if($filtro_sexo === 'Macho') echo 'selected'; ?>>Machos </option>
                <option value="Hembra" <?php if($filtro_sexo === 'Hembra') echo 'selected'; ?>>Hembras </option>
            </select>
            
            <input type="text" name="f_texto" placeholder="Buscar por nombre o raza (ej: Border Collie)..." value="<?php echo htmlspecialchars($filtro_texto); ?>">
            
            <button type="submit" class="btn-buscar">🔍 Buscar Mascotas</button>
        </form>
    </div><br>

   <br> 
   <div class="contenedor">
        <?php
        $sql = "SELECT * FROM tablon_de_adopciones WHERE (observaciones_medicas NOT LIKE '%SOLICITUD_DE%' OR observaciones_medicas IS NULL)";
        
        if (!empty($filtro_especie)) {
            $esp_safe = strtolower(mysqli_real_escape_string($conexion, $filtro_especie));
            $sql .= " AND LOWER(especie) = '$esp_safe'";
        }

        if (!empty($filtro_sexo)) {
            $sex_safe = mysqli_real_escape_string($conexion, $filtro_sexo);
            $sql .= " AND sexo = '$sex_safe'";
        }
        
        if (!empty($filtro_texto)) {
            $texto_safe = strtolower(mysqli_real_escape_string($conexion, $filtro_texto));
            $sql .= " AND (LOWER(nombre) LIKE '%$texto_safe%' OR LOWER(raza) LIKE '%$texto_safe%')";
        }

        $sql .= " ORDER BY id DESC";
        $res = mysqli_query($conexion, $sql);
        
        if (mysqli_num_rows($res) == 0) {
            echo "<div class='sin-resultados'>";
            echo "    <p>❌ Lo sentimos, actualmente no hay ningún animal disponible que coincida con tus criterios de búsqueda. 🐾</p>";
            echo "</div>";
        } else {
            while ($f = mysqli_fetch_assoc($res)) {
                
                $nombre_imagen = "";
                if (isset($f['imagen'])) {
                    $nombre_imagen = $f['imagen'];
                } elseif (isset($f['foto'])) {
                    $nombre_imagen = $f['foto'];
                }
                
                $ruta_foto = "../../imagenes/adopciones_de_animales/" . $nombre_imagen;
                
                if (empty($nombre_imagen) || !file_exists($ruta_foto)) {
                    $ruta_foto = "../imagenes/animales/" . $nombre_imagen;
                }
                
                if (empty($nombre_imagen)) {
                    $ruta_foto = "../imagenes/animales/defecto.png";
                }
            ?>
                <div class="caja">
                    <img src="<?php echo $ruta_foto; ?>"><br>
                    
                    <div class="info">
                        <h3><?php echo htmlspecialchars($f['nombre']); ?></h3><br>
                        <p><b>Raza:</b> <?php echo htmlspecialchars($f['raza']); ?></p>
                        <p><b>Edad:</b> <?php echo isset($f['edad']) ? htmlspecialchars($f['edad']) : '-'; ?> años</p>
                        <p><b>Sexo:</b> <?php echo !empty($f['sexo']) ? htmlspecialchars($f['sexo']) : '-'; ?></p><br>
                        
                        <?php if (isset($_SESSION['usuarioAutenticado'])): ?>
                            <div class="bloque-cartilla-integrada">
                                <div class="cartilla-titulo">🩺 Cartilla Médica Veterinaria</div>
                                <div class="linea-medica"><b>Nº Chip:</b> <?php echo !empty($f['num_chip']) ? htmlspecialchars($f['num_chip']) : 'Sin chip / No asignado'; ?></div>
                                <div class="linea-medica"><b>Vacuna Rabia:</b> <?php echo (isset($f['vacuna_rabia']) && ($f['vacuna_rabia'] == '1' || strtolower($f['vacuna_rabia']) == 'si' || strtolower($f['vacuna_rabia']) == 'sí')) ? 'Sí' : 'No'; ?></div>
                                <div class="linea-medica"><b>Desparasitado:</b> <?php echo (isset($f['desparasitado']) && ($f['desparasitado'] == '1' || strtolower($f['desparasitado']) == 'si' || strtolower($f['desparasitado']) == 'sí')) ? 'Sí' : 'No'; ?></div>
                                <div class="linea-medica"><b>Vacuna Administrada:</b> <?php echo !empty($f['vacuna_nombre']) ? htmlspecialchars($f['vacuna_nombre']) : 'Ninguna'; ?></div>
                                <div class="linea-medica"><b>Fecha / Lote:</b> <?php echo (!empty($f['vacuna_fecha']) && $f['vacuna_fecha'] != '0000-00-00') ? htmlspecialchars($f['vacuna_fecha']) : '-'; ?> / <?php echo !empty($f['vacuna_lote']) ? htmlspecialchars($f['vacuna_lote']) : '-'; ?></div>
                                <div class="linea-medica" style="margin-top: 5px;"><b>Notas Médicas / Descripción:</b> <br><i style="color: #64748b;"><?php echo !empty($f['observaciones_medicas']) ? nl2br(htmlspecialchars($f['observaciones_medicas'])) : 'Sin notas clínicas registradas.'; ?></i></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="botones" style="display: flex; flex-direction: column; gap: 6px; padding: 10px 20px 20px 20px;">
                        <?php if (isset($_SESSION['usuarioAutenticado'])): ?>
                            <a href="?id_adoptar=<?php echo $f['id']; ?>" 
                               onclick="return confirm('¿Seguro que quieres solicitar la adopción de <?php echo $f['nombre']; ?>?')" 
                               class="btn">Solicitar adopción</a>
                        <?php else: ?>
                            <div class="botones-candado">
                                <span>🔒 Para solicitar adopción o ver la cartilla médica debes <a href="../login_usuarios.php">Entrar</a> o <a href="../registro_usuarios.php">Registrarte</a>.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php 
            } // Fin del while
        } // Fin del else 
        ?>
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