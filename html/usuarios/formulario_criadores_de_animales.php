<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de compromiso</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body class="fondo-formulario">

    <div class="contenedor">
        <div class="caja-formulario">
            <h2>Formulario de compromiso</h2>

            <p class="intro-principal">
                Para garantizar una adopción segura y responsable, te pedimos que completes este breve compromiso. Tu futuro compañero te está esperando.
            </p>
            
            <?php 
            // Guardo la variable por si existe en la URL para usarla abajo
            $e = isset($_GET['e']) ? $_GET['e'] : '';

            // Solo muestro el bloque de arriba si falta algún campo obligatorio por rellenar (Error 1)
            if ($e !== '' && strpos($e, "1") !== false) {
                echo "<div style='background-color: #f8d7da; color: #252525; padding: 15px; border: 1px solid #f7d6d9; border-radius: 5px; margin-bottom: 20px; font-family: sans-serif; text-align: center;'>";
                echo "<strong>⚠️ Por favor, revisa lo siguiente:</strong><br>";
                echo "• Te faltan huecos por rellenar.<br>";
                echo "</div>";
            }
            ?>

            <?php
            $url_validacion = "validacion_formulario_criadores.php";
            if (strpos($_SERVER['REQUEST_URI'], '/usuarios/') !== false) {
                $url_validacion = "validacion_formulario_criadores.php";
            }
            ?>
            
            <form action="<?php echo htmlspecialchars($url_validacion, ENT_QUOTES, 'UTF-8'); ?>" method="POST">

                <div class="bloque">
                    <h3>Datos del interesado</h3>
                    
                    <div class="celda-input">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo isset($_GET['nom']) ? htmlspecialchars($_GET['nom'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
                    </div>

                    <div class="celda-input">
                        <label for="apellidos">Apellidos</label>
                        <input type="text" id="apellidos" name="apellidos" value="<?php echo isset($_GET['ape']) ? htmlspecialchars($_GET['ape'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
                    </div>

                    <div class="celda-input">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" value="<?php echo isset($_GET['tel']) ? htmlspecialchars($_GET['tel'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
                        <?php if (strpos($e, "2") !== false): ?>
                            <span class="error-inline">• Debe contener 9 números.</span>
                        <?php endif; ?>
                    </div>

                    <div class="celda-input">
                        <label for="email">Correo electrónico</label>
                        <input type="email" id="email" name="email" value="<?php echo isset($_GET['ema']) ? htmlspecialchars($_GET['ema'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
                        <?php if (strpos($e, "4") !== false): ?>
                            <span class="error-inline">• Debe estar completo.</span>
                        <?php endif; ?>
                    </div>

                    <div class="celda-input">
                        <label for="direccion">Dirección completa</label>
                        <input type="text" id="direccion" name="direccion" value="<?php echo isset($_GET['dir']) ? htmlspecialchars($_GET['dir'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="Calle, número, piso..." required>
                    </div>
                    
                    <div class="celda-input">
                        <label for="tipo_animal">¿Qué tipo de animal quieres adoptar?</label>
                        <input type="text" id="tipo_animal" name="tipo_animal" value="<?php echo isset($_GET['ani']) ? htmlspecialchars($_GET['ani'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="Ej: Perro o gato" required>
                        <?php if (strpos($e, "3") !== false): ?>
                            <span class="error-inline">• Solo perros o gatos.</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bloque">
                    <h3>Aceptación de requisitos</h3>
                    <p class="intro-requisitos">
                       En Find a Pet nos tomamos muy en serio el bienestar y el futuro de los animales. Por favor, confirma que estás de acuerdo con estos compromisos básicos para asegurar que tendrá una vida estupenda a tu lado:
                    </p>
                    <div class="contenedor-checkbox">
                        <input type="checkbox" id="req1" name="req1" required>
                        <label for="req1">Me comprometo a garantizar su bienestar, cubriendo sus necesidades de alimentación, salud y revisiones veterinarias.</label>
                    </div>
                    <div class="contenedor-checkbox">
                        <input type="checkbox" id="req2" name="req2" required>
                        <label for="req2">Dispondrá de un espacio adecuado y adaptado a su tamaño, así como del tiempo de atención, juego o paseos diarios que requiera su especie.</label>
                    </div>
                    <div class="contenedor-checkbox">
                        <input type="checkbox" id="req3" name="req3" required>
                        <label for="req3">El animal convivirá con el dueño y las personas que estén viviendo con él/ella como un miembro más de la familia y bajo ningún concepto lo utilizaré para la cría o comercio.</label>
                    </div>
                    <div class="contenedor-checkbox">
                        <input type="checkbox" id="req4" name="req4" required>
                        <label for="req4">Si por causas mayores no pudiera seguir haciéndome cargo de él, contactaré con Find a Pet o un refugio autorizado para buscarle un hogar responsable.</label>
                    </div>
                </div>

                <div class="bloque">
                    <div class="contenedor-checkbox">
                        <input type="checkbox" id="acuerdo_final" name="acuerdo_final" required>
                        <label for="acuerdo_final">Confirmo que he leído y acepto todos los requisitos del formulario de compromiso.</label>
                    </div>

                    <div class="caja-donacion">
                        <h3>Donación voluntaria</h3>
                        <p>Apartado para colaboraciones voluntarias destinadas al mantenimiento de la página web y ayuda a refugios de animales.</p><br>
                        <span class="telefono-donacion">Mantenimiento de la página web: 600 189 240</span>
                        <span class="telefono-donacion">Refugios de animales: 600 378 446</span>
                    </div>
                    <button type="submit" class="boton">Aceptar y continuar</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.pathname);
        }
    </script>
</body>
</html>