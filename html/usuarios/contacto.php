<?php
// Inicio la sesión si no está activa para poder verificar si el usuario está registrado
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        /* Fuerza la tipografía Poppins para mantener la consistencia */
        * {
            font-family: 'Poppins', sans-serif !important;
        }
    </style>
</head>
<body class="fondo-formulario" style="margin: 0; padding: 0; display: flex; flex-direction: column; min-height: 100vh;">

    <header style="margin: 0; padding: 0;">
        <div class="top-bar">Correo electrónico: findaapet@gmail.com<br>Teléfono: 654 987 321</div>
        <nav class="navbar-custom">
            <div class="container nav-container">
                <a class="logo-link" href="index.php">
                    <img src="imagenes/findapet.jpeg" alt="Logo">
                </a>
                <div class="menu-links">
                    <a href="index_inicio.php" class="fw-semibold">Inicio</a>
                    <a href="./adopciones_de_animales/adopciones_de_animales.php" class="fw-semibold">Adoptar</a>
                    
                    <?php if(isset($_SESSION['usuarioAutenticado'])): ?>
                        <a href="./criadores_de_animales/criadores_de_animales.php" class="fw-semibold">Criadores</a>
                    <?php endif; ?>
                    
                    <a href="sobre_nosotros.html" class="fw-semibold">Sobre nosotros</a>
                    <a href="contacto.php" class="fw-semibold">Contacto</a>
                </div>
                <div class="botones-acceso">
                    <?php if(isset($_SESSION['usuarioAutenticado'])): ?>
                        <a href="logout_usuarios.php" class="boton-acceso">Cerrar sesión</a>
                    <?php else: ?>
                        <a href="login_usuarios.php" class="boton-acceso">Entrar</a>
                        <a href="registro_usuarios.php" class="boton-registro">Registrarse</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main style="flex: 1; display: flex; align-items: center; justify-content: center; padding: 20px 0;">
        <div class="contenedor">
            <div class="caja-formulario">
                <h2>Contacto</h2>

                <p class="texto-secundario">
                    Si tienes dudas o quieres colaborar con nosotros o quieres dar tu perro o gato en adopción, envíanos un mensaje para tener mas información.
                </p><br>

                <?php 
                if (isset($_GET['e'])) {
                    $e = $_GET['e'];
                    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px; font-family: sans-serif; text-align: center;'>";
                    echo "<strong>⚠️ Por favor, revisa lo siguiente:</strong><br>";
                    
                    if (strpos($e, "1") !== false) { 
                        echo "• Te faltan huecos por rellenar o has introducido solo espacios.<br>"; 
                    }
                    if (strpos($e, "2") !== false) { 
                        echo "• El correo electrónico no tiene un formato válido.<br>"; 
                    }
                    
                    echo "</div>";
                }
                ?>
                <form action="validar_contacto.php" method="POST">
                    
                    <div class="bloque">
                        <label for="nombre">Nombre completo</label>
                        <input type="text" id="nombre" name="nombre" 
                               value="<?php echo isset($_GET['nom']) ? htmlspecialchars($_GET['nom']) : ''; ?>" required>

                        <label for="email">Correo electrónico</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo isset($_GET['ema']) ? htmlspecialchars($_GET['ema']) : ''; ?>" required>

                        <label for="asunto">Asunto</label>
                        <input type="text" id="asunto" name="asunto" 
                               value="<?php echo isset($_GET['asu']) ? htmlspecialchars($_GET['asu']) : ''; ?>" required>

                        <label for="mensaje">Mensaje</label>
                        <textarea id="mensaje" name="mensaje" rows="5" required><?php echo isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : ''; ?></textarea>
                    </div>

                    <div class="bloque-final">
                        <button type="submit" class="boton">Enviar mensaje</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer class="footer-custom">
        <div class="footer-container">
            <div class="footer-col">
                <img src="imagenes/findapet.jpeg" alt="Logo" class="footer-logo-img">
                <p class="footer-text">Plataforma para la adopción responsable y contacto con criadores.</p>
            </div>
            <div class="footer-col footer-col-center">
                <h4 class="footer-title-sub">Navegación</h4>
                <ul class="footer-list">
                    <li><a href="index_inicio.php">Inicio</a></li>
                    <li><a href="formulario_adopciones_de_animales.php">Adoptar</a></li>
                    <li><a href="formulario_criadores_de_animales.php">Criadores</a></li>
                    <li><a href="contacto.php">Contacto</a></li>
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

    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.pathname);
        }
    </script>
    
</body>
</html>