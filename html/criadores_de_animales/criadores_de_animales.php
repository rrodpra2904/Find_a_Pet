<?php 
// Incluyo el archivo de seguridad específico para el formulario de criadores.
// Esto asegura que solo las personas con acceso permitido puedan ver esta lista.
include("../administrador/seguridad_formulario_criadores.php"); 
?>

<?php
/* 1. CONEXIÓN A LA BASE DE DATOS:
   Cargo el archivo de conexión para poder realizar consultas a las tablas. */
include 'conexion.php'; 
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
                <a href="../usuarios/login_usuarios.php" class="boton-acceso">Entrar</a>
                <a href="../usuarios/registro_usuarios.php" class="boton-registro">Registrarse</a>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <h1> Criadores Verificados</h1><br>

        <p class="parrafo">
            Encuentra profesionales comprometidos con la excelencia y el bienestar animal. 
            Todos nuestros criadores cuentan con certificaciones oficiales.
        </p><br>

        <div class="contenedor-principal">
            <?php
            /* 2. CONSULTA DINÁMICA A LA BD:
               Selecciono todos los registros de la tabla 'criadores_de_animales'.
               Los ordenos por ID de forma descendente para que los últimos registros añadidos aparezcan primero. */
            $sql = "SELECT * FROM criadores_de_animales ORDER BY id DESC";
            $resultado = mysqli_query($conexion, $sql);
            
            // Verificamos si la base de datos tiene registros
            if (mysqli_num_rows($resultado) > 0) {
                // Iniciamos un bucle para recorrer cada criador y generar su tarjeta visual
                while ($fila = mysqli_fetch_assoc($resultado)) {
                ?>
                    <div class="tarjeta">
                        <img src="../imagenes/criadores_de_animales/<?php echo $fila['logo_del_criador']; ?>" alt="Logo Criador">
                        
                        <div class="contenido">
                            <h3><?php echo $fila['nombre_del_criador']; ?></h3>
                            
                            <p><span class="negrita">Raza:</span> <?php echo $fila['raza_de_animal']; ?></p>
                            <p><span class="negrita">Ubicación:</span> <?php echo $fila['localidad']; ?></p>
                            <p><span class="negrita">Teléfono:</span> <?php echo $fila['telefono']; ?></p>
                            
                            <div class="descripcion-corta">
                                <?php echo $fila['informacion_del_criador']; ?>
                            </div>

                            <div class="badge-verificado">Verificado</div>
                        </div>
                    </div>
                <?php 
                } 
            } else {
                /* 4. MANEJO DE ESTADO VACÍO:
                   Si no hay datos, mostramos un mensaje amigable para que la pǵina web no se vea vacía. */
                echo "<div class='no-datos'>
                        <h2>Próximamente nuevos criadores verificados 🐾</h2>
                        <p>Estamos validando las certificaciones oficiales de nuevos profesionales.</p>
                      </div>";
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