<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario Adopción - Find a Pet</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>

<body class="fondo-formulario">

    <div class="contenedor">
        <div class="caja-formulario">
            <h2>Formulario de compromiso</h2>

            <p class="texto-secundario">
                Rellena este formulario para confirmar que te harás cargo del animal de forma responsable.
            </p>
            <?php 
            if (isset($_GET['e'])) {
                $e = $_GET['e'];
                echo "<div style='background-color: #f8d7da; color: #252525; padding: 15px; border: 1px solid #f7d6d9; border-radius: 5px; margin-bottom: 20px; font-family: sans-serif; text-align: center;'>";
                echo "<strong>⚠️ Por favor, revisa lo siguiente:</strong><br>";
                
                // Si el número 1 está en la URL, muestro ese error.
                if (strpos($e, "1") !== false) { echo "• Te faltan huecos por rellenar.<br>"; }
                // Si el número 2 está, muestro el mensaje de error del campo de teléfono.
                if (strpos($e, "2") !== false) { echo "• El teléfono debe tener entre 6 o 9 números.<br>"; }
                // Si el número 3 está, muestro el mensaje de error del campo de tipo de animal.
                if (strpos($e, "3") !== false) { echo "• Solo puedes adoptar perro o gato.<br>"; }
                // Esto lo pongo para que si el correo no tiene el punto o el @ salga que el formato no vale y tiene que estar completo.
                if (strpos($e, "4") !== false) { echo "• El correo electrónico tiene que estar completo.<br>"; }
                
                echo "</div>";
            }
            ?>

            <?php
            $url_validacion = "validar_formulario_adopcion.php";
            if (strpos($_SERVER['REQUEST_URI'], '/usuarios/') !== false) {
                $url_validacion = "../validar_formulario_adopcion.php";
            }
            ?>
            
            <form action="<?php echo $url_validacion; ?>" method="POST">

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
                    <label for="tipo_animal">¿Qué tipo de animal quieres adoptar?</label>
                    <input type="text" id="tipo_animal" name="tipo_animal" value="<?php echo isset($_GET['ani']) ? $_GET['ani'] : ''; ?>" placeholder="Ej: Perro o gato" required>
                </div>
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
                <div class="bloque">
                    <div class="contenedor-checkbox">
                        <input type="checkbox" id="acuerdo_final" name="acuerdo_final" required>
                        <label for="acuerdo_final">Confirmo que he leído y acepto todas las condiciones.</label>
                    </div>

                    <div class="caja-donacion">
                        <h3>Donación voluntaria</h3>
                        <p>Apartado para colaboraciones voluntarias destinadas al mantenimiento de la web y ayuda a refugios.</p><br>
                        <span class="telefono-donacion">Mantenimiento página web: 600 189 240</span>
                        <span class="telefono-donacion">Refugios de animales: 600 378 446</span>
                    </div>
                    <button type="submit" class="boton">Aceptar y continuar</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>