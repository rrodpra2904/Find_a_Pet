<?php 
// Incluyo el archivo de seguridad específico para el formulario de criadores.
// Esto asegura que solo las personas con acceso permitido puedan ver esta lista.

// Inicio la sesión si no está activa para poder verificar si el usuario está registrado
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<?php
/* 1. CONEXIÓN A LA BASE DE DATOS:
   Cargo el archivo de conexión para poder realizar consultas a las tablas. */
include 'conexion.php'; 

// --- GESTIÓN DE FILTROS ---
// Recojo lo que el usuario escribe en los filtros (si no hay nada, se queda vacío)
$filtro_nombre = isset($_GET['buscar_nombre']) ? trim($_GET['buscar_nombre']) : '';
$filtro_raza = isset($_GET['buscar_raza']) ? trim($_GET['buscar_raza']) : '';
$filtro_ubicacion = isset($_GET['buscar_ubicacion']) ? trim($_GET['buscar_ubicacion']) : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Directorio de Criadores - Find A Pet</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles_criadores.css">
    <link rel="stylesheet" href="../styles.css">
    
    <style>
        /* Fuerza que el botón de cerrar sesión use Poppins, sea grande y no se vea aplastado */
        .botones-acceso .boton-cerrar-sesion {
            font-family: 'Poppins', sans-serif !important;
            font-size: 17.5px !important; 
            font-weight: 700 !important; 
            display: inline-block !important;
            padding: 14px 32px !important; 
            text-decoration: none !important;
            background-color: #ffffff !important; 
            color: #2c3e50 !important; 
            border-radius: 50px !important;
            box-shadow: 0 3px 8px rgba(0,0,0,0.08) !important;
            transition: color 0.2s ease, transform 0.2s ease !important;
            line-height: 1 !important;
        }

        .botones-acceso .boton-cerrar-sesion:hover {
            background-color: #ffffff !important;
            color: #9b59b6 !important;
            transform: translateY(-1px) !important;
        }

        /* --- Estilos para la barra de filtros --- */
        .seccion-filtros {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin: 0 auto 30px auto;
            max-width: 1100px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .formulario-filtros {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
        }

        .grupo-filtro {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-width: 200px;
        }

        .grupo-filtro label {
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .grupo-filtro input {
            font-family: 'Poppins', sans-serif;
            padding: 10px 15px;
            border: 1px solid #dcdde1;
            border-radius: 8px;
            font-size: 15px;
            color: #2c3e50;
            outline: none;
            transition: border-color 0.2s;
        }

        .grupo-filtro input:focus {
            border-color: #9b59b6;
        }

        .botones-filtro {
            display: flex;
            gap: 10px;
            align-items: flex-end;
            margin-top: 23px;
        }

        .boton-buscar {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            background-color: #9b59b6;
            color: #ffffff;
            border: none;
            padding: 11px 25px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .boton-buscar:hover {
            background-color: #8e44ad;
        }

        .boton-limpiar {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            background-color: #7f8c8d;
            color: #ffffff;
            border: none;
            padding: 11px 25px;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.2s;
        }

        .boton-limpiar:hover {
            background-color: #95a5a6;
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
                <a href="../adopciones_de_animales/adopciones_de_animales.php" class="fw-semibold">Adoptar</a>
                <a href="criadores_de_animales.php" class="fw-semibold">Criadores</a>
                <a href="../sobre_nosotros.html" class="fw-semibold">Sobre nosotros</a>
                <a href="../contacto.php" class="fw-semibold">Contacto</a>
            </div>
            <div class="botones-acceso">
                <?php if(isset($_SESSION['usuarioAutenticado'])): ?>
                    <a href="../adopciones_de_animales/logout_usuarios.php" class="boton-cerrar-sesion">Cerrar sesión</a>
                <?php else: ?>
                    <a href="../login_usuarios.php" class="boton-cerrar-sesion">Entrar</a>
                    <a href="../registro_usuarios.php" class="boton-registro">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <h1> Criadores Verificados</h1><br>

        <p class="parrafo">
            Encuentra profesionales comprometidos con la excelencia y el bienestar animal. 
            Todos nuestros criadores cuentan con certificaciones oficiales.
        </p><br>

        <div class="seccion-filtros">
            <form action="" method="GET" class="formulario-filtros">
                <div class="grupo-filtro">
                    <label for="buscar_nombre">Nombre del criador</label>
                    <input type="text" id="buscar_nombre" name="buscar_nombre" placeholder="Ej: Criadero Real, Toby..." value="<?php echo htmlspecialchars($filtro_nombre); ?>">
                </div>

                <div class="grupo-filtro">
                    <label for="buscar_raza">Raza de animal</label>
                    <input type="text" id="buscar_raza" name="buscar_raza" placeholder="Ej: Border Collie, Persa..." value="<?php echo htmlspecialchars($filtro_raza); ?>">
                </div>

                <div class="grupo-filtro">
                    <label for="buscar_ubicacion">Ubicación / Localidad</label>
                    <input type="text" id="buscar_ubicacion" name="buscar_ubicacion" placeholder="Ej: Granada, Madrid..." value="<?php echo htmlspecialchars($filtro_ubicacion); ?>">
                </div>

                <div class="botones-filtro">
                    <button type="submit" class="boton-buscar">🔍 Buscar</button>
                    <?php if ($filtro_nombre !== '' || $filtro_raza !== '' || $filtro_ubicacion !== ''): ?>
                        <a href="criadores_de_animales.php" class="boton-limpiar">Limpiar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="contenedor-principal">
            <?php
            /* 2. CONSULTA DINÁMICA A LA BD CON FILTROS EN CADENA:
               Monto las condiciones SQL añadiendo el filtro de nombre. */
            $condiciones = [];
            
            if ($filtro_nombre !== '') {
                $condiciones[] = "nombre_del_criador LIKE '%" . mysqli_real_escape_string($conexion, $filtro_nombre) . "%'";
            }
            if ($filtro_raza !== '') {
                $condiciones[] = "raza_de_animal LIKE '%" . mysqli_real_escape_string($conexion, $filtro_raza) . "%'";
            }
            if ($filtro_ubicacion !== '') {
                $condiciones[] = "localidad LIKE '%" . mysqli_real_escape_string($conexion, $filtro_ubicacion) . "%'";
            }

            // Uno todas las búsquedas que el usuario use
            $sql = "SELECT * FROM criadores_de_animales";
            if (count($condiciones) > 0) {
                $sql .= " WHERE " . implode(' AND ', $condiciones);
            }
            $sql .= " ORDER BY id DESC";

            $resultado = mysqli_query($conexion, $sql);
            
            // Verifico si la base de datos tiene registros
            if (mysqli_num_rows($resultado) > 0) {
                // Inicio un bucle para recorrer cada criador y generar su tarjeta visual
                while ($fila = mysqli_fetch_assoc($resultado)) {
                ?>
                    <div class="tarjeta">
                        <img src="../../imagenes/criadores_de_animales/<?php echo $fila['logo_del_criador']; ?>" alt="Logo Criador">
                        
                        <div class="contenido">
                            <h3><?php echo htmlspecialchars($fila['nombre_del_criador']); ?></h3>
                            
                            <p><span class="negrita">Raza:</span> <?php echo htmlspecialchars($fila['raza_de_animal']); ?></p>
                            <p><span class="negrita">Ubicación:</span> <?php echo htmlspecialchars($fila['localidad']); ?></p>
                            <p><span class="negrita">Teléfono:</span> <?php echo htmlspecialchars($fila['telefono']); ?></p>
                            
                            <div class="descripcion-corta">
                                <?php echo nl2br(htmlspecialchars($fila['informacion_del_criador'])); ?>
                            </div>

                            <div class="badge-verificado">Verificado</div>
                        </div>
                    </div>
                <?php 
                } 
            } else {
                /* 4. MANEJO DE ESTADO VACÍO: */
                if ($filtro_nombre !== '' || $filtro_raza !== '' || $filtro_ubicacion !== '') {
                    echo "<div class='no-datos'>
                            <h2>No se encontraron resultados para tu búsqueda 🐾</h2>
                            <p>Intenta cambiar los términos o limpia los filtros para ver todos los criadores.</p>
                          </div>";
                } else {
                    echo "<div class='no-datos'>
                            <h2>Próximamente nuevos criadores verificados 🐾</h2>
                            <p>Estamos validando las certificaciones oficiales de nuevos profesionales.</p>
                          </div>";
                }
            }
            ?>
        </div>
    </main>

    <footer class="footer-custom">
        <div class="footer-container">
            <div class="footer-col">
                <img src="../imagenes/findapet.jpeg" alt="Logo" class="footer-logo-img">
                <p class="footer-text">Plataforma para la adopción responsable y contacto con criadores.</p>
            </div>
            <div class="footer-col footer-col-center">
                <h4 class="footer-title-sub">Navegación</h4>
                <ul class="footer-list">
                    <li><a href="../index_inicio.php">Inicio</a></li>
                    <li><a href="../adopciones_de_animales/adopciones_de_animales.php">Adoptar</a></li>
                    <li><a href="../criadores_de_animales.php">Criadores</a></li>
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