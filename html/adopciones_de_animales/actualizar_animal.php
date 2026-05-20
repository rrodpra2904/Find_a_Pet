<?php 
// Incluyo el archivo de seguridad para que solo el administrador pueda entrar aquí.
include 'seguridad.php'; 

// Incluyo la conexión a la base de datos para poder hacer los cambios.
include 'conexion.php';

// Compruebo si los datos vienen del formulario por el método POST.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Recojo todos los datos que el administrador ha cambiado en el formulario.
    $id_up = $_POST['id_animal'];
    $nom   = $_POST['nombre_animal'];
    $raz   = $_POST['raza_animal'];
    $edad  = $_POST['edad_animal'];
    $desc  = $_POST['desc_animal'];
    $adopt = $_POST['adoptado_animal'];
    
    // Defino la ruta de la carpeta donde se van a guardar las fotos de los animales.
    $carpeta = "../imagenes/adopciones_de_animales/";

    // 1. Compruebo si el administrador ha seleccionado una foto nueva para subir.
    if (!empty($_FILES['foto_animal']['name'])) {
        
        // Cojo el nombre de la foto y la muevo a mi carpeta de imágenes.
        $foto = $_FILES['foto_animal']['name'];
        move_uploaded_file($_FILES['foto_animal']['tmp_name'], $carpeta . $foto);
        
        // Preparo la sintaxis de SQL para actualizar todo, incluyendo la foto nueva.
        $sql_up = "UPDATE animales SET 
                  nombre='$nom', raza='$raz', edad='$edad', 
                  descripcion='$desc', imagen='$foto', adoptado='$adopt' 
                  WHERE id=$id_up";
    } else {
        
        // 2. Si el administrador no ha subido foto nueva, solo actualizo los textos para no borrar la imagen que ya había.
        $sql_up = "UPDATE animales SET 
                  nombre='$nom', raza='$raz', edad='$edad', 
                  descripcion='$desc', adoptado='$adopt' 
                  WHERE id=$id_up";
    }

    // Lanzo la orden a la base de datos y, si todo va bien, vuelvo a la página web de gestión de adopciones de animales.
    if (mysqli_query($conexion, $sql_up)) {
        header("Location: ../administrador/gestion_de_adopciones_de_animales.php");
    } else {
        // Si hay algún fallo, muestro este mensaje para saber que algo ha salido mal.
        echo "Error al actualizar los datos";
    }
}
?>