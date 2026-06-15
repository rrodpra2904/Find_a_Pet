<?php 
// Incluyo el archivo de seguridad para asegurarnos de que solo usuarios autorizados accedan.
include 'seguridad.php'; 
?>

<?php
// Conecto a la base de datos.
include '../conexion.php';

/* 1. RECUPERACIÓN DEL REGISTRO ESPECÍFICO:
   Recojo el ID del criador que queremos editar a través de la URL (método GET). */
$id = $_GET['id'];

/* Realizo una consulta SELECT filtrando por ese ID para obtener 
   toda la información actual de ese criador. */
$consulta = mysqli_query($conexion, "SELECT * FROM criadores_de_animales WHERE id = '$id'");

/* Guardo los datos en la variable $dato para poder pintarlos en los campos del formulario. */
$dato = mysqli_fetch_assoc($consulta);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Criador - Find A Pet</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles_editar_criadores.css">
</head>
<body>

<div class="caja-lila">
    <h2>Editar Criador</h2>
    
    <form action="../actualizar_criadores_de_animales.php" method="POST" enctype="multipart/form-data">
        
        /* 2. CAMPO OCULTO (ID):
           Es fundamental enviar el ID de forma oculta para que el archivo que actualiza 
           sepa exactamente qué registro debe modificar en la base de datos. */
        <input type="hidden" name="id" value="<?php echo $dato['id']; ?>">

        /* 3. CARGA DE DATOS EN LOS INPUTS:
           Usamos el atributo 'value' para mostrar la información que ya existe en la BD. */
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?php echo $dato['nombre_del_criador']; ?>" required>

        <label>Teléfono:</label>
        <input type="text" name="telefono" value="<?php echo $dato['telefono']; ?>" required maxlength="9">

        <label>Localidad:</label>
        <input type="text" name="localidad" value="<?php echo $dato['localidad']; ?>" required>

        <label>Raza:</label>
        <input type="text" name="raza" value="<?php echo $dato['raza_de_animal']; ?>" required>

        <label>Información:</label>
        <textarea name="info" rows="3" required><?php echo $dato['informacion_del_criador']; ?></textarea>

        <label>Foto/Logo (opcional):</label>
        <input type="file" name="logo_archivo">
        <p style="font-size: 12px; color: #666;">* Deja este campo vacío si no quieres cambiar la imagen actual.</p>

        <button type="submit" class="boton-lila">GUARDAR CAMBIOS</button>
        <a href="../administrador/gestion_de_criadores_de_animales.php" class="volver">Volver atrás</a>
    </form>
</div>

</body>
</html>