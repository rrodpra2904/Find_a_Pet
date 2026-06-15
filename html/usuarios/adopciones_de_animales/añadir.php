<?php 

// 1. Control de acceso

// Incluyo el archivo de seguridad para que nadie pueda entrar a esta página web sin estar logueado.
include 'seguridad.php'; 

// Incluyo el archivo de conexión que contiene el objeto $db (PDO).
include 'conexion.php'; 

// --- Bloqueo de seguridad para empleados ---

// Verifico el rol del usuario: si es un empleado, lo redirijo fuera porque 
// esta página web de añadir animales solo está permitida para administradores.
if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'empleado') {
    header("Location: ../administrador/gestion_de_adopciones_de_animales.php");
    exit();
}

// 2. Preparación de variables

// Variables para los mensajes de error: las inicio vacías y se llenarán si falla una validación.
$error_nombre = "";
$error_raza = "";
$error_foto = "";
$error_edad = ""; 
$error_desc = ""; 
$error_especie = ""; 
$error_sexo = "";    

// Variables para conservar los datos: así, si hay un error, el usuario no pierde lo que ya escribió.
$nombre = "";
$raza = "";
$edad = "";
$descripcion = "";
$especie = ""; 
$sexo = "";    

// NUEVAS VARIABLES: Conservación de datos para la Cartilla Veterinaria
$num_chip = "";
$vacuna_rabia = "0";
$desparasitado = "0";
$vacuna_nombre = "";
$vacuna_fecha = "";
$vacuna_lote = "";
$observaciones_medicas = "";

// 3. Procesamiento del formulario

