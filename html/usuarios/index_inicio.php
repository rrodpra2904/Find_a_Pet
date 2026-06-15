<?php 
// Inicio la sesión al principio para comprobar si el usuario está logueado
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find a Pet</title>
    <link rel="stylesheet" href="styles.css">
    
    <link rel="preconnect" href="https://dog.ceo">
    <link rel="preconnect" href="https://api.thecatapi.com">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
</head>

<body class="body-custom"> 

<div class="top-bar">Correo electrónico: findaapet@gmail.com<br>Teléfono: 654 987 321</div>

<nav class="navbar-custom">
    <div class="container nav-container">
        <a class="logo-link" href="index.php">
            <img src="imagenes/findapet.jpeg" alt="Logo">
        </a>
        <div class="menu-links">
            <a href="./index_inicio.php" class="fw-semibold">Inicio</a>
            <a href="adopciones_de_animales/adopciones_de_animales.php" class="fw-semibold">Adoptar</a>
            <a href="formulario_criadores_de_animales.php" class="fw-semibold">Criadores</a>
            <a href="sobre_nosotros.html" class="fw-semibold">Sobre nosotros</a>
            <a href="contacto.php" class="fw-semibold">Contacto</a>
        </div>
        <div class="botones-acceso">
            <?php if(isset($_SESSION['usuario_id']) || isset($_SESSION['email']) || isset($_SESSION['usuarioAutenticado'])): ?>
                <a href="./logout_usuarios.php" class="boton-acceso">Cerrar sesión</a>
            <?php else: ?>
                <a href="./usuarios/login_usuarios.php" class="boton-acceso">Entrar</a>
                <a href="./usuarios/registro_usuarios.php" class="boton-registro">Registrarse</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="slider-seguro-container" style="max-width: 1200px; margin: 50px auto 20px auto; padding: 0 15px; position: relative;">
    
    <button onclick="cambiarFotoManual(-1)" class="flecha-slider" style="left: 30px;">❮</button>
    
    <img id="fotoSliderPrincipal" src="https://images.unsplash.com/photo-1543466835-00a7907e9de1?q=80&w=1200" 
         alt="Mascotas Find a Pet" 
         style="width: 100%; height: 400px; object-fit: cover; border-radius: 20px; transition: opacity 0.4s ease-in-out; display: block;">
    
    <button onclick="cambiarFotoManual(1)" class="flecha-slider" style="right: 30px;">❯</button>
</div>

<style>
    .flecha-slider {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background-color: rgba(0, 0, 0, 0.4);
        color: white;
        border: none;
        font-size: 24px;
        padding: 12px 18px;
        cursor: pointer;
        border-radius: 50%;
        transition: background-color 0.3s, transform 0.2s;
        z-index: 10;
    }

    .flecha-slider:hover {
        background-color: rgba(0, 0, 0, 0.8);
        transform: translateY(-50%) scale(1.1);
    }
</style>

<script>
    (function() {
        const misFotos = [
            "https://images.unsplash.com/photo-1543466835-00a7907e9de1?q=80&w=1200",
            "https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?q=80&w=1200",
            "https://images.unsplash.com/photo-1444212477490-ca407925329e?q=80&w=1200"
        ];
        
        let indiceActual = 0;
        const imgElemento = document.getElementById('fotoSliderPrincipal');
        let temporizadorAuto;

        function mostrarImagen(nuevoIndice) {
            if (!imgElemento) return;
            imgElemento.style.opacity = 0;
            
            setTimeout(() => {
                indiceActual = nuevoIndice;
                if (indiceActual >= misFotos.length) indiceActual = 0;
                if (indiceActual < 0) indiceActual = misFotos.length - 1;
                
                imgElemento.src = misFotos[indiceActual];
                imgElemento.style.opacity = 1;
            }, 400);
        }

        window.cambiarFotoManual = function(direccion) {
            clearInterval(temporizadorAuto);
            iniciarAutomático();
            mostrarImagen(indiceActual + direccion);
        }

        function iniciarAutomático() {
            temporizadorAuto = setInterval(() => {
                mostrarImagen(indiceActual + 1);
            }, 4000);
        }

        iniciarAutomático();
    })();
</script>

