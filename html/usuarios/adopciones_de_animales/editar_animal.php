<?php 

// 1. Seguridad de acceso

// Verifico que el usuario esté logueado.
include 'seguridad.php'; 

// --- Bloqueo de seguridad para empleados ---

// Al igual que el archivo añadir.php, si el usuario es un empleado lo echo, 
// porque solo el administrador puede editar los datos de los animales.
if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'empleado') {
    header("Location: ../administrador/gestion_de_adopciones_de_animales.php");
    exit();
}

// Conecto a la base de datos con PDO.
include 'conexion.php';

// 2. Recuperación del animal

// Recojo el ID que me llega por la URL para saber qué animal quiero editar.
$id = $_GET['id'];

// Preparo la consulta con un marcador (:id) para que sea seguro y evitar ataques de inyección SQL.
$sql = "SELECT * FROM animales WHERE id = :id";
$stmt = $db->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

// Guardo los datos del animal en la variable $animal.
$animal = $stmt->fetch(PDO::FETCH_ASSOC);

// Si por alguna razón el ID no existe en la base de datos, 
// redirijo al usuario a la gestión de adopciones de animales para evitar errores en la página web.
if (!$animal) {
    header("Location: ../administrador/gestion_de_adopciones_de_animales.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar animal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles_editar_animal.css">
</head>
<body>

<div class="caja">
    <h2>Modificar Animal</h2>
    
    <form action="actualizar_animal.php" method="POST" enctype="multipart/form-data">
        
        <input type="hidden" name="id_animal" value="<?php echo $animal['id']; ?>">

        <label>Nombre:</label>
        <input type="text" name="nombre_animal" value="<?php echo $animal['nombre']; ?>" required>
        
        <label>Raza:</label>
        <input type="text" name="raza_animal" value="<?php echo $animal['raza']; ?>" required>
        
        <label>Edad:</label>
        <input type="number" name="edad_animal" value="<?php echo $animal['edad']; ?>">
        
        <label>Descripción:</label>
        <textarea name="desc_animal" rows="3"><?php echo $animal['descripcion']; ?></textarea>

        <label>¿Está adoptado?</label>
        <div class="contenedor-radio">
            <input type="radio" name="adoptado_animal" value="No" <?php if($animal['adoptado'] == 'No') echo 'checked'; ?>> Todavía busca casa
            <br>
            <input type="radio" name="adoptado_animal" value="Si" <?php if($animal['adoptado'] == 'Si') echo 'checked'; ?>> Sí, ya está adoptado
        </div>
        
        <label>Foto nueva (opcional):</label>
        <p style="font-size: 12px; color: #777;">Imagen actual: <?php echo $animal['imagen']; ?></p>
        <input type="file" name="foto_animal">
        
        <button type="submit" class="btn-update">GUARDAR CAMBIOS</button>
        <a href="../administrador/gestion_de_adopciones_de_animales.php" class="volver">Cancelar y volver</a>
    </form>
</div>

</body>
</html>