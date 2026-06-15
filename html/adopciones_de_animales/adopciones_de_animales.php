<?php 

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'conexion.php'; 


if (isset($_GET['id_adoptar'])) {
   
    if (isset($_SESSION['usuario_id']) || isset($_SESSION['email'])) {
        $id = intval($_GET['id_adoptar']);

  
        $sql_update = "UPDATE animales SET adoptado = 'Si', rol = 'Cliente' WHERE id = $id";
        
        if (mysqli_query($conexion, $sql_update)) {
            echo "<script>
                    alert('¡Felicidades! Has adoptado a este animal 🐾');
                    window.location.href='../adopciones_de_animales/adopciones_de_animales.php';
                  </script>";
            exit(); 
        }
    } else {
    
        echo "<script>alert('Debes iniciar sesión con tu cuenta de usuario para solicitar una adopción. Por favor, ve a la pestaña Entrar.');</script>";
    }
}

$especie_inicial = isset($_SESSION['preferencia_especie']) ? $_SESSION['preferencia_especie'] : '';
$filtro_especie = isset($_POST['f_especie']) ? trim($_POST['f_especie']) : $especie_inicial;
$filtro_texto = isset($_POST['f_texto']) ? trim($_POST['f_texto']) : '';
?>

<?php

include 'conexion.php'; 



if (isset($_GET['id_adoptar'])) {
    
    if (isset($_SESSION['usuario_id']) || isset($_SESSION['email'])) {
        $id = intval($_GET['id_adoptar']);


        $sql_update = "UPDATE animales SET adoptado = 'Si', rol = 'Cliente' WHERE id = $id";
        
        
        if (mysqli_query($conexion, $sql_update)) {
            echo "<script>
                    alert('¡Felicidades! Has adoptado a este animal 🐾');
                    window.location.href='../adopciones_de_animales/adopciones_de_animales.php';
                  </script>";
            exit(); 
        }
    } else {
        echo "<script>alert('Debes iniciar sesión para solicitar una adopción.');</script>";
    }
}




$especie_inicial = isset($_SESSION['preferencia_especie']) ? $_SESSION['preferencia_especie'] : '';


