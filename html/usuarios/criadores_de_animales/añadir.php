<?php 
// Incluyo el archivo de seguridad para verificar que el usuario tenga permiso (sesión activa).
include 'seguridad.php'; 
?>
<?php
// Incluyo la conexión a la base de datos (usando el objeto $db de PDO).
include 'conexion.php'; 

// --- BLOQUEO DE SEGURIDAD PARA EMPLEADOS ---
// Si el usuario tiene el rol de empleado, lo redirijo fuera porque no tiene permisos de escritura/creación.
if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'empleado') {
    header("Location: ../administrador/gestion_de_criadores_de_animales.php");
    exit();
}

/* 1. VARIABLES PARA MENSAJES DE ERROR:
   Las preparo vacías para llenarlas solo si el usuario comete un fallo al rellenar el formulario. */
$error_nombre = "";
$error_localidad = "";
$error_telefono = "";
$error_foto = "";
$error_desc = ""; 

/* 2. VARIABLES PARA CONSERVAR LOS DATOS ESCRITOS:
   Esto sirve para que, si hay un error de validación, los campos no aparezcan vacíos de nuevo (Persistencia). */
$nombre = "";
$localidad = "";
$telefono = "";
$raza = "";
$info = "";

// Compruebo si el formulario se ha enviado mediante el método POST.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    /* RECOGIDA DE DATOS:
       Uso trim() para limpiar espacios en blanco accidentales al principio o al final de los textos. */
    $nombre    = trim($_POST['nombre_del_criador']);
    $localidad = trim($_POST['localidad']);
    $telefono  = trim($_POST['telefono']);
    $raza      = trim($_POST['raza_de_animal']);
    $info      = trim($_POST['informacion_del_criador']);
    
    // Uso esta variable para que me diga si es verdadero.
    $formulario_valido = true; 

    // --- 3. PROCESO DE VALIDACIÓN ---
    
    if ($nombre == "") { 
        $error_nombre = "El nombre es obligatorio."; 
        $formulario_valido = false; 
    }
    
    if ($localidad == "") { 
        $error_localidad = "La localidad es obligatoria."; 
        $formulario_valido = false; 
    }
    
    /* VALIDACIÓN DEL TELÉFONO: 
       Uso ctype_digit para asegurar que solo sean números y strlen para que sean exactamente 9 cifras. */
    if ($telefono == "") {
        $error_telefono = "El teléfono es obligatorio.";
        $formulario_valido = false;
    } elseif (!ctype_digit($telefono) || strlen($telefono) != 9) {
        $error_telefono = "El teléfono debe tener 9 números.";
        $formulario_valido = false;
    }

    if ($info == "") {
        $error_desc = "La descripción no puede estar vacía.";
        $formulario_valido = false;
    }

    // Miro si han subido una foto revisando el array superglobal $_FILES.
    if (empty($_FILES['logo_del_criador']['name'])) {
        $error_foto = "Es obligatorio subir una foto.";
        $formulario_valido = false;
    }

    /* 4. GESTIÓN DE GUARDADO Y SUBIDA DE ARCHIVOS:
       Si el formulario es válido, procedemos a mover la foto y guardar en DB. */
    if ($formulario_valido == true) {
        
        $original = $_FILES['logo_del_criador']['name'];
        $temp = $_FILES['logo_del_criador']['tmp_name'];
        
        
        $info_archivo = pathinfo($original);
        $nombre_base = $info_archivo['filename'];
        $extension = strtolower($info_archivo['extension']);
        $nombre_foto = $nombre_base . '.' . $extension;

       
        $destino = "../imagenes/criadores_de_animales/";

        // Muevo el archivo de la memoria temporal del servidor a nuestra carpeta de imágenes.
        if (move_uploaded_file($temp, $destino . $nombre_foto)) {
            
            /* 5. INSERCIÓN SEGURA CON PDO:
               Utilizo sentencias preparadas para evitar ataques de Inyección SQL. */
            $sql = "INSERT INTO criadores_de_animales (nombre_del_criador, localidad, telefono, raza_de_animal, informacion_del_criador, logo_del_criador) 
                    VALUES (:nombre, :localidad, :telefono, :raza, :info, :foto)";
            
            $sentencia = $db->prepare($sql);
            
            // Vinculo las variables a los marcadores de la consulta (:nombre, :localidad...).
            $sentencia->bindParam(':nombre', $nombre);
            $sentencia->bindParam(':localidad', $localidad);
            $sentencia->bindParam(':telefono', $telefono);
            $sentencia->bindParam(':raza', $raza);
            $sentencia->bindParam(':info', $info);
            $sentencia->bindParam(':foto', $nombre_foto);

            if ($sentencia->execute()) {
                // Éxito: redirijo al listado principal.
                header("Location: ../administrador/gestion_de_criadores_de_animales.php");
                exit();
            } else {
                echo "Lo siento, ha habido un error al guardar en la base de datos.";
            }
        } else {
            echo "Lo siento, hubo un problema al transferir el archivo al servidor. Revisa el tamaño de la imagen.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir criadores - Find A Pet</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles_añadir_criadores.css">
</head>
<body class="fondo-formulario">

    <div id="google_translate_element"></div>

    <div class="contenedor">
        <div class="caja-formulario">
            <h2>Añadir criadores</h2>
            <p class="texto-secundario">Rellena los datos para registrar un nuevo criador en el sistema.</p>
            
            <form action="" method="POST" enctype="multipart/form-data" class="notranslate">
                <div class="bloque">
                    <h3>Datos de Contacto</h3>
                    
                    <label>Nombre del criador:</label>
                    <input type="text" name="nombre_del_criador" value="<?php echo $nombre; ?>">
                    <?php if ($error_nombre != "") { echo "<span class='msj-error'>$error_nombre</span>"; } ?>
                    
                    <label>Localidad:</label>
                    <input type="text" name="localidad" value="<?php echo $localidad; ?>">
                    <?php if ($error_localidad != "") { echo "<span class='msj-error'>$error_localidad</span>"; } ?>
                    
                    <label>Teléfono:</label>
                    <input type="text" name="telefono" value="<?php echo $telefono; ?>">
                    <?php if ($error_telefono != "") { echo "<span class='msj-error'>$error_telefono</span>"; } ?>
                </div>

                <div class="bloque">
                    <h3>Información Extra</h3>
                    <label>Raza que cría:</label>
                    <input type="text" name="raza_de_animal" value="<?php echo $raza; ?>">
                    
                    <label>Descripción:</label>
                    <textarea name="informacion_del_criador" rows="4"><?php echo $info; ?></textarea>
                    <?php if ($error_desc != "") { echo "<span class='msj-error'>$error_desc</span>"; } ?>

                    <label style="margin-top: 15px;">Logo o Foto:</label>
                    <input type="file" name="logo_del_criador">
                    <?php if ($error_foto != "") { echo "<span class='msj-error'>$error_foto</span>"; } ?>
                </div>
                
                <button type="submit" class="boton">Registrar Criador</button>
                <a href="../administrador/gestion_de_criadores_de_animales.php" class="volver">Cancelar y volver al listado</a>
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