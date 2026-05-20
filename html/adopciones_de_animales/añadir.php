<?php 

// 1. Control de acceso

// Incluyo el archivo de seguridad para que nadie pueda entrar a esta página web sin estar logueado.
include 'seguridad.php'; 

// Incluyo el archivo de conexión que contiene el objeto $db (PDO).
include 'conexion.php'; 

// --- Bloqueo de seguridad para emplados ---

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

// Variables para conservar los datos: así, si hay un error, el usuario no pierde lo que ya escribió.
$nombre = "";
$raza = "";
$edad = "";
$descripcion = "";

// 3. Procesamiento del formulario

// Solo ejecuto este bloque si el usuario ha pulsado el botón de enviar (método POST).
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Recojo los datos y uso trim() para limpiar espacios accidentales al principio o al final.
    $nombre      = trim($_POST['nombre_animal']);
    $raza        = trim($_POST['raza_animal']);
    $edad        = trim($_POST['edad_animal']); 
    $descripcion = trim($_POST['desc_animal']); 
    
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

    // Compruebo que la edad no esté vacía y que sea un número positivo.
    if ($edad == "") {
        $error_edad = "La edad es obligatoria.";
        $todo_ok = false;
    } elseif (!is_numeric($edad) || $edad < 0) {
        $error_edad = "La edad debe ser un número válido.";
        $todo_ok = false;
    }

    if ($descripcion == "") {
        $error_desc = "La descripción no puede estar vacía.";
        $todo_ok = false;
    }

    // Verifico que se haya seleccionado un archivo para la fotografía.
    if (empty($_FILES['foto_animal']['name'])) {
        $error_foto = "Tienes que subir una foto del animal.";
        $todo_ok = false;
    }

    // 5. Guardado de datos (Si todo está correcto)
    if ($todo_ok) {
        
        // Gestiono la subida de la imagen: nombre original y ruta temporal.
        $foto = $_FILES['foto_animal']['name'];
        $temp = $_FILES['foto_animal']['tmp_name'];
        $carpeta = "../imagenes/adopciones_de_animales/";
        
        // Muevo la foto desde la memoria temporal del servidor a mi carpeta definitiva.
        if (move_uploaded_file($temp, $carpeta . $foto)) {
            
            // Preparo la consulta SQL con marcadores (:nombre, etc.) para evitar inyecciones SQL.
            $sql = "INSERT INTO animales (nombre, raza, edad, descripcion, imagen, adoptado) 
                    VALUES (:nombre, :raza, :edad, :descripcion, :imagen, 'No')";
            
            $sentencia = $db->prepare($sql);

            // Vinculo mis variables a los marcadores de la consulta de forma segura.
            $sentencia->bindParam(':nombre', $nombre);
            $sentencia->bindParam(':raza', $raza);
            $sentencia->bindParam(':edad', $edad);
            $sentencia->bindParam(':descripcion', $descripcion);
            $sentencia->bindParam(':imagen', $foto);

            // Ejecuto la sentencia en la base de datos.
            if ($sentencia->execute()) {
                // Si tiene éxito, vuelvo a la pantalla de gestión de adopciones de animales.
                header("Location: ../administrador/gestion_de_adopciones_de_animales.php");
                exit(); 
            } else {
                echo "Lo siento, no se ha podido guardar el registro en este momento.";
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
    <title>Añadir Animales</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles_añadir_adopciones.css">
</head>
<body class="fondo-formulario">

    <div id="google_translate_element"></div>

    <div class="contenedor">
        <div class="caja-formulario">
            <h2>Añadir animales</h2>
            <p class="texto-secundario">Rellena la ficha para que el animal aparezca en la lista de adopciones.</p>
            
            <form action="" method="POST" enctype="multipart/form-data" class="notranslate">
                
                <div class="bloque">
                    <h3>Información del Animal</h3>
                    
                    <label>Nombre del animal:</label>
                    <input type="text" name="nombre_animal" value="<?php echo $nombre; ?>">
                    <?php if ($error_nombre != "") { echo "<span class='msj-error'>$error_nombre</span>"; } ?>
                    
                    <label>Raza:</label>
                    <input type="text" name="raza_animal" value="<?php echo $raza; ?>">
                    <?php if ($error_raza != "") { echo "<span class='msj-error'>$error_raza</span>"; } ?>
                    
                    <label>Edad:</label>
                    <input type="text" name="edad_animal" value="<?php echo $edad; ?>">
                    <?php if ($error_edad != "") { echo "<span class='msj-error'>$error_edad</span>"; } ?>
                </div>

                <div class="bloque">
                    <h3>Descripción y foto</h3>
                    
                    <label>Descripción:</label>
                    <textarea name="desc_animal" rows="4"><?php echo $descripcion; ?></textarea>
                    <?php if ($error_desc != "") { echo "<span class='msj-error'>$error_desc</span>"; } ?>
                    
                    <label style="margin-top: 20px;">Subir Fotografía:</label>
                    <input type="file" name="foto_animal">
                    <?php if ($error_foto != "") { echo "<span class='msj-error'>$error_foto</span>"; } ?>
                </div>

                <button type="submit" class="boton">Guardar Animal</button>
                
                <a href="../administrador/gestion_de_adopciones_de_animales.php" class="volver">
                    Cancelar y volver a la lista de adopciones
                </a>
            </form>
        </div>
    </div>

    <script>
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage: 'es',
            includedLanguages: 'en,fr,it,de,pt,es',
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
            autoDisplay: false
        });
    }
    </script>
    <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
     
    <script>
        // Este código evita que, al recargar la página web, se reenvíe el formulario 
        // y aparezcan de nuevo los mensajes de error antiguos de forma innecesaria.
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.pathname);
        }
    </script>

</body>
</html>