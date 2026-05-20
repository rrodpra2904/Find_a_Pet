<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario Adopción - Find a Pet</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body class="fondo-formulario">

    <div class="contenedor">
        <div class="caja-formulario">
            <h2>Formulario de compromiso</h2>

            <p class="texto-secundario">
                Rellena este formulario para confirmar que te harás cargo del animal de forma responsable.
            </p>
            <!-- Esto lo pongo para que cuando el usuario se equivoque al meter los datos en los campos de teléfono,
             tipo de animal que van adoptar y si deja espacios vacios en los campos dandole a la tecla de espacio
             saldrá también un mensaje de error ariba del todo de la página web.-->
            <?php 
            if (isset($_GET['e'])) {
                $e = $_GET['e'];
                echo "<div style='background-color: #f8d7da; color: #252525; padding: 15px; border: 1px solid #f7d6d9; border-radius: 5px; margin-bottom: 20px; font-family: sans-serif; text-align: center;'>";
                echo "<strong>⚠️ Por favor, revisa lo siguiente:</strong><br>";
                
                // Si el número 1 está en la URL, muestro ese error.
                if (strpos($e, "1") !== false) { echo "• Te faltan huecos por rellenar.<br>"; }
                // Si el número 2 está, muestro el del teléfono.
                if (strpos($e, "2") !== false) { echo "• El teléfono debe tener entre 6 o 9 números.<br>"; }
                // Si el número 3 está, muestro el del animal.
                if (strpos($e, "3") !== false) { echo "• Solo puedes adoptar perro o gato.<br>"; }
                // Esto lo pongo para que si el correo no tiene el punto o el @ salga que el formato no vale y tiene que estar completo.
                if (strpos($e, "4") !== false) { echo "• El correo electrónico tiene que estar completo.<br>"; }
                
                echo "</div>";
            }
            ?>

            <!-- Aquí pongo el formulario de compromiso para las adopciones de animales, esto es obligatorio rellenarlo.-->
            
            <form action="validacion_formulario_criadores.php" method="POST">

                <div class="bloque">
                    <h3>Datos del interesado</h3>
                    
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo isset($_GET['nom']) ? $_GET['nom'] : ''; ?>" required>

                    <label for="apellidos">Apellidos</label>
                    <input type="text" id="apellidos" name="apellidos" value="<?php echo isset($_GET['ape']) ? $_GET['ape'] : ''; ?>" required>

                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" value="<?php echo isset($_GET['tel']) ? $_GET['tel'] : ''; ?>" required>

                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($_GET['ema']) ? $_GET['ema'] : ''; ?>" required>

                    <label for="direccion">Dirección completa</label>
                    <input type="text" id="direccion" name="direccion" value="<?php echo isset($_GET['dir']) ? $_GET['dir'] : ''; ?>" placeholder="Calle, número, piso..." required>
                    <!-- Esto lo pongo para que la persona escriba que animal va adoptar si va ser un perro o un gato. -->
                    <label for="tipo_animal">¿Qué tipo de animal quieres adoptar?</label>
                    <input type="text" id="tipo_animal" name="tipo_animal" value="<?php echo isset($_GET['ani']) ? $_GET['ani'] : ''; ?>" placeholder="Ej: Perro o gato" required>
                </div>
                <!-- Aquí pongo los requisito que tiene que aceptar la persona para que pueda adoptar un animal, 
                 esto es obligatorio. -->
                <div class="bloque">
                    <h3>Aceptación de requisitos</h3>
                    <div class="contenedor-checkbox">
                        <input type="checkbox" id="req1" name="req1" required>
                        <label for="req1">Me comprometo a cuidar del animal correctamente y llevarlo al veterinario si hace falta.</label>
                    </div>
                    <div class="contenedor-checkbox">
                        <input type="checkbox" id="req2" name="req2" required>
                        <label for="req2">Tendrá espacio adecuado y saldrá todos los días.</label>
                    </div>
                    <div class="contenedor-checkbox">
                        <input type="checkbox" id="req3" name="req3" required>
                        <label for="req3">No lo usaré para cría ilegal.</label>
                    </div>
                    <div class="contenedor-checkbox">
                        <input type="checkbox" id="req4" name="req4" required>
                        <label for="req4">Si no puedo hacerme cargo, buscaré un hogar responsable o lo llevaré a un refugio.</label>
                    </div>
                </div>
                <!-- Aquí pongo para que marque que ha leido y acepta todas las condiciones
                  y también ponga la información de donación voluntaria para que la persona sepa a que 
                  números de telefonos tiene que llamar. -->
                <div class="bloque">
                    <div class="contenedor-checkbox">
                        <input type="checkbox" id="acuerdo_final" name="acuerdo_final" required>
                        <label for="acuerdo_final">Confirmo que he leído y acepto todas las condiciones.</label>
                    </div>

                    <div class="caja-donacion">
                        <h3>Donación voluntaria</h3>
                        <p>Apartado para colaboraciones voluntarias destinadas al mantenimiento de la web y ayuda a refugios.</p>
                        <span class="telefono-donacion">Mantenimiento página web: 600 189 240</span>
                        <span class="telefono-donacion">Refugios de animales: 600 378 446</span>
                    </div>
                    <!-- Esto lo pongo para que la persona envie el formulario a la base de datos. -->
                    <button type="submit" class="boton">Aceptar y continuar</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Este script lo pongo para que el traductor de Google pueda traducir la página web de formulario de 
     compromiso para adopciones de animales en el idioma que se la ha indicado a la hora de marcarlo en el botón
     de idioma. -->
    <script>
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage: 'es',
            includedLanguages: 'en,fr,it,de,pt,es',
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
            autoDisplay: false
        }, 'google_translate_element');
    }
    </script>
    <!-- Este script hay que ponerlo si o si para que el traductor de Google pueda traducir la página web
     una vez puesto esto tiene que salir automáticamente la barra del traductor de Google arrba de la página web. -->
    
    <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
     <!-- Esto lo he puesto para que cuando una persona cargue la página web no le salga otra vez los mensajes de errores
      que le ha mostrado antes de recargar la página web de esta forma cuando se equivoque otra vez al introducir
      un dato en los campos de teléfono y tipo de animal si se equivoca en algunos de ellos le saldra el mensaje de
      error. -->
       
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.pathname);
        }
    </script>
</body>
</html>