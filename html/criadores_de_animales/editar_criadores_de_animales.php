<?php 
// Incluyo el archivo de seguridad para asegurarme de que solo usuarios autorizados accedan.
include 'seguridad.php'; 
?>

<?php
// Conecto a la base de datos.
include 'conexion.php';

/* 1. RECUPERACIÓN DEL REGISTRO ESPECÍFICO:
   Recojo el ID del criador que quiero editar a través de la URL (método GET). 
   Uso un condicional para que si no hay "id" en la URL, valga 0 por defecto y no salte el Warning. */
$id = isset($_GET['id']) ? $_GET['id'] : 0;

/* Realizo una consulta SELECT filtrando por ese ID para obtener 
   toda la información actual de ese criador usando PDO. */
$sql = "SELECT * FROM criadores_de_animales WHERE id = :id";
$sentencia = $db->prepare($sql);
$sentencia->bindParam(':id', $id);
$sentencia->execute();

/* Guardo los datos en la variable $dato para poder pintarlos en los campos del formulario. */
$dato = $sentencia->fetch(PDO::FETCH_ASSOC);

/* Si el ID no existe o no se encuentra en la base de datos, inicializamos $dato vacío 
   para que no salten más errores al intentar pintar los campos abajo. */
if (!$dato) {
    $dato = [
        'id' => '',
        'nombre_del_criador' => '',
        'telefono' => '',
        'localidad' => '',
        'raza_de_animal' => '',
        'informacion_del_criador' => ''
    ];
}
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
    
    <form action="actualizar_criadores_de_animales.php" method="POST" enctype="multipart/form-data">
        
        <?php
        /* 2. CAMPO OCULTO (ID):
           Es fundamental enviar el ID de forma oculta para que el archivo que actualiza 
           sepa exactamente qué registro debe modificar en la base de datos. */
        ?>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($dato['id'], ENT_QUOTES, 'UTF-8'); ?>">

        <?php
        /* 3. CARGA DE DATOS EN LOS INPUTS:
           Uso el atributo 'value' para mostrar la información que ya existe en la BD. */
        ?>
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($dato['nombre_del_criador'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label>Teléfono:</label>
        <input type="text" name="telefono" value="<?php echo htmlspecialchars($dato['telefono'], ENT_QUOTES, 'UTF-8'); ?>" required maxlength="9">

        <label>Localidad:</label>
        <input type="text" name="localidad" value="<?php echo htmlspecialchars($dato['localidad'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label>Raza:</label>
        <input type="text" name="raza" value="<?php echo htmlspecialchars($dato['raza_de_animal'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label>Información:</label>
        <textarea name="info" rows="3" required><?php echo htmlspecialchars($dato['informacion_del_criador'], ENT_QUOTES, 'UTF-8'); ?></textarea>

        <label>Foto/Logo (opcional):</label>
        <input type="file" name="logo_archivo">
        <p style="font-size: 12px; color: #666;">* Deja este campo vacío si no quieres cambiar la imagen actual.</p>

        <button type="submit" class="boton-lila">GUARDAR CAMBIOS</button>
        <a href="../administrador/gestion_de_criadores_de_animales.php" class="volver">Volver atrás</a>
    </form>
</div>

</body>
</html>