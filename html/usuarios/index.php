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
// COMPROBACIÓN DINÁMICA DE FORMULARIOS RELLENADOS (Para ocultar/mostrar)
// ====================================================================
$ya_ha_rellenado_adopciones = false;
$ya_ha_rellenado_criadores = false;

$fila_adopcion = null;
$fila_criador = null;

try {
    // CORRECCIÓN: Quitamos el "_de_" extra para que coincida exactamente con tu tabla real de la BD
    $sql_adopcion = "SELECT * FROM formulario_de_compromiso_adopciones_de_animales WHERE usuario_email = :email LIMIT 1";
    $stmt_a = $db->prepare($sql_adopcion);
    $stmt_a->bindParam(':email', $nombreUsuario);
    $stmt_a->execute();
    $fila_adopcion = $stmt_a->fetch(PDO::FETCH_ASSOC);
    if ($fila_adopcion) {
        $ya_ha_rellenado_adopciones = true;
    }
} catch (PDOException $e) {}

try {
    $sql_criador = "SELECT * FROM formulario_de_compromiso_de_criadores_de_animales WHERE usuario_email = :email LIMIT 1";
    $stmt_c = $db->prepare($sql_criador);
    $stmt_c->bindParam(':email', $nombreUsuario);
    $stmt_c->execute();
    $fila_criador = $stmt_c->fetch(PDO::FETCH_ASSOC);
    if ($fila_criador) {
        $ya_ha_rellenado_criadores = true;
    }
} catch (PDOException $e) {}

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
                <div class="card-icon">🐾🩺</div>
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

        <div style="margin-top: 35px; padding: 25px; background-color: #ffffff; border: 1px solid #edf2f7; border-left: 5px solid #9b59b6; border-radius: 12px; text-align: left; box-shadow: 0 4px 12px rgba(0,0,0,0.02);">
            <h3 style="margin: 0 0 8px 0; color: #2c3e50; font-size: 16px; font-weight: 600; font-family: 'Poppins', sans-serif;"><span style="font-size: 22px; vertical-align: middle;">🏆</span> Sección de Criadores Verificados</h3>
            <p style="margin: 0 0 20px 0; font-size: 13.5px; color: #7f8c8d; line-height: 1.6; font-family: 'Poppins', sans-serif;">
                Si estás buscando contactar con un criador profesional verificado para conocer ejemplares específicos, recuerda que puedes acceder al formulario de solicitud correspondiente para tramitar tu documentación de manera segura.
            </p>
            
            <?php if ($ya_ha_rellenado_criadores): ?>
                <a href="../criadores_de_animales/criadores_de_animales.php" class="btn-action-primary" style="display: inline-block; padding: 10px 20px; font-size: 13px; text-decoration: none; background-color: #27ae60; color: white;">
                    🏆 Acceder a la Web de Criadores Públicos
                </a>
            <?php else: ?>
                <a href="../formulario_criadores_de_animales.php" class="btn-action-primary" style="display: inline-block; padding: 10px 20px; font-size: 13px; text-decoration: none;">
                    📋 Acceder al Formulario de Criadores
                </a>
            <?php endif; ?>
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
                2. Si es tu primera vez, entra en la sección "Adopciones" and rellena el <strong>Formulario de Compromiso</strong>.<br><br>
                3. Cuando envíes el formulario, te facilitaremos el contacto (teléfono o correo electrónico) de la persona responsable para que puedas hablar con ella y enviar la documentación necesaria.<br><br>
                4. Una vez recibida la documentación, la revisaremos. Si todo es correcto, retiraremos al animal del tablón y de la página web de adopciones.
            </div>
        </div>
        
        <div style="text-align: left; margin-bottom: 25px;">
            <a href="subir_animal.php" class="btn-action-primary" style="padding: 10px 20px; font-size: 13px; text-decoration: none; display: inline-block;">➕ Subir un animal (Encontrado o caso particular)</a>
        </div>

        <?php if (!$hayAnimalesEnBaseDatos): ?>
            <div class="status-box-empty">
                <span class="icon" style="font-size: 48px; display: block; margin-bottom: 10px;">📭</span>
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
        
        <?php if ($ya_ha_rellenado_adopciones): ?>
            <div style="background: #ffffff; border: 1px solid #edf2f7; padding: 40px; border-radius: 16px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.01);">
                <span style="font-size: 48px;">✅</span>
                <h3 style="color: #2c3e50; margin-top: 15px;">¡Ya has completado tu compromiso de adopción!</h3>
                <p style="color: #7f8c8d; font-size: 14px; margin-bottom: 25px;">No necesitas volver a rellenar los datos. Puedes ir directamente a ver el catálogo público de animales.</p>
                <a href="../adopciones_de_animales/adopciones_de_animales.php" class="btn-action-primary" style="display: inline-block; padding: 12px 25px; text-decoration: none; background-color: #27ae60; color: white; border-radius: 10px;">
                    🐾 Acceder a la página web de adopciones de animales
                </a>
            </div>
        <?php else: ?>
            <div style="background: #ffffff; border: 1px solid #edf2f7; padding: 35px; border-radius: 16px; text-align: left; box-shadow: 0 4px 15px rgba(0,0,0,0.01);">
                <h4 style="margin: 0 0 12px 0; color: #2c3e50; font-size: 17px; font-weight: 600;">📋 ¿Qué ocurre al enviarlo?</h4>
                <p style="font-size: 13.5px; color: #6c757d; line-height: 1.6; margin: 0 0 20px 0;">
                   Cuando completes el formulario, guardaremos tus datos y no tendrás que volver a introducirlos.
                   Después serás redirigido a la página web de adopciones para continuar con el proceso.
                </p>

                <hr style="border: 0; border-top: 1px solid #e0e0e0; margin: 25px 0;">

                <h4 style="margin: 0 0 12px 0; color: #2c3e50; font-size: 17px; font-weight: 600;">📝 Rellena tus datos aquí:</h4><br>
                <div class="fondo-formulario">
                   <?php include '../formulario_adopciones_de_animales.php'; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div id="formularios" class="contenido-pestana">
        <div class="section-header">
            <h2>Mis Formularios</h2>
            <p>Aquí se guardan las copias de los documentos que vayas tramitando en la plataforma. <strong>Puedes imprimir o guardar tus formularios en formato PDF pulsando el botón correspondiente.</strong></p>
        </div>

        <?php if ($ya_ha_rellenado_adopciones || $ya_ha_rellenado_criadores): ?>
            <div style="text-align: right; margin-bottom: 20px; padding-right: 10px;">
                <button onclick="window.print()" class="btn-action-primary" style="padding: 10px 20px; font-size: 13px; text-decoration: none; cursor: pointer; display: inline-block;">🖨️ Imprimir todo en PDF</button>
            </div>
        <?php endif; ?>
        
        <?php if (!$ya_ha_rellenado_adopciones && !$ya_ha_rellenado_criadores): ?>
            <div class="status-box-empty">
                <span class="icon">📂</span>
                <h4>Todavía no hay ningún formulario rellenado.</h4>
                <p>Cuando completes el formulario por primera vez en la sección "Adopciones", se guardará aquí automáticamente para que puedas consultarlo o descargarlo en PDF cuando lo necesites.</p>
            </div>
        <?php else: ?>

            <?php if ($ya_ha_rellenado_adopciones && $fila_adopcion): ?>
                <div class="contenedor">
                    <div class="caja-formulario">
                        <h2 style="color: #9b59b6; margin-bottom: 15px;">Formulario de compromiso (Adopciones)</h2>
                        <p class="intro-principal">Para garantizar una adopción segura y responsable, te pedimos que completes este breve compromiso.</p>

                        <div class="bloque">
                            <h3>Datos del interesado</h3>
                            <label>Nombre</label><input type="text" class="input-historial-solo-lectura" value="<?php echo htmlspecialchars($fila_adopcion['nombre'] ?? ''); ?>" disabled>
                            <label>Apellidos</label><input type="text" class="input-historial-solo-lectura" value="<?php echo htmlspecialchars($fila_adopcion['apellidos'] ?? ''); ?>" disabled>
                            <label>Teléfono</label><input type="tel" class="input-historial-solo-lectura" value="<?php echo htmlspecialchars($fila_adopcion['telefono'] ?? ''); ?>" disabled>
                            <label>Correo electrónico</label><input type="email" class="input-historial-solo-lectura" value="<?php echo htmlspecialchars($fila_adopcion['email'] ?? ''); ?>" disabled>
                            <label>Dirección completa</label><input type="text" class="input-historial-solo-lectura" value="<?php echo htmlspecialchars($fila_adopcion['direccion'] ?? ''); ?>" disabled>
                            <label>¿Qué tipo de animal quieres adoptar?</label><input type="text" class="input-historial-solo-lectura" value="<?php echo htmlspecialchars($fila_adopcion['tipo_animal'] ?? ''); ?>" disabled>
                        </div>

                        <div class="bloque">
                            <h3>Requisitos aceptados</h3>
                            <div class="contenedor-checkbox" style="margin-bottom: 10px;">
                                <input type="checkbox" checked disabled class="checkbox-morado-historial">
                                <label>Me comprometo a garantizar su bienestar, cubriendo sus necesidades de alimentación, salud y revisiones veterinarias.</label>
                            </div>
                            <div class="contenedor-checkbox" style="margin-bottom: 10px;">
                                <input type="checkbox" checked disabled class="checkbox-morado-historial">
                                <label>Dispondrá de un espacio adecuado y adaptado a su tamaño, así como del tiempo de atención, juego o paseos diarios que requiera su especie.</label>
                            </div>
                            <div class="contenedor-checkbox" style="margin-bottom: 10px;">
                                <input type="checkbox" checked disabled class="checkbox-morado-historial">
                                <label>El animal convivirá con el dueño y las personas que estén viviendo con él/ella como un miembro más de la familia y bajo ningún concepto lo utilizaré para la cría o comercio.</label>
                            </div>
                            <div class="contenedor-checkbox" style="margin-bottom: 10px;">
                                <input type="checkbox" checked disabled class="checkbox-morado-historial">
                                <label>Si por causas mayores no pudiera seguir haciéndome cargo de él, contactaré con Find a Pet o un refugio autorizado para buscarle un hogar responsable.</label>
                            </div>
                            <div class="contenedor-checkbox" style="margin-top: 15px; padding-top: 10px; border-top: 1px dashed #ddd;">
                                <input type="checkbox" checked disabled class="checkbox-morado-historial">
                                <label style="font-weight: 600; color: #2c3e50;">Confirmo que he leído y acepto todos los requisitos del formulario de compromiso.</label>
                            </div>
                        </div>

                        <div class="bloque">
                            <h3>Donación voluntaria</h3>
                            <p style="font-size: 13.5px; color: #7f8c8d; line-height: 1.5; margin-bottom: 10px;">Apartado para colaboraciones voluntarias destinadas al mantenimiento de la página web y ayuda a refugios de animales.</p>
                            <span style="display: block; font-size: 13px; color: #2c3e50; margin-bottom: 4px;">Mantenimiento de la página web: 600 189 240</span>
                            <span style="display: block; font-size: 13px; color: #2c3e50;">Refugios de animales: 600 378 446</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($ya_ha_rellenado_adopciones && $ya_ha_rellenado_criadores): ?>
                <div class="separador-historial"></div>
            <?php endif; ?>

            <?php if ($ya_ha_rellenado_criadores && $fila_criador): ?>
                <div class="contenedor">
                    <div class="caja-formulario">
                        <h2>Formulario de compromiso (Criadores)</h2>

                        <p class="intro-principal">
                            Para garantizar una adopción segura y responsable, te pedimos que completes este breve compromiso. Tu futuro compañero te está esperando.
                        </p>
                        
                        <form action="#" method="POST">
                            <div class="bloque">
                                <h3>Datos del interesado</h3>
                                
                                <div class="celda-input">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" id="nombre" name="nombre" class="input-historial-solo-lectura" value="<?php echo htmlspecialchars($fila_criador['nombre'] ?? ''); ?>" disabled>
                                </div>

                                <div class="celda-input">
                                    <label for="apellidos">Apellidos</label>
                                    <input type="text" id="apellidos" name="apellidos" class="input-historial-solo-lectura" value="<?php echo htmlspecialchars($fila_criador['apellidos'] ?? ''); ?>" disabled>
                                </div>

                                <div class="celda-input">
                                    <label for="telefono">Teléfono</label>
                                    <input type="tel" id="telefono" name="telefono" class="input-historial-solo-lectura" value="<?php echo htmlspecialchars($fila_criador['telefono'] ?? ''); ?>" disabled>
                                </div>

                                <div class="celda-input">
                                    <label for="email">Correo electrónico</label>
                                    <input type="email" id="email" name="email" class="input-historial-solo-lectura" value="<?php echo htmlspecialchars($fila_criador['email'] ?? ''); ?>" disabled>
                                </div>

                                <div class="celda-input">
                                    <label for="direccion">Dirección completa</label>
                                    <input type="text" id="direccion" name="direccion" class="input-historial-solo-lectura" value="<?php echo htmlspecialchars($fila_criador['direccion'] ?? ''); ?>" disabled>
                                </div>
                                
                                <div class="celda-input">
                                    <label for="tipo_animal">¿Qué tipo de animal quieres adoptar?</label>
                                    <input type="text" id="tipo_animal" name="tipo_animal" class="input-historial-solo-lectura" value="<?php echo htmlspecialchars($fila_criador['tipo_animal'] ?? ''); ?>" disabled>
                                </div>
                            </div>

                            <div class="bloque">
                                <h3>Aceptación de requisitos</h3>
                                <p class="intro-requisitos">
                                En Find a Pet nos tomamos muy en serio el bienestar y el futuro de los animales. Por favor, confirma que estás de acuerdo con estos compromisos básicos para asegurar que tendrá una vida estupenda a tu lado:
                                </p>
                                <div class="contenedor-checkbox">
                                    <input type="checkbox" id="req1" name="req1" checked disabled>
                                    <label for="req1">
                                        Me comprometo a garantizar su bienestar, cubriendo sus necesidades de alimentación, salud y revisiones veterinarias.
                                    </label>
                                </div>
                                <div class="contenedor-checkbox">
                                    <input type="checkbox" id="req2" name="req2" checked disabled>
                                    <label for="req2">Dispondrá de un espacio adecuado y adaptado a su tamaño, así como del tiempo de atención, juego o paseos diarios que requiera su especie.</label>
                                </div>
                                <div class="contenedor-checkbox">
                                    <input type="checkbox" id="req3" name="req3" checked disabled>
                                    <label for="req3">El animal convivirá con el dueño y las personas que estén viviendo con él/ella como un miembro más de la familia y bajo ningún concepto lo utilizaré para la cría o comercio.</label>
                                </div>
                                <div class="contenedor-checkbox">
                                    <input type="checkbox" id="req4" name="req4" checked disabled>
                                    <label for="req4">Si por causas mayores no pudiera seguir haciéndome cargo de él, contactaré con Find a Pet o un refugio autorizado para buscarle un hogar responsable.</label>
                                </div>
                            </div>

                            <div class="bloque">
                                <div class="contenedor-checkbox">
                                    <input type="checkbox" id="acuerdo_final" name="acuerdo_final" checked disabled>
                                    <label for="acuerdo_final">Confirmo que he leído y acepto todos los requisitos del formulario de compromiso.</label>
                                </div>

                                <div class="caja-donacion">
                                    <h3>Donación voluntaria</h3>
                                    <p>Apartado para colaboraciones voluntarias destinadas al mantenimiento de la página web y ayuda a refugios de animales.</p><br>
                                    <span class="telefono-donacion" style="display:block; margin-bottom:4px;">Mantenimiento de la página web: 600 189 240</span>
                                    <span class="telefono-donacion">Refugios de animales: 600 378 446</span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>

    <div id="cartilla" class="contenido-pestana">
        <div class="section-header">
            <h2>Cartilla Veterinaria</h2>
            <p>Información sobre la salud y revisiones de tu mascota:</p>
        </div>
        
        <div class="status-box-empty">
            <span class="icon">🐾🩺</span>
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
                <div style="width: 80px; height: 80px; background: #f5edfa; border: 2px dashed #9b59b6; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; color: #000000; font-weight: 600; text-align: center; padding: 5px; box-sizing: border-box;">Foto de perfil:</div>
                <div>
                    <h3 style="margin: 0 0 4px 0; color: #2c3e50; font-size: 16px;">Usuario: <?php echo htmlspecialchars($nombreUsuario); ?></h3>
                    <p style="margin: 0; font-size: 12px; color: #7f8c8d;">Usuario de la comunidad</p>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <a href="editar_perfil.php" class="btn-action-primary" style="padding: 10px 20px; font-size: 13px; text-decoration: none; display: inline-block;">⚙️ Editar mis datos</a>
                <a href="logout.php" class="btn-logout-inside" style="padding: 10px 20px; font-size: 13px; text-decoration: none; display: inline-block;">Cerrar Sesión ✕</a>
                <a href="dar_baja.php" class="btn-delete-inside" style="padding: 10px 20px; font-size: 13px; text-decoration: none; display: inline-block;" onclick="return confirm('¿Seguro que quieres borrar tu cuenta? Perderás todo lo que tienes guardado.');">Eliminar cuenta</a>
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