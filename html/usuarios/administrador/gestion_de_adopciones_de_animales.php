<?php
// 1. Control de seguridad: Importo el archivo que verifica si el usuario es administrador.
// Si alguien intenta entrar por la URL sin permiso, el archivo 'seguridad.php' lo echará.
include 'seguridad.php'; 
?>

<?php 
// 2. Importo la conexión a la base de datos MySQL.
include '../adopciones_de_animales/conexion.php'; 

// 3. Lanzo la consulta a la tabla 'animales'. 
// Uso "ORDER BY id DESC" para que los animales más nuevos que he añadido aparezcan arriba del todo de la lista.
$res = mysqli_query($conexion, "SELECT * FROM animales ORDER BY id DESC");

// 4. Verifico si la consulta ha funcionado correctamente. 
// Si hay algún problema (por ejemplo, una tabla mal escrita), el die() detendrá el código y mostrará el error.
if (!$res) {
    die("Error en la consulta: " . mysqli_error($conexion));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Adopciones - Find a Pet</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles_gestion_de_adopciones.css">
</head>
<body>

<div class="contenedor-principal">
    <h2>Panel de Gestión de Animales</h2><br>

    <a href="../adopciones_de_animales/añadir.php" class="boton-nuevo">Añadir un animal</a>

    <div class="div-table">
        <table>
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Raza</th>
                    <th>Edad</th>
                    <th>Descripción</th>
                    <th>Adopción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            
            <tbody>
                <?php 
                    /* 5. Empieza el bucle while: 
                       Este trozo de código se repetirá tantas veces como animales tengamos en la base de datos.
                       mysqli_fetch_assoc va sacando una fila cada vez que hay un animal en la base de datos
                       y la guarda en la variable $row. */
                    while ($row = mysqli_fetch_assoc($res)) { 
                ?>
                <tr>
                    <td>
                        <img src="../imagenes/adopciones_de_animales/<?php echo $row['imagen']; ?>" class="img-animal">
                    </td>
                    
                    <td><strong><?php echo $row['nombre']; ?></strong></td>
                    <td><?php echo $row['raza']; ?></td>
                    <td><?php echo $row['edad']; ?> años</td>
                    <td class="texto-desc"><?php echo $row['descripcion']; ?></td>
                    
                    <td>
                        <span style="font-weight: bold; color: <?php echo ($row['adoptado'] == 'Si') ? '#01923e' : '#ad5605'; ?>;">
                            <?php echo $row['adoptado']; ?>
                        </span>
                    </td>
                    
                    <td class="celda-botones">
                        <a href="../adopciones_de_animales/editar_animal.php?id=<?php echo $row['id']; ?>" class="boton-accion boton-editar">Modificar</a>
                        
                        <a href="../adopciones_de_animales/eliminar_animal.php?id=<?php echo $row['id']; ?>" 
                           class="boton-accion boton-borrar" 
                           onclick="return confirm('¿Seguro que quieres borrar este registro?')">Eliminar</a>
                    </td>
                </tr>
                <?php } // 6. Aquí cierro el bucle: Una vez que se han mostrado todos los datos, el código sigue hacia abajo. ?>
            </tbody>
        </table>
    </div>
</div>

    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.pathname);
        }
    </script>
</body>
</html>