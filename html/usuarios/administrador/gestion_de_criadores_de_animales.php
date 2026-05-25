<?php
// 1. Control de seguridad: Importo el archivo que verifica si el usuario es administrador.

// Pongo seguridad.php para que en caso de que no se haya iniciado sesión con un 
// usuario con rol administrador no pueda entrar por la URL a la página web de gestión 
// de criadores de animales sin que haya iniciado sesión como administrador.
include 'seguridad.php'; 
?>

<?php
// 2. Importo la configuración de la conexión a la base de datos MySQL.
include '../criadores_de_animales/conexion.php'; 

// 3. Lanzo la consulta a la tabla de criadores de animales.

// Lo ordeno por ID de forma descendente (DESC) para que los últimos animales que he subido salgan los primeros de la lista.
$res = mysqli_query($conexion, "SELECT * FROM criadores_de_animales ORDER BY id DESC");

// 4. Si la base de datos da error, que el sistema se pare y me diga qué ha pasado para poder arreglarlo.
if (!$res) {
    die("Error en la consulta: " . mysqli_error($conexion));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control - Criadores</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles_gestion_de_criadores_de_animales.css">
</head>
<body>

<div class="caja-principal">
    <h2>Gestión de Criadores Verificados</h2>

    <a href="../../criadores_de_animales/añadir.php" class="btn-nuevo">+ Nuevo Criador</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Logo</th>
                <th>Nombre / Teléfono</th>
                <th>Localidad</th>
                <th>Raza</th>
                <th>Información</th> 
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            
            <?php 
                 /* 5. Empiezo el bucle para ir mostrando por pantalla cada criador uno a uno en su fila correspondiente.
                    Uso mysqli_fetch_assoc para que me devuelva los datos con el nombre de la columna de la tabla. */
                 while ($fila = mysqli_fetch_assoc($res)) { 
            ?>
            <tr>
                <td><?php echo $fila['id']; ?></td>
                
                <td>
                    <img src="../imagenes/criadores_de_animales/<?php echo $fila['logo_del_criador']; ?>" class="foto-logo">
                </td>
                
                <td>
                    <span style="font-size: 16px; font-weight: 600;"><?php echo $fila['nombre_del_criador']; ?></span><br>
                    <small style="color: #888;"><?php echo $fila['telefono']; ?></small>
                </td>
                
                <td>
                    <span class="ciudad"><?php echo $fila['localidad']; ?></span>
                </td>
                
                <td>
                    <span class="etiqueta-raza"><?php echo $fila['raza_de_animal']; ?></span>
                </td>
                
                <td class="col-info">
                    <?php 
                         /* Uso nl2br para que los saltos de línea que se guardan en la base de datos 
                            (cuando el usuario pulsa 'Enter' en el formulario) se vean correctamente 
                            como saltos de línea en el navegador. */
                         echo nl2br($fila['informacion_del_criador']); 
                    ?>
                </td>
                
                <td>
                    <a href="../../criadores_de_animales/editar_criadores_de_animales.php?id=<?php echo $fila['id']; ?>" class="btn-accion btn-modificar">Modificar</a>
                    
                    <a href="../../criadores_de_animales/eliminar_criador.php?id=<?php echo $fila['id']; ?>" 
                       class="btn-accion btn-eliminar" 
                       onclick="return confirm('¿Estás seguro de eliminar este criador?')">
                       Eliminar
                    </a>
                </td>
            </tr>
            <?php } // 6. Aquí cierro el bucle while una vez que se han pintado todos los registros ?>
        </tbody>
    </table>
</div>
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.pathname);
        }
    </script>
</body>
</html>