// Solo ejecuto este bloque si el usuario ha pulsado el botón de enviar (método POST).
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Recojo los datos y uso trim() para limpiar espacios accidentales al principio o al final.
    $nombre      = trim($_POST['nombre_animal']);
    $raza        = trim($_POST['raza_animal']);
    $edad        = trim($_POST['edad_animal']); 
    $descripcion = trim($_POST['desc_animal']); 
    $especie     = isset($_POST['especie_animal']) ? trim($_POST['especie_animal']) : ""; 
    $sexo        = isset($_POST['sexo_animal']) ? trim($_POST['sexo_animal']) : "";       
    
    // NUEVAS VARIABLES: Recogida de datos sanitarios de la cartilla
    $num_chip              = trim($_POST['num_chip']);
    $vacuna_rabia          = isset($_POST['vacuna_rabia']) ? $_POST['vacuna_rabia'] : "0";
    $desparasitado         = isset($_POST['desparasitado']) ? $_POST['desparasitado'] : "0";
    $vacuna_nombre         = trim($_POST['vacuna_nombre']);
    $vacuna_fecha          = !empty($_POST['vacuna_fecha']) ? $_POST['vacuna_fecha'] : null;
    $vacuna_lote           = trim($_POST['vacuna_lote']);
    $observaciones_medicas = trim($_POST['observaciones_medicas']);

    // Esta variable me indica si el formulario es válido para guardarse.
    $todo_ok = true;

    // --- 4. Validaciones de seguridad ---
    if ($nombre == "") {
        $error_nombre = "El nombre del animal es obligatorio.";
        $todo_ok = false;
    }

    if ($raza == "") {
        $error_raza = "Debes indicar la raza.";
        $todo_ok = false;
    }

    if ($edad == "") {
        $error_edad = "La edad es obligatoria.";
        $todo_ok = false;
    } elseif (!is_numeric($edad) || $edad < 0) {
        $error_edad = "La edad debe ser un número válido.";
        $todo_ok = false;
    }

    if ($especie == "") {
        $error_especie = "Debes seleccionar una especie para los filtros.";
        $todo_ok = false;
    }

    if ($sexo == "") {
        $error_sexo = "Debes seleccionar el sexo para los filtros.";
        $todo_ok = false;
    }

    if ($descripcion == "") {
        $error_desc = "La descripción no puede estar vacía.";
        $todo_ok = false;
    }

    if (empty($_FILES['foto_animal']['name'])) {
        $error_foto = "Tienes que subir una foto del animal.";
        $todo_ok = false;
    }

    // 5. Guardado de datos (Si todo está correcto)
    if ($todo_ok) {
        
        $nombre_original_foto = $_FILES['foto_animal']['name'];
        $temp = $_FILES['foto_animal']['tmp_name'];
        $carpeta = "../imagenes/adopciones_de_animales/";
        
        $extension_foto = pathinfo($nombre_original_foto, PATHINFO_EXTENSION);
        $foto = "animal_admin_" . time() . "_" . uniqid() . "." . $extension_foto;
        
        if (move_uploaded_file($temp, $carpeta . $foto)) {
            
            try {
                // Iniciamos una transacción para asegurar que se guarde en todas las tablas o en ninguna
                $db->beginTransaction();

                // 5.1 INSERTAR EN LA TABLA PRINCIPAL 'ANIMALES'
                $sql = "INSERT INTO animales (nombre, raza, edad, descripcion, imagen, adoptado) 
                        VALUES (:nombre, :raza, :edad, :descripcion, :imagen, 'No')";
                
                $sentencia = $db->prepare($sql);
                $sentencia->bindParam(':nombre', $nombre);
                $sentencia->bindParam(':raza', $raza);
                $sentencia->bindParam(':edad', $edad);
                $sentencia->bindParam(':descripcion', $descripcion);
                $sentencia->bindParam(':imagen', $foto);
                $sentencia->execute();

                // Recuperamos el ID autogenerado del animal que acabamos de meter
                $nuevo_id_animal = $db->lastInsertId();

                // 5.2 INSERTAR EN LA TABLA AUXILIAR 'FILTRO_ANIMALES'
                $sqlFiltro = "INSERT INTO filtro_animales (id_animal, especie) VALUES (:id_animal, :especie)";
                $sentenciaFiltro = $db->prepare($sqlFiltro);
                $especie_limpia = strtolower($especie);
                $sentenciaFiltro->bindParam(':id_animal', $nuevo_id_animal);
                $sentenciaFiltro->bindParam(':especie', $especie_limpia);
                $sentenciaFiltro->execute();

                // 5.3 INSERTAR EN LA TABLA AUXILIAR 'FILTRO_SEXO_ANIMALES'
                $sqlSexo = "INSERT INTO filtro_sexo_animales (id_animal, sexo) VALUES (:id_animal, :sexo)";
                $sentenciaSexo = $db->prepare($sqlSexo);
                $sexo_limpio = strtolower($sexo); 
                $sentenciaSexo->bindParam(':id_animal', $nuevo_id_animal);
                $sentenciaSexo->bindParam(':sexo', $sexo_limpio);
                $sentenciaSexo->execute();

                // 5.4 INTEGRACIÓN CLAVE: INSERTAR EN 'TABLON_DE_ADOPCIONES' CON SU CARTILLA VETERINARIA COMPLETA
                // Forzamos campos por defecto como 'administrador' y verificado '1' según se muestra en tu estructura
                $usuario_correo_defecto = "administrador";
                $verificado_defecto = "1";
                
                $sqlTablon = "INSERT INTO tablon_de_adopciones 
                              (id, nombre, especie, raza, descripcion, edad, sexo, usuario_email, verificado, foto, num_chip, vacuna_rabia, desparasitado, vacuna_nombre, vacuna_fecha, vacuna_lote, observaciones_medicas, fecha_publicacion) 
                              VALUES 
                              (:id, :nombre, :especie, :raza, :descripcion, :edad, :sexo, :usuario_email, :verificado, :foto, :num_chip, :vacuna_rabia, :desparasitado, :vacuna_nombre, :vacuna_fecha, :vacuna_lote, :observaciones_medicas, NOW())";
                
                $sentenciaTablon = $db->prepare($sqlTablon);
                
                // Vinculamos todas las columnas sanitarias rellenadas por el Administrador
                $sentenciaTablon->bindParam(':id', $nuevo_id_animal); // Forzamos el mismo ID para sincronizar las tablas
                $sentenciaTablon->bindParam(':nombre', $nombre);
                $sentenciaTablon->bindParam(':especie', $especie);
                $sentenciaTablon->bindParam(':raza', $raza);
                $sentenciaTablon->bindParam(':descripcion', $descripcion);
                $sentenciaTablon->bindParam(':edad', $edad);
                $sentenciaTablon->bindParam(':sexo', $sexo);
                $sentenciaTablon->bindParam(':usuario_email', $usuario_correo_defecto);
                $sentenciaTablon->bindParam(':verificado', $verificado_defecto);
                $sentenciaTablon->bindParam(':foto', $foto);
                
                // Vinculación de la cartilla veterinaria oficial
                $sentenciaTablon->bindParam(':num_chip', $num_chip);
                $sentenciaTablon->bindParam(':vacuna_rabia', $vacuna_rabia);
                $sentenciaTablon->bindParam(':desparasitado', $desparasitado);
                $sentenciaTablon->bindParam(':vacuna_nombre', $vacuna_nombre);
                $sentenciaTablon->bindParam(':vacuna_fecha', $vacuna_fecha);
                $sentenciaTablon->bindParam(':vacuna_lote', $vacuna_lote);
                $sentenciaTablon->bindParam(':observaciones_medicas', $observaciones_medicas);
                
                $sentenciaTablon->execute();

                // Confirmamos la transacción limpia
                $db->commit();

                // Si tiene éxito, vuelvo a la pantalla de gestión de adopciones de animales.
                header("Location: ../administrador/gestion_de_adopciones_de_animales.php");
                exit(); 

            } catch (PDOException $e) {
                // Si algo falla, cancelamos todo el proceso para evitar descuadres en la base de datos
                $db->rollBack();
                echo "Lo siento, no se ha podido guardar el registro en este momento: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Animales y Cartilla</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles_añadir_adopciones.css">
    
    <style>
        /* --- ENFOQUE MORADO REFINADO PARA TODOS LOS CAMPOS DE LA CAJA --- */
        * {
            font-family: 'Poppins', sans-serif !important;
        }

        .caja-formulario input, .caja-formulario select, .caja-formulario textarea {
            outline: none !important;
            border: 2px solid #ccc !important;
            border-radius: 6px !important;
            transition: all 0.2s ease !important;
        }

        .caja-formulario input:focus, .caja-formulario select:focus, .caja-formulario textarea:focus {
            border-color: #8a2be2 !important;
            box-shadow: 0 0 8px rgba(138, 43, 226, 0.25) !important;
        }

        .caja-formulario select {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            margin-bottom: 12px;
            background-color: #fff;
            box-sizing: border-box;
        }

        /* Contenedor especial para los elementos de la cartilla veterinaria */
        .bloque-cartilla-medica {
            background-color: #fbf9ff;
            border: 1px dashed #b19ffb;
            border-radius: 8px;
            padding: 20px;
            margin-top: 25px;
        }

        .bloque-cartilla-medica h3 {
            color: #252525;
            border-bottom: 2px solid #e1bee7;
            padding-bottom: 8px;
            margin-top: 0;
        }

        .flex-check {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            cursor: pointer;
        }

        .flex-check input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #8a2be2;
        }
    </style>
</head>
<body class="fondo-formulario">

    <div id="google_translate_element"></div>

    <div class="contenedor">
        <div class="caja-formulario">
            <h2>Añadir animales</h2>
            
            <p style="color: #556375; font-size: 14.5px; line-height: 1.6; margin-bottom: 25px; margin-top: 5px;">
               Por favor, cumplimenta todos los apartados de la ficha técnica. Este asistente registrará la información de la mascota en el sistema central y generará automáticamente su <b>Cartilla Veterinaria</b> en el tablón público para tramitar futuras adopciones.
            </p>
            
            <form action="" method="POST" enctype="multipart/form-data" class="notranslate">
                
                <div class="bloque">
                    <h3>Información del Animal</h3>
                    
                    <label>Nombre del animal:</label>
                    <input type="text" name="nombre_animal" value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php if ($error_nombre != "") { echo "<span class='msj-error'>" . htmlspecialchars($error_nombre, ENT_QUOTES, 'UTF-8') . "</span>"; } ?>
                    
                    <label>Especie (Para el Buscador):</label>
                    <select name="especie_animal">
                        <option value="">-- Selecciona Especie --</option>
                        <option value="Perro" <?php if($especie === "Perro") echo "selected"; ?>>Perro</option>
                        <option value="Gato" <?php if($especie === "Gato") echo "selected"; ?>>Gato</option>
                    </select>
                    <?php if ($error_especie != "") { echo "<span class='msj-error'>" . htmlspecialchars($error_especie, ENT_QUOTES, 'UTF-8') . "</span>"; } ?>

                    <label>Sexo (Para el Buscador):</label>
                    <select name="sexo_animal">
                        <option value="">-- Selecciona Sexo --</option>
                        <option value="Macho" <?php if($sexo === "Macho") echo "selected"; ?>>Macho</option>
                        <option value="Hembra" <?php if($sexo === "Hembra") echo "selected"; ?>>Hembra</option>
                    </select>
                    <?php if ($error_sexo != "") { echo "<span class='msj-error'>" . htmlspecialchars($error_sexo, ENT_QUOTES, 'UTF-8') . "</span>"; } ?>

                    <label>Raza:</label>
                    <input type="text" name="raza_animal" value="<?php echo htmlspecialchars($raza, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php if ($error_raza != "") { echo "<span class='msj-error'>" . htmlspecialchars($error_raza, ENT_QUOTES, 'UTF-8') . "</span>"; } ?>
                    
                    <label>Edad:</label>
                    <input type="text" name="edad_animal" value="<?php echo htmlspecialchars($edad, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php if ($error_edad != "") { echo "<span class='msj-error'>" . htmlspecialchars($error_edad, ENT_QUOTES, 'UTF-8') . "</span>"; } ?>
                </div>

                <div class="bloque bloque-cartilla-medica">
                    <h3>📋 Datos de la cartilla veterinaria</h3>
                    
                    <label>Número de Identificación Microchip:</label>
                    <input type="text" name="num_chip" placeholder="Ej: 123456789098765" value="<?php echo htmlspecialchars($num_chip, ENT_QUOTES, 'UTF-8'); ?>" maxlength="15">
                    
                    <div style="margin-top: 15px; margin-bottom: 15px;">
                        <label class="flex-check">
                            <input type="checkbox" name="vacuna_rabia" value="1" <?php if($vacuna_rabia == "1") echo "checked"; ?>>
                            <span>¿Vacuna de la Rabia al día? (Marcar si está vacunado)</span>
                        </label>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label class="flex-check">
                            <input type="checkbox" name="desparasitado" value="1" <?php if($desparasitado == "1") echo "checked"; ?>>
                            <span>¿Desparasitado correctamente (Interna/Externa)?</span>
                        </label>
                    </div>

                    <label>Otras vacunas administradas (Nombre clínico):</label>
                    <input type="text" name="vacuna_nombre" placeholder="Ej: Heptavalente, Pentavalente felina..." value="<?php echo htmlspecialchars($vacuna_nombre, ENT_QUOTES, 'UTF-8'); ?>">

                    <label>Fecha de la última vacunación:</label>
                    <input type="date" name="vacuna_fecha" style="width:100%; padding:10px; margin-bottom:12px; box-sizing:border-box;" value="<?php echo htmlspecialchars($vacuna_fecha ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                    <label>Lote de la Vacuna:</label>
                    <input type="text" name="vacuna_lote" placeholder="Ej: Lote A123X" value="<?php echo htmlspecialchars($vacuna_lote, ENT_QUOTES, 'UTF-8'); ?>">

                    <label>Observaciones del Veterinario / Historial clínico inicial:</label>
                    <textarea name="observaciones_medicas" rows="3" placeholder="Añade detalles sobre el estado de salud, alergias o tratamientos actuales..."><?php echo htmlspecialchars($observaciones_medicas, ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>

                <div class="bloque" style="margin-top: 20px;">
                    <h3>Descripción y foto</h3>
                    
                    <label>Descripción general para el público:</label>
                    <textarea name="desc_animal" rows="4"><?php echo htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <?php if ($error_desc != "") { echo "<span class='msj-error'>" . htmlspecialchars($error_desc, ENT_QUOTES, 'UTF-8') . "</span>"; } ?>
                    
                    <label style="margin-top: 20px;">Subir Fotografía:</label>
                    <input type="file" name="foto_animal">
                    <?php if ($error_foto != "") { echo "<span class='msj-error'>" . htmlspecialchars($error_foto, ENT_QUOTES, 'UTF-8') . "</span>"; } ?>
                </div>

                <button type="submit" class="boton">Guardar Animal</button>
                
                <a href="../administrador/gestion_de_adopciones_de_animales.php" class="volver">
                    Cancelar y volver a la lista de adopciones
                </a>
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