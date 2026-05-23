<?php
// ====================================================================
// 1. CONTROL DE ACCESO Y SEGURIDAD
// ====================================================================
session_start();
require '../conexion_db.php'; 

if (!isset($_SESSION['usuarioAutenticado']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: login_usuarios.php");
    exit();
}

$nombreUsuario = $_SESSION['usuarioAutenticado'];

// ====================================================================
// 2. CONFIGURACIÓN DEL TABLÓN (Base de Datos)
// ====================================================================
$hayAnimalesEnBaseDatos = false; 
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
        /* RESETEO TOTAL: Adiós al filo gris del body */
        html, body { 
            margin: 0 !important; 
            padding: 0 !important; 
            background-color: #f8f9fa; 
            font-family: 'Poppins', sans-serif; 
            color: #333;
            top: 0 !important;
        }

        /* BARRA DE NAVEGACIÓN: Fondo morado corporativo de Find a Pet */
        .navbar {
            background-color: #9b59b6; 
            border-bottom: 3px solid #8e44ad; 
            padding: 10px 40px !important;    
            margin: 0 !important;             
            display: flex; 
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            width: 100% !important;
        }

        /* CONTENEDOR DEL LOGO: Alineado a la izquierda */
        .navbar-brand {
            display: flex;
            align-items: center;
            padding-right: 20px;
            flex: 1; /* Le da espacio equivalente para equilibrar el centrado */
        }

        /* EL LOGO: Con fondo blanco de protección para que resalte perfecto sobre el morado */
        .navbar-brand img {
            height: 70px;         
            width: auto !important;
            object-fit: contain;
            display: block;
            margin-right:80px;
            background-color: #ffffff;
            padding: 4px;
            border-radius: 8px;
        }

        /* MENÚ DE ENLACES: Completamente centrado en la barra */
        .menu-enlaces { 
            display: flex; 
            gap: 12px !important;             
            align-items: center; 
            justify-content: center;
            flex: 2; /* Toma el espacio central prioritario */
        }

        /* BOTONES DEL MENÚ: Letras blancas, en negrita y súper legibles */
        .menu-enlaces button {
            background: none; 
            border: none; 
            font-family: 'Poppins', sans-serif;
            color: #ffffff;                  /* Color blanco nítido */
            font-weight: 700;                /* Negrita bien marcada */
            font-size: 14px;                 
            text-transform: uppercase; 
            letter-spacing: 0.3px; 
            padding: 8px 14px;  
            cursor: pointer; 
            border-radius: 18px; 
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        /* Hover y Estado Activo del menú morado */
        .menu-enlaces button:hover { 
            color: #ffffff; 
            background-color: rgba(255, 255, 255, 0.15); }
        .menu-enlaces button.activo { 
            color: #363636; 
            background-color: #ffffff; 
            font-weight: 700; box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15); }

        /* Div invisible para equilibrar el flex a la derecha y que el menú quede matemáticamente centrado */
        .navbar-spacer {
            flex: 1;
            display: flex;
            justify-content: flex-end;
        }

        /* CUERPO DEL PANEL */
        .main-wrapper { max-width: 950px; margin: 35px auto; padding: 0 20px; }
        .contenido-pestana { display: none; animation: fadeIn 0.4s ease; }
        .contenido-pestana.activo { display: block; }
        

        .section-header { margin-bottom: 30px; text-align: left; }
        .section-header h2 { font-size: 25px; color: #313131; margin: 0 0 6px 0; font-weight: 700; }
        .section-header p { font-size: 13.5px; color: #353636; margin: 0; }

        .cards-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; margin-top: 20px; }
        .card-manual {
            background: #ffffff; border: 1px solid #edf2f7; border-radius: 14px;
            padding: 24px; text-align: left; box-shadow: 0 5px 15px rgba(0,0,0,0.02);
            display: flex; gap: 15px;
        }
        .card-manual:hover { 
            box-shadow: 0 10px 25px rgba(155, 89, 182, 0.08); 
            border-color: #e8d5f2; }
        .card-icon { 
            font-size: 24px; 
            background: #f5edfa; 
            width: 50px; 
            height: 50px; 
            border-radius: 12px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: #fffeff; 
            flex-shrink: 0; 
        }
        .card-text h3 { margin: 0 0 6px 0; font-size: 15px; color: #292929; font-weight: 600; }
        .card-text p { margin: 0; font-size: 13px; color: #424242; line-height: 1.6; }

        .status-box-empty {
            background: #ffffff; 
            border: 2px dashed #e2e8f0; 
            border-radius: 16px;
            padding: 50px 30px; 
            text-align: center; 
            color: #373838; 
            max-width: 650px; 
            margin: 30px auto;
        }

        /* BOTONES INTERNOS DEL USUARIO: Fondo lila claro, letras negras y en negrita */
        .btn-action-primary {
            background-color: #f3e8f9 !important;  
            color: #000000 !important;             
            font-weight: 700 !important;           
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            display: inline-block;
            border-radius: 10px;
            border: 1px solid #e2cdf0;
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(155, 89, 182, 0.05);
            cursor: pointer;
        }
        .btn-action-primary:hover {
            background-color: #e2cdf0 !important;  
            color: #000000 !important;
            transform: translateY(-1px);
        }

        /* BOTÓN CERRAR SESIÓN: Fondo rojo granada muy suave, letras negras y en negrita */
        .btn-logout-inside {
            background-color: #ffa1a1c4 !important;  /* Rojo granada muy claro */
            color: #0a0a0a !important;             /* Letras negras */
            font-weight: 700 !important;           /* En negrita */
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            display: inline-block;
            border-radius: 10px;
            border: 1px solid #f8d7da;             
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.03);
            cursor: pointer;
        }
        .btn-logout-inside:hover {
            background-color: #f8d7da !important;  
            color: #000000 !important;
            transform: translateY(-1px);
        }

        /* BOTÓN ELIMINAR CUENTA: Fondo rojo medio suave, letras negras y en negrita */
        .btn-delete-inside {
            background-color: #ff7e8fd3 !important;  /* Rojo un pelín más marcado (medio suave) */
            color: #000000 !important;             /* Letras negras */
            font-weight: 700 !important;           /* En negrita */
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            display: inline-block;
            border-radius: 10px;
            border: 1px solid #ffcdd2;             
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.05);
            cursor: pointer;
        }
        .btn-delete-inside:hover {
            background-color: #ffcdd2 !important;  
            color: #000000 !important;
            transform: translateY(-1px);
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
                    <p>
                        Aquí puedes consultar animales disponibles antes de que aparezcan en la web pública o subir un aviso si has encontrado un animal perdido o no puedes hacerte cargo de él. 
                        Los animales verificados aparecerán marcados en <strong>color verde</strong>.
                    </p>
                </div>
            </div>

            <div class="card-manual">
                <div class="card-icon">📝</div>
                <div class="card-text">
                    <h3>Adopciones</h3>
                    <p>
                        Para poder adoptar, primero tendrás que rellenar el formulario de compromiso. 
                        Solo es necesario hacerlo una vez. Después podrás acceder al catálogo de animales disponibles.
                    </p>
                </div>
            </div>

            <div class="card-manual">
                <div class="card-icon">📂</div>
                <div class="card-text">
                    <h3>Mis Formularios</h3>
                    <p>
                        Aquí podrás consultar los formularios enviados anteriormente y descargarlos en PDF cuando lo necesites.
                    </p>
                </div>
            </div>

            <div class="card-manual">
                <div class="card-icon">🩺</div>
                <div class="card-text">
                    <h3>Cartilla Veterinaria</h3>
                    <p>
                        En esta sección podrás consultar la información veterinaria de tus mascotas, como vacunas, raza, edad, desparasitaciones, etc.
                    </p>
                </div>
            </div>

            <div class="card-manual">
                <div class="card-icon">⚙️</div>
                <div class="card-text">
                    <h3>Mi Perfil</h3>
                    <p>
                        Desde aquí podrás modificar tus datos personales, cambiar la imagen de perfil, cerrar sesión o eliminar tu cuenta.
                    </p>
                </div>
            </div>

        </div>
    </div>

    <div id="tablon" class="contenido-pestana">
    <div class="section-header">
        <h2>Tablón de adopciones de animales</h2><br>

        <p>
            Échale un vistazo a los animales disponibles antes de que aparezcan en la página pública de adopciones.
            También puedes subir tú mismo un animal perdido que hayas encontrado o uno del que no puedas hacerte cargo por cualquier motivo.
            Antes de poder adoptarlos, primero tendremos que revisarlos y darles el visto bueno
            (<strong>los verás destacados en color verde</strong>).
        </p><br>

        <p>
            Si después de una semana nadie solicita la adopción, el anuncio pasará automáticamente a la web pública.
        </p><br>

        <div style="margin-top: 15px; background: #e8f5e9; border-left: 4px solid #2e7d32; padding: 15px; border-radius: 4px; font-size: 13.5px; color: #1b5e20; line-height: 1.5;">

            <strong>🐾 ¿Cómo funciona el proceso para adoptar desde aquí?</strong><br><br>

            1. Elige uno de los animales que ya estén verificados (los marcados en color verde).<br><br>

            2. Si es tu primera vez, entra en la sección "Adopciones" y rellena el
            <strong>Formulario de Compromiso</strong>.<br><br>

            3. Cuando envíes el formulario, te facilitaremos el contacto
            (teléfono o correo electrónico) de la persona responsable para que puedas hablar con ella y enviar la documentación necesaria.<br><br>

            4. Una vez recibida la documentación, la revisaremos. Si todo es correcto, retiraremos al animal del tablón y de la página web de adopciones.

        </div>
    </div>
        
        <div style="text-align: left; margin-bottom: 25px;">
            <a href="subir_animal.php" class="btn-action-primary" style="padding: 10px 20px; font-size: 13px;">➕ Subir un animal (Encontrado o caso particular)</a>
        </div>

        <?php if (!$hayAnimalesEnBaseDatos): ?>
            <div class="status-box-empty">
                <span class="icon">📭</span>
                <h4>El tablón está vacío ahora mismo</h4>
                <p>No hay ninguna mascota publicada en este momento. Cuando un usuario suba un animal (porque lo ha encontrado o porque no puede atenderlo) y lo revisemos, aparecerá aquí en verde enseguida.</p>
            </div>
        <?php else: ?>
            <?php endif; ?>
    </div>

    <div id="adopciones" class="contenido-pestana">
        <div class="section-header">
            <h2>Formulario de Compromiso</h2>
            <p>Necesitamos que rellenes este formulario para poder gestionar cualquier adopción en la plataforma.</p>
        </div>
        
        <div style="background: #ffffff; border: 1px solid #edf2f7; padding: 35px; border-radius: 16px; text-align: left; box-shadow: 0 4px 15px rgba(0,0,0,0.01);">
            
            <h4 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 15px; font-weight: 600;">📋 ¿Qué ocurre al enviarlo?</h4>
            <p style="font-size: 13.5px; color: #6c757d; line-height: 1.6; margin: 0 0 20px 0;">
               Cuando completes el formulario, guardaremos tus datos y no tendrás que volver a introducirlos.
               Después serás redirigido a la página web de adopciones para continuar con el proceso.
            </p>

            <hr style="border: 0; border-top: 1px solid #edf2f7; margin: 25px 0;">

            <h4 style="margin: 0 0 15px 0; color: #9b59b6; font-size: 15px; font-weight: 600;">📝 Rellena tus datos aquí:</h4>
            
            <div style="background: #fafafa; border: 1px solid #eee; padding: 25px; border-radius: 12px;">
                <?php include '../formulario_adopciones_de_animales.php'; ?>
            </div>
        </div>
    </div>

    <div id="formularios" class="contenido-pestana">
        <div class="section-header">
            <h2>Mis Formularios</h2>
            <p>Aquí se guardan las copias de los documentos que vayas tramitando en la plataforma.</p>
        </div>
        
        <div class="status-box-empty">
            <span class="icon">📂</span>
            <h4>Todavía no hay ningún formulario rellenado.</h4>
            <p>Cuando completes el formulario por primera vez en la sección "Adopciones", se guardará aquí automáticamente para que puedas consultarlo o descargarlo en PDF cuando lo necesites.</p>
        </div>
    </div>

    <div id="cartilla" class="contenido-pestana">
        <div class="section-header">
            <h2>Cartilla Veterinaria</h2>
            <p>Información sobre la salud y revisiones de tu mascota:</p>
        </div>
        
        <div class="status-box-empty">
            <span class="icon">🩺</span>
            <h4>No tienes ningún animal asignado.</h4>
            <p>En esta sección podrás consultar los datos de tu mascota de forma clara, como sus vacunas, desparasitaciones, raza, edad, etc.
               Si adoptas un animal, su información aparecerá automáticamente aquí para que puedas tener su cartilla veterinaria siempre disponible.</p>
        </div>
    </div>

    <div id="perfil" class="contenido-pestana">
        <div class="section-header">
            <h2>Mi Perfil</h2>
            <p>Aquí puedes consultar y modificar los datos de tu cuenta cuando lo necesites.</p>
        </div>
        
        <div style="background: white; border: 1px solid #edf2f7; border-radius: 14px; padding: 30px; text-align: left; box-shadow: 0 4px 12px rgba(0,0,0,0.01);">
            <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px;">
                <div style="width: 80px; height: 80px; background: #f5edfa; border: 2px dashed #9b59b6; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; color: #9b59b6; font-weight: 600;">Foto de perfil:</div>
                <div>
                    <h3 style="margin: 0 0 4px 0; color: #2c3e50; font-size: 16px;">Usuario: <?php echo $nombreUsuario; ?></h3>
                    <p style="margin: 0; font-size: 12px; color: #7f8c8d;">Usuario de la comunidad</p>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <a href="editar_perfil.php" class="btn-action-primary" style="padding: 10px 20px; font-size: 13px;">⚙️ Editar mis datos</a>
                <a href="logout.php" class="btn-logout-inside" style="padding: 10px 20px; font-size: 13px;">Cerrar Sesión ✕</a>
                <a href="dar_baja.php" class="btn-delete-inside" style="padding: 10px 20px; font-size: 13px;" onclick="return confirm('¿Seguro que quieres borrar tu cuenta? Perderás todo lo que tienes guardado.');">Eliminar cuenta</a>
            </div>
        </div>
    </div>

</div>

<script>
function cambiarPestana(evt, nombrePestana) {
    var contenidos = document.getElementsByClassName("contenido-pestana");
    for (var i = 0; i < contenidos.length; i++) { contenidos[i].classList.remove("activo"); }

    var botones = document.querySelectorAll(".menu-enlaces button");
    for (var i = 0; i < botones.length; i++) { botones[i].classList.remove("activo"); }

    document.getElementById(nombrePestana).classList.add("activo");
    evt.currentTarget.classList.add("activo");
}
</script>

</body>
</html>