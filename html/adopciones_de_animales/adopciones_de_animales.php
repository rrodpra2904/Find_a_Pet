<?php 
// Incluyo el archivo de seguridad para comprobar que el usuario ha pasado por el formulario previo.
// Si no tiene la sesión activa, este archivo lo echará automáticamente.
include("../administrador/seguridad_formulario_adopciones.php"); 
?>

<?php
// 1. Conexión a la base de datos para poder consultar y actualizar los animales.
include 'conexion.php'; 

// 2. Lógica para procesar la adopción de animales cuando el usuario pulsa el botón.

// Compruebo si me llega por la URL el ID del animal que se quiere adoptar.
if (isset($_GET['id_adoptar'])) {
    $id = $_GET['id_adoptar'];

    // Actualizo la tabla: pongo 'Si' en adoptado para que ya no salga en la lista
    // y cambio el rol a 'Cliente' para registrar que ese animal ya tiene dueño.
    $sql_update = "UPDATE animales SET adoptado = 'Si', rol = 'Cliente' WHERE id = $id";
    
    // Si la consulta se ejecuta bien, lanzo un aviso y refresco la página web de adopciones de animales.
    if (mysqli_query($conexion, $sql_update)) {
        echo "<script>
                alert('¡Felicidades! Has adoptado a este animal 🐾');
                window.location.href='../adopciones_de_animales/adopciones_de_animales.php';
              </script>";
        exit(); 
    }
}
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

    <h1>Adoptar un animal 🐾 </h1><br>

    <p class="parrafo">
        Bienvenido a nuestra página web de adopciones de animales. Aquí puedes ver a todos los animales que buscan un hogar. 
        Adoptar es un compromiso de por vida, <br><br>¡encuentra a tu compañero ideal y dale la oportunidad que se merece!
    </p><br>

   <br> 
   <div class="contenedor">
        <?php
        // Consulto solo los animales que no han sido adoptados todavía.
        // Los ordeno por ID descendente para que los últimos en llegar aparezcan primero.
        $sql = "SELECT * FROM animales WHERE adoptado = 'No' ORDER BY id DESC";
        $res = mysqli_query($conexion, $sql);
        
        // Empiezo un bucle para crear una "caja" o tarjeta por cada animal que haya en la base de datos.
        while ($f = mysqli_fetch_assoc($res)) {
        ?>
            <div class="caja">
                <img src="../imagenes/adopciones_de_animales/<?php echo $f['imagen']; ?>"><br>
                
                <div class="info">
                    <h3><?php echo $f['nombre']; ?></h3><br>
                    <p><b>Raza:</b> <?php echo $f['raza']; ?></p>
                    <p><b>Edad:</b> <?php echo $f['edad']; ?> años</p><br>
                    <p><?php echo $f['descripcion']; ?></p>
                </div>

                <div class="botones">
                    <a href="?id_adoptar=<?php echo $f['id']; ?>" 
                       onclick="return confirm('¿Seguro que quieres adoptar a <?php echo $f['nombre']; ?>?')" 
                       class="btn">Solicitar adopción</a>
                </div>
            </div>
        <?php } // Aquí termina el bucle de los animales ?>
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