<div class="container hero-container">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="proximo_mejor_amigo">
                <b>Tu próximo mejor amigo te espera aquí</b>
            </h1>
            <p class="proximo_mejor_amigo-texto-principal">
                 En <b>Find a Pet</b> hacemos que encontrar a tu <b>perro o gato</b> ideal sea sencillo, seguro y, sobre todo, responsable.

                 Ya sea que busques adoptar o contactar con criadores certificados, estamos aquí para asegurar que todo el proceso sea transparente y pensando siempre en el bienestar animal. 
            </p>
            <p class="proximo_mejor_amigo-p-sub-texto-principal">
                Te invitamos a descubrir los valores que nos mueven y cómo trabajamos día a día para lograr un impacto positivo.
            </p>
            <div class="d-flex gap-3 mt-4">
                <a href="sobre_nosotros.html" class="boton-morado">Conócenos</a>
            </div>
        </div>
        <div class="col-md-6 text-center">
            <img src="imagenes/buscando_mascotas.jpeg" alt="Mascotas felices" class="proximo_mejor_amigo-img">
        </div>
    </div>
</div>
<div class="container my-5">
    <div class="p-5 compromiso-container">
        <div class="row align-items-center">
            <div class="col-md-5 mb-4 mb-md-0 text-center">
                <img src="imagenes/nuestro_compromiso.png" 
                     alt="Amor animal" class="img-fluid img-compromiso" style="border-radius: 20px;">
            </div>
            <div class="col-md-7 text-start ps-md-5">
                <h2 class="nuestro_compromiso">Nuestro compromiso</h2>
                <p class="texto-compromiso-negro">
                     En <b>Find a Pet</b>, estamos profundamente comprometidos con el cumplimiento de la Ley del Bienestar Animal.<br>
                     <br>
                     Trabajamos de forma activa para <b>evitar el abandono</b> y asegurar el bienestar de los animales. Nuestra misión es promover el rechazo absoluto al maltrato animal, fomentando que tanto las familias adoptantes como los criadores certificados cumplan con los estándares más estrictos de protección y responsabilidad.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="container intro-cajas-container">
    <h2 class="titulo-seccion-grande">Encuentra tu mascota ideal</h2> 
    <p class="parrafo_mascota_ideal">Ya sea que quieras adoptar o contactar con criadores de confianza, aquí encontrarás todo lo necesario para tomar la mejor decisión.</p>
</div>

<div class="container mb-5">
    <div class="row g-5 text-center">
    <div class="col-md-6">
        <div class="card-container">
            <a href="./adopciones_de_animales/adopciones_de_animales.php" class="card-link" style="text-decoration: none; color: inherit; display: block;">
                <div class="card-icon">🐶🐱</div>
                <h3 class="card-title">Adopción de animales</h3>
                <p class="card-text">
                    Hay cientos de <b>perros y gatos</b> esperando una oportunidad para quererte. Conoce a todos nuestros compañeros disponibles y encuentra a tu amigo ideal. 
                </p>
                <div>
                    <span class="boton-morado">Ver animales</span>
                </div>
            </a>
        </div>
    </div>
        <div class="col-md-6">
            <div class="card-container">
                <a href="./criadores_de_animales/criadores_de_animales.php" style="text-decoration: none; color: inherit; display: block;"><div class="card-icon">🏅</div>
                <h3 class="card-title">Criadores de animales</h3>
                <p class="card-text">
                    ¿Buscas una raza de perros o gatos específica con todas las garantías? Accede a nuestra red oficial de confianza. <b>Completa el formulario</b> para contactar con criadores éticos y certificados. 
                </p>
                <div>
                     <span class="boton-morado">Conócelos</span>
                </div>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container objectives-container">
    <div class="text-center mb-4">
        <h2>NUESTROS OBJETIVOS</h2>
    </div>
    <div class="table-responsive objectives-table-box p-3 border-0">
        <table class="table mb-0">
            <tbody>
                <tr class="border-bottom">
                    <td class="table-icon-cell">🐾</td>
                    <td class="table-text-cell texto-principal"> <b>Bienestar:</b> Asegurar que los animales vivan sanos y respetados.</td>
                </tr>
                <tr class="border-bottom">
                    <td class="table-icon-cell">🚫</td>
                    <td class="table-text-cell texto-principal"> <b>Responsabilidad:</b> Fomentamos el compromiso para evitar el abandono.</td>
                </tr>
                <tr>
                    <td class="table-icon-cell">🛡️</td>
                    <td class="table-text-cell texto-principal"> <b>Protección:</b>Velar por el cumplimiento estricto de la Ley de Bienestar Animal.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="container text-center mb-2">
    <h2 class="titulo">HISTORIAS CON FINAL FELIZ ❤️</h2>
    <p class="story-intro-text">Nuestra mayor alegría es ver cómo cada animal encuentra su lugar especial en el mundo.</p>
    <div class="story-box p-4 m-0"> 
        <p class="fw-bold">¿Has adoptado con nosotros?</p>
        <p>Si quieres compartir tu historia, nos encantaría recibirla.</p>
        <p class="m-0"><b>Correo: <a class="story-link">historias@findapet.com</a></b></p>
    </div>
