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

// Recojo el ID que me llega por la URL de forma segura para evitar Warnings si no existe.
$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Preparo la consulta con un marcador (:id) para que sea seguro y evitar ataques de inyección SQL.
$sql = "SELECT * FROM animales WHERE id = :id";
$stmt = $db->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

// Guardo los datos del animal en la variable $animal.
$animal = $stmt->fetch(PDO::FETCH_ASSOC);

// Si por alguna razón el ID no existe in la base de datos, 
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
    
    <p class="texto-introduccion" style="font-size: 14px; color: #555; margin-bottom: 20px; line-height: 1.5;">
        Desde este panel puedes actualizar la información de la ficha del animal seleccionado. Modifica los campos necesarios, revisa el estado de adopción y guarda los cambios para que la información se actualice de forma inmediata en el catálogo público.
    </p>
    
    <form action="actualizar_animal.php" method="POST" enctype="multipart/form-data">
        
        <input type="hidden" name="id_animal" value="<?php echo htmlspecialchars($animal['id'], ENT_QUOTES, 'UTF-8'); ?>">

        <label>Nombre:</label>
        <input type="text" name="nombre_animal" value="<?php echo htmlspecialchars($animal['nombre'], ENT_QUOTES, 'UTF-8'); ?>" required>
        
        <label>Raza:</label>
        <input type="text" name="raza_animal" value="<?php echo htmlspecialchars($animal['raza'], ENT_QUOTES, 'UTF-8'); ?>" required>
        
        <label>Edad:</label>
        <input type="number" name="edad_animal" value="<?php echo htmlspecialchars($animal['edad'], ENT_QUOTES, 'UTF-8'); ?>">
        
        <label>Descripción:</label>
        <textarea name="desc_animal" rows="3"><?php echo htmlspecialchars($animal['descripcion'], ENT_QUOTES, 'UTF-8'); ?></textarea>

        <label>¿Está adoptado?</label>
        <div class="contenedor-radio">
            <input type="radio" name="adoptado_animal" value="No" <?php if($animal['adoptado'] == 'No') echo 'checked'; ?>> Todavía busca casa
            <br>
            <input type="radio" name="adoptado_animal" value="Si" <?php if($animal['adoptado'] == 'Si') echo 'checked'; ?>> Sí, ya está adoptado
        </div>
        
        <label style="margin-top: 15px;">Foto nueva (opcional):</label>
        <p style="font-size: 12px; color: #777; margin-bottom: 5px;">Imagen actual: <?php echo htmlspecialchars($animal['imagen'], ENT_QUOTES, 'UTF-8'); ?></p>
        
        <div style="margin-bottom: 12px;">
            <img src="../../imagenes/adopciones_de_animales/<?php echo htmlspecialchars($animal['imagen'], ENT_QUOTES, 'UTF-8'); ?>" alt="Miniatura actual" style="width: 80px; height: auto; border-radius: 5px; border: 1px solid #ccc; display: block;">
        </div>

        <input type="file" name="foto_animal" accept="image/*">
        
        <button type="submit" class="btn-update" style="margin-top: 20px;">GUARDAR CAMBIOS</button>
        <a href="../administrador/gestion_de_adopciones_de_animales.php" class="volver">Cancelar y volver</a>
    </form>
</div>

</body>
</html>