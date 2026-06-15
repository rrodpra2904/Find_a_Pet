<?php 
// Incluyo el archivo de seguridad para verificar que quien edita es un administrador logueado.
include 'seguridad.php'; 

// Conecto con la base de datos para poder aplicar los cambios.
include 'conexion.php';

/* 1. RECOGIDA DE DATOS DEL FORMULARIO:
   Recojo todos los textos que el usuario ha modificado en el formulario de edición. */
$id     = $_POST['id'];
$nombre = $_POST['nombre'];
$tel    = $_POST['telefono'];
$loc    = $_POST['localidad'];
$raza   = $_POST['raza'];
$info   = $_POST['info'];

/* 2. GESTIÓN DE LA IMAGEN (Logo):
   Miro si el usuario ha seleccionado un archivo nuevo en el campo de tipo 'file'. */
$foto = $_FILES['logo_archivo']['name'];

if ($foto != "") {
    /* CASO A: SI HAY FOTO NUEVA.
       Lo subo a la carpeta correspondiente del servidor. */
    move_uploaded_file($_FILES['logo_archivo']['tmp_name'], "../imagenes/criadores_de_animales/" . $foto);
    
    /* Preparamos la consulta SQL para actualizar todos los campos, 
       incluyendo el nombre del nuevo archivo de imagen. */
    $sql = "UPDATE criadores_de_animales SET 
            nombre_del_criador='$nombre', 
            telefono='$tel', 
            localidad='$loc', 
            raza_de_animal='$raza', 
            informacion_del_criador='$info', 
            logo_del_criador='$foto' 
            WHERE id='$id'";
} else {
    /* CASO B: SI NO HAY FOTO NUEVA.
       Solo actualizo los textos. Si no lo higo así, el campo de la foto 
       se quedaría vacío y borraría el logo anterior por error. */
    $sql = "UPDATE criadores_de_animales SET 
            nombre_del_criador='$nombre', 
            telefono='$tel', 
            localidad='$loc', 
            raza_de_animal='$raza', 
            informacion_del_criador='$info' 
            WHERE id='$id'";
}

/* 3. EJECUCIÓN DE LA CONSULTA:
   Lanzo la orden a la base de datos usando la conexión establecida. */
mysqli_query($conexion, $sql);

/* 4. FINALIZACIÓN Y REDIRECCIÓN:
   Una vez guardados los cambios, devolvemos al administrador a la tabla de gestión 
   para que pueda ver los datos ya actualizados. */
header("Location: ../administrador/gestion_de_criadores_de_animales.php");
?>