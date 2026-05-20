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
// 2. CONTROL DEL TABLÓN (Base de Datos)
// ====================================================================
// Cambia a true cuando tu consulta de la base de datos encuentre animales
$hayAnimalesEnBaseDatos = false; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Find a Pet</title>
    
    <link rel="stylesheet" href="styles_login_usuarios.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Diseño de la página */
        body { 
            margin: 0; padding: 0; background-color: #f8f9fa; 
            font-family: 'Poppins', sans-serif; color: #333;
        }

        /* Barra de menú superior */
        .navbar {
            background-color: #ffffff; border-bottom: 3px solid #9b59b6;
            padding: 15px 40px; display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 4px 20px rgba(155, 89, 182, 0.08);
        }

        .navbar .logo-txt { font-weight: 700; color: #2c3e50; font-size: 18px; letter-spacing: -0.5px; }
        .menu-enlaces { display: flex; gap: 15px; align-items: center; }

        .menu-enlaces button {
            background: none; border: none; font-family: 'Poppins', sans-serif;
            color: #7f8c8d; font-weight: 600; font-size: 13px;
            text-transform: uppercase; letter-spacing: 0.5px; padding: 8px 16px;
            cursor: pointer; border-radius: 20px; transition: all 0.3s ease;
        }

        .menu-enlaces button:hover { color: #9b59b6; background-color: #f5edfa; }
        .menu-enlaces button.activo { color: #ffffff; background-color: #9b59b6; font-weight: 600; box-shadow: 0 4px 10px rgba(155, 89, 182, 0.2); }

        /* Botón de cerrar sesión */
        .btn-logout {
            color: #e74c3c !important; font-weight: 700 !important; text-decoration: none; 
            font-size: 13px; text-transform: uppercase; margin-left: 10px; 
            padding: 8px 16px; border-radius: 20px; transition: all 0.3s ease;
        }
        .btn-logout:hover {
            color: #ffffff !important; background-color: #e74c3c;
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
        }

        /* Contenedor de contenidos */
        .main-wrapper { max-width: 850px; margin: 40px auto; padding: 0 20px; }
        .contenido-pestana { display: none; animation: fadeIn 0.4s ease; }
        .contenido-pestana.activo { display: block; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Cabeceras */
        .section-header { margin-bottom: 30px; text-align: left; }
        .section-header h2 { font-size: 26px; color: #2c3e50; margin: 0 0 8px 0; font-weight: 700; }
        .section-header p { font-size: 14px; color: #7f8c8d; margin: 0; }

        /* Tarjetas de las Instrucciones */
        .cards-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; margin-top: 20px; }
        .card-manual {
            background: #ffffff; border: 1px solid #edf2f7; border-radius: 14px;
            padding: 24px; text-align: left; box-shadow: 0 5px 15px rgba(0,0,0,0.02);
            transition: all 0.3s ease; display: flex; gap: 15px;
        }
        .card-manual:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(155, 89, 182, 0.08); border-color: #e8d5f2; }
        .card-icon { font-size: 24px; background: #f5edfa; width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #9b59b6; flex-shrink: 0; }
        .card-text h3 { margin: 0 0 6px 0; font-size: 15px; color: #2c3e50; font-weight: 600; }
        .card-text p { margin: 0; font-size: 13px; color: #6c757d; line-height: 1.6; }

        /* Cuadros de aviso cuando algo está vacío */
        .status-box-empty {
            background: #ffffff; border: 2px dashed #e2e8f0; border-radius: 16px;
            padding: 50px 30px; text-align: center; color: #7f8c8d; max-width: 650px; margin: 30px auto;
        }
        .status-box-empty .icon { font-size: 45px; margin-bottom: 15px; display: inline-block; }
        .status-box-empty h4 { margin: 0 0 8px 0; color: #2c3e50; font-size: 16px; font-weight: 600; }
        .status-box-empty p { margin: 0; font-size: 13.5px; line-height: 1.6; color: #7f8c8d; }

        /* Lista de pasos */
        .lista-proceso { margin: 20px 0; padding: 0; list-style: none; text-align: left; }
        .lista-proceso li { position: relative; padding-left: 30px; margin-bottom: 12px; font-size: 14px; color: #555; line-height: 1.6; }
        .lista-proceso li::before { content: "➔"; position: absolute; left: 0; color: #9b59b6; font-weight: bold; }

        /* Botones */
        .btn-action-primary {
            display: inline-block; background: linear-gradient(135deg, #9b59b6, #8e44ad);
            color: #ffffff; padding: 12px 24px; border-radius: 25px; text-decoration: none;
            font-weight: 600; font-size: 14px; border: none; cursor: pointer;
            box-shadow: 0 4px 15px rgba(155, 89, 182, 0.3); transition: all 0.3s ease;
        }
        .btn-action-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(155, 89, 182, 0.4); }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="logo-txt">Find a Pet 🐾</div>
    <div class="menu-enlaces">
        <button id="btn-instrucciones" class="activo" onclick="cambiarPestana(event, 'instrucciones')">Instrucciones</button>
        <button id="btn-tablon" onclick="cambiarPestana(event, 'tablon')">Tablón de Adopciones</button>
        <button id="btn-adopciones" onclick="cambiarPestana(event, 'adopciones')">Adopciones</button>
        <button id="btn-formularios" onclick="cambiarPestana(event, 'formularios')">Mis Formularios</button>
        <button id="btn-cartilla" onclick="cambiarPestana(event, 'cartilla')">Cartilla Sanitaria</button>
        <button id="btn-perfil" onclick="cambiarPestana(event, 'perfil')">Mi Perfil</button>
        <a href="logout.php" class="btn-logout">Cerrar Sesión ✕</a>
    </div>
</nav>

<div class="main-wrapper">

    <div id="instrucciones" class="contenido-pestana activo">
        <div class="section-header">
            <h2>¡Hola, <?php echo htmlspecialchars($nombreUsuario); ?>!</h2>
            <p>Te damos la bienvenida. Aquí te explicamos de forma muy sencilla para qué sirve cada apartado del menú:</p>
        </div>
        
        <div class="cards-grid">
            <div class="card-manual">
                <div class="card-icon">📋</div>
                <div class="card-text">
                    <h3>Pestaña: Instrucciones</h3>
                    <p>Es esta pantalla en la que estás ahora. Sirve de guía rápida para que sepas cómo moverte por la aplicación y qué puedes hacer en cada sitio.</p>
                </div>
            </div>

            <div class="card-manual">
                <div class="card-icon">📢</div>
                <div class="card-text">
                    <h3>Pestaña: Tablón de Adopciones</h3>
                    <p>Un espacio para buscar una mascota o para registrar una nueva publicación si quieres dar un animal en adopción. También sirve para avisar si has visto a un perro o gato abandonado en la calle para que podamos ayudarlo.</p>
                </div>
            </div>

            <div class="card-manual">
                <div class="card-icon">📝</div>
                <div class="card-text">
                    <h3>Pestaña: Adopciones</h3>
                    <p>Aquí es donde se hace todo el papeleo. Te explicamos los pasos a seguir y es el sitio donde tienes que rellenar el Formulario de Compromiso para poder adoptar a un animal.</p>
                </div>
            </div>

            <div class="card-manual">
                <div class="card-icon">📂</div>
                <div class="card-text">
                    <h3>Pestaña: Mis Formularios</h3>
                    <p>Tu carpeta personal. Aquí se guardan los formularios de compromiso que vayas rellenando y enviando, y podrás descargarlos en tu ordenador en formato PDF.</p>
                </div>
            </div>

            <div class="card-manual">
                <div class="card-icon">🩺</div>
                <div class="card-text">
                    <h3>Pestaña: Cartilla Sanitaria</h3>
                    <p>La ficha médica. Si miras la información de un animal, aquí verás todos sus datos de salud: su raza, los años o meses que tiene, si está desparasitado y las vacunas que lleva puestas.</p>
                </div>
            </div>

            <div class="card-manual">
                <div class="card-icon">⚙️</div>
                <div class="card-text">
                    <h3>Pestaña: Mi Perfil</h3>
                    <p>Tu cuenta personal. Desde aquí puedes cambiar tus datos de contacto, poner una foto de perfil nueva, cerrar sesión o darte de baja si ya no quieres usar la web.</p>
                </div>
            </div>
        </div>
    </div>

    <div id="tablon" class="contenido-pestana">
        <div class="section-header">
            <h2>Tablón de Adopciones</h2>
            <p>Aquí puedes buscar una mascota o rellenar una publicación si tienes o has visto un animal que necesita un hogar:</p>
        </div>
        
        <div style="text-align: left; margin-bottom: 25px;">
            <a href="subir_animal.php" class="btn-action-primary">➕ Registrar un Animal para Adopción / Avisar de un Abandono</a>
        </div>

        <?php if (!$hayAnimalesEnBaseDatos): ?>
            <div class="status-box-empty">
                <span class="icon">📭</span>
                <h4>El tablón está vacío por ahora</h4>
                <p>En este momento no hay ningún perro o gato registrado. Cuando un usuario suba una ficha de mascota o avise de un animal perdido y el administrador lo revise, aparecerá aquí abajo de inmediato.</p>
            </div>
        <?php else: ?>
            <?php endif; ?>
    </div>

    <div id="adopciones" class="contenido-pestana">
        <div class="section-header">
            <h2>¿Cómo funciona el proceso para adoptar?</h2>
            <p>Lee atentamente los pasos y rellena el formulario de abajo para empezar el proceso:</p>
        </div>
        
        <div style="background: #ffffff; border: 1px solid #edf2f7; padding: 35px; border-radius: 16px; text-align: left; box-shadow: 0 4px 15px rgba(0,0,0,0.01);">
            
            <h4 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 15px; font-weight: 600;">🐾 Pasos que debes seguir:</h4>
            
            <ul class="lista-proceso">
                <li><strong>Elige a tu compañero:</strong> Ve a la pestaña del "Tablón de Adopciones" y quédate con el nombre de la mascota que quieras acoger.</li>
                <li><strong>Rellena el documento de compromiso:</strong> El formulario de abajo es obligatorio. Sirve para asegurar que el animal va a estar bien cuidado, en un entorno seguro y bajo tu responsabilidad legal.</li>
                <li><strong>¿Qué pasa al enviarlo?:</strong> Al pulsar el botón, tu solicitud se guarda. Si el centro aprueba la adopción, el sistema hace un trabajo automático muy importante: **quita la ficha del animal del Tablón privado y también de la Página Web Pública** a la vez para que nadie más pueda pedirlo.</li>
                <li><strong>Descarga tu copia:</strong> Una vez enviado, podrás ir a "Mis Formularios" y bajarte un resguardo en PDF para guardarlo en tu ordenador o imprimirlo.</li>
            </ul>

            <hr style="border: 0; border-top: 1px solid #edf2f7; margin: 25px 0;">

            <h4 style="margin: 0 0 15px 0; color: #9b59b6; font-size: 15px; font-weight: 600;">📝 Rellena aquí tu Formulario de Compromiso:</h4>
            
            <div style="background: #fafafa; border: 1px solid #eee; padding: 25px; border-radius: 12px;">
                <?php include 'compromiso.php'; ?>
            </div>
            
            <div style="text-align: right; margin-top: 25px;">
                <button class="btn-action-primary" style="width: 100%; text-align: center;">
                    Enviar Formulario de Compromiso y Solicitar Adopción 🐾
                </button>
            </div>
        </div>
    </div>

    <div id="formularios" class="contenido-pestana">
        <div class="section-header">
            <h2>Mis Formularios de Adopción</h2>
            <p>Aquí tienes los resguardos de los documentos que has enviado:</p>
        </div>
        
        <div class="status-box-empty">
            <span class="icon">📂</span>
            <h4>Aún no has enviado ninguna solicitud</h4>
            <p>Esta pestaña es como tu archivador personal. En cuanto vayas a la pestaña "Adopciones" y mandes el formulario de compromiso relleno, aquí te aparecerá un botón para poder descargártelo en formato PDF.</p>
        </div>
    </div>

    <div id="cartilla" class="contenido-pestana">
        <div class="section-header">
            <h2>Cartilla Sanitaria</h2>
            <p>Información de vacunas y revisiones médicas de la mascota:</p>
        </div>
        
        <div class="status-box-empty">
            <span class="icon">🩺</span>
            <h4>No has seleccionado ningún animal</h4>
            <p>Para poder ver una cartilla médica, primero tienes que ir a la pestaña <strong>"Tablón de Adopciones"</strong> y pulsar sobre el animal que te interesa. Así el sistema sabrá de qué perro o gato quieres consultar la edad, la raza, las vacunas o si está desparasitado.</p>
        </div>
    </div>

    <div id="perfil" class="contenido-pestana">
        <div class="section-header">
            <h2>Mi Perfil de Usuario</h2>
            <p>Gestiona tus datos de usuario de forma sencilla:</p>
        </div>
        
        <div style="background: white; border: 1px solid #edf2f7; border-radius: 14px; padding: 30px; text-align: left; box-shadow: 0 4px 12px rgba(0,0,0,0.01);">
            <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px;">
                <div style="width: 80px; height: 80px; background: #f5edfa; border: 2px dashed #9b59b6; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; color: #9b59b6; font-weight: 600;">TU FOTO</div>
                <div>
                    <h3 style="margin: 0 0 4px 0; color: #2c3e50; font-size: 16px;"><?php echo htmlspecialchars($nombreUsuario); ?></h3>
                    <p style="margin: 0; font-size: 12px; color: #7f8c8d;">Tipo de cuenta: Usuario Adoptante</p>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <a href="editar_perfil.php" class="btn-action-primary" style="padding: 10px 20px; font-size: 13px;">⚙️ Cambiar mis Datos</a>
                <a href="dar_baja.php" class="btn-action-primary" style="background: #7f8c8d; box-shadow: none; padding: 10px 20px; font-size: 13px;" onclick="return confirm('¿Seguro que quieres borrar tu cuenta? Se eliminarán tus datos de la web por completo.');">✕ Borrar mi Cuenta</a>
            </div>
        </div>
    </div>

</div>

<script>
function cambiarPestana(evt, nombrePestana) {
    var contenidos = document.çgetElementsByClassName("contenido-pestana");
    for (var i = 0; i < contenidos.length; i++) { contenidos[i].classList.remove("activo"); }

    var botones = document.querySelectorAll(".menu-enlaces button");
    for (var i = 0; i < botones.length; i++) { botones[i].classList.remove("activo"); }

    document.getElementById(nombrePestana).classList.add("activo");
    evt.currentTarget.classList.add("activo");
}
</script>

</body>
</html>