</div>

<div class="container mb-5">
    <h2 class="titulo-seccion-lila">Animales con hogar</h2>
    <div class="contenedor-grid">
        <div class="caja-animal">
            <div class="img-box">
                <div class="loader" id="l-perro"></div>
                <img id="img-perro" src="" alt="Perro" class="revelar">
            </div>
            <div class="info-box">
                <h3 class="nombre-pet" id="nombre-perro"></h3><br>
                <p id="user-perro" class="pet-user-text pet-user"></p><br>
                <p id="loc-perro" class="ubicacion"></p><br>
            </div>
        </div>
        <div class="caja-animal">
            <div class="img-box">
                <div class="loader" id="l-gato"></div>
                <img id="img-gato" src="" alt="Gato" class="revelar">
            </div>
            <div class="info-box">
                <h3 class="nombre-pet" id="nombre-gato"></h3><br>
                <p id="user-gato" class="pet-user-text pet-user"></p><br>
                <p id="loc-gato" class="ubicacion"></p>
            </div>
        </div>
    </div>
</div>

<div class="container py-5 donation-section rounded-4">
    <div class="row px-5 align-items-center">
        <div class="col-md-8">
            <h2 class="donacion-titulo">DONACIÓN VOLUNTARIA 💖</h2>
            <p class="texto-principal">Ayúdanos a mantener la página web y colaborar con refugios de animales locales.</p>
            <div class="row">
                <div class="col-sm-6 mb-4">
                    <p class="mantenimiento-web">🔧 Apoyo para el mantenimiento de la página web:</p>
                    <span class="donation-number-box">600 189 240</span>
                </div>
                <div class="col-sm-6 mb-4">
                    <p class="mb-2 fw-semibold text-uppercase small">🐶 Apoyo para refugios de animales:</p>
                    <span class="donation-number-box">600 378 446</span>
                </div>
            </div>
        </div>
        <div>
            <img class="img-donacion" src="imagenes/donacion_voluntaria.jpeg" alt="Donación" style="width: 120px;">
        </div>
    </div>
</div>

<footer class="footer-custom">
    <div class="footer-container">
        <div class="footer-col">
            <img src="imagenes/findapet.jpeg" alt="Logo" class="footer-logo-img">
            <p class="footer-text">Plataforma para la adopción responsable y contacto con criadores.</p>
        </div>
        <div class="footer-col footer-col-center">
            <h4 class="footer-title-sub">Navegación</h4>
            <ul class="footer-list">
                <li><a href="index.php">Inicio</a></li>
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
    const nombres = ["Rex", "Luna", "Toby", "Simba", "Bella", "Kira", "Max", "Lola", "Coco", "Thor", "Nala", "Tayson"];
    const usuarios = ["animal_lover", "pet_adopt", "happy_dog", "cat_home", "vida_animal", "mascota_feliz"];
    const ubicaciones = ["Granada", "Motril", "Madrid", "Sevilla", "Barcelona", "Málaga", "Valencia"];

    function cargarMascotas() {
        const azar = (lista) => lista[Math.floor(Math.random() * lista.length)];

        document.getElementById('nombre-perro').innerText = azar(nombres);
        document.getElementById('user-perro').innerText = "@" + azar(usuarios);
        document.getElementById('loc-perro').innerText = "📍 " + azar(ubicaciones);

        document.getElementById('nombre-gato').innerText = azar(nombres);
        document.getElementById('user-gato').innerText = "@" + azar(usuarios);
        document.getElementById('loc-gato').innerText = "📍 " + azar(ubicaciones);

        const textos = document.querySelectorAll('.info-box h3, .info-box p');
        textos.forEach(t => { 
            t.style.opacity = "1"; 
            t.style.visibility = "visible"; 
        });

        const imgP = document.getElementById('img-perro');
        const imgG = document.getElementById('img-gato');

        if(imgG) imgG.src = "https://api.thecatapi.com/v1/images/search?format=src";
        
        fetch('https://dog.ceo/api/breeds/image/random')
            .then(res => res.json())
            .then(data => { 
                if(imgP) imgP.src = data.message; 
            });

        if(imgP) imgP.onload = () => { 
            imgP.style.opacity = "1"; 
            document.getElementById('l-perro').style.display = "none"; 
        };
        
        if(imgG) imgG.onload = () => { 
            imgG.style.opacity = "1"; 
            document.getElementById('l-gato').style.display = "none"; 
        };
    }

    document.addEventListener('DOMContentLoaded', cargarMascotas);
</script>

</body>
</html>