$filtro_especie = isset($_POST['f_especie']) ? trim($_POST['f_especie']) : $especie_inicial;
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    
    <style>
        .bloque-filtrado {
            max-width: 1200px;
            margin: 20px auto;
            padding: 15px 25px;
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
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            outline: none;
            flex: 1;
            min-width: 200px;
        }
        .btn-buscar {
            background: #9b59b6;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 15px;
            transition: background 0.2s ease;
        }
        .btn-buscar:hover {
            background: #8e44ad;
        }
        .btn-cartilla {
            display: inline-block;
            background-color: #34495e;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 14px;
            margin-top: 8px;
            text-align: center;
            transition: background 0.2s ease;
        }
        .btn-cartilla:hover {
            background-color: #2c3e50;
        }
        .botones-candado {
            padding: 12px;
            background: #fdfaf0;
            border: 1px dashed #e74c3c;
            text-align: center;
            border-radius: 6px;
            font-size: 13px;
            color: #555;
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
                <a href="../index.php" class="fw-semibold">Inicio</a>
                <a href="../adopciones_de_animales/adopciones_de_animales.php" class="fw-semibold">Adoptar</a>
                <a href="../formulario_criadores_de_animales.php" class="fw-semibold">Criadores</a>
                <a href="../sobre_nosotros.html" class="fw-semibold">Sobre nosotros</a>
                <a href="../contacto.php" class="fw-semibold">Contacto</a>
            </div>
            <div class="botones-acceso">
                <?php if(isset($_SESSION['usuario_id']) || isset($_SESSION['email'])): ?>
                    <a href="../usuarios/logout.php" class="boton-acceso">Salir</a>
                <?php else: ?>
                    <a href="../usuarios/login_usuarios.php" class="boton-acceso">Entrar</a>
                    <a href="../usuarios/registro_usuarios.php" class="boton-registro">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <h1>Adoptar un animal 🐾 </h1><br>

    <p class="parrafo">
        Bienvenido a nuestra página web de adopciones de animales. Aquí puedes ver a todos los animales que buscan un hogar. 
        Adoptar es un compromiso de por vida, <br><br>¡encuentra a tu compañero ideal y dale la oportunidad que se merece!
    </p><br>

    <div class="bloque-filtrado">
        <form method="POST" action="adopciones_de_animales.php" class="form-filtro">
            <select name="f_especie">
                <option value="">-- Ver todas las especies --</option>
                <option value="perro" <?php if(strtolower($filtro_especie) === 'perro') echo 'selected'; ?>>Perros</option>
                <option value="gato" <?php if(strtolower($filtro_especie) === 'gato') echo 'selected'; ?>>Gatos</option>
            </select>
            
            <input type="text" name="f_texto" placeholder="Buscar por nombre o raza (ej: Border Collie)..." value="<?php echo htmlspecialchars($filtro_texto); ?>">
            
            <button type="submit" class="btn-buscar">🔍 Buscar Mascotas</button>
        </form>
    </div>

   <br> 
   <div class="contenedor">
        <?php
      
        $sql = "SELECT a.*, f.especie AS especie_filtro 
                FROM animales a 
                LEFT JOIN filtro_animales f ON a.id = f.id_animal 
                WHERE a.adoptado = 'No'";
        
        
        if (!empty($filtro_especie)) {
            $esp_safe = strtolower(mysqli_real_escape_string($conexion, $filtro_especie));
            $sql .= " AND LOWER(f.especie) = '$esp_safe'";
        }
        
        
        if (!empty($filtro_texto)) {
            $texto_safe = strtolower(mysqli_real_escape_string($conexion, $filtro_texto));
            $sql .= " AND (LOWER(a.nombre) LIKE '%$texto_safe%' OR LOWER(a.raza) LIKE '%$texto_safe%' OR LOWER(a.descripcion) LIKE '%$texto_safe%')";
        }

      
        $sql .= " ORDER BY a.id DESC";
        $res = mysqli_query($conexion, $sql);
        
   
        if (mysqli_num_rows($res) == 0) {
            echo "<div class='sin-resultados'>";
            echo "    <p>❌ Lo sentimos, actualmente no hay ningún animal disponible que coincida con tus criterios de búsqueda. 🐾</p>";
            echo "</div>";
        } else {
        
            while ($f = mysqli_fetch_assoc($res)) {
                
          
                $nombre_imagen = $f['imagen'];
                $ruta_foto = "../imagenes/adopciones_de_animales/" . $nombre_imagen;
                
                if (!file_exists($ruta_foto) || empty($nombre_imagen)) {
                    $ruta_foto = "../usuarios/imagenes/animales/" . $nombre_imagen;
                }
            ?>
                <div class="caja">
                    <img src="<?php echo $ruta_foto; ?>"><br>
                    
                    <div class="info">
                        <h3><?php echo $f['nombre']; ?></h3><br>
                        <p><b>Raza:</b> <?php echo $f['raza']; ?></p>
                        <p><b>Edad:</b> <?php echo $f['edad']; ?> años</p><br>
                        <p><?php echo $f['descripcion']; ?></p>
                    </div>

                    <div class="botones" style="display: flex; flex-direction: column; gap: 6px; padding: 10px 0;">
                        <?php if (isset($_SESSION['usuario_id']) || isset($_SESSION['email'])): ?>
                            <a href="?id_adoptar=<?php echo $f['id']; ?>" 
                               onclick="return confirm('¿Seguro que quieres adoptar a <?php echo $f['nombre']; ?>?')" 
                               class="btn">Solicitar adopción</a>
                               
                            <a href="ver_cartilla.php?id=<?php echo $f['id']; ?>" class="btn-cartilla">📋 Ver Cartilla Médica</a>
                        <?php else: ?>
                            <div class="botones-candado">
                                <span>🔒 Para solicitar adopción o ver la cartilla debes <a href="../usuarios/login_usuarios.php">Entrar</a> o <a href="../usuarios/registro_usuarios.php">Registrarte</a>.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php 
            } 
        } 
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