<?php 
// 1. Control de seguridad: Verifico que solo los administradores puedan ver estos informes.
include 'seguridad.php'; 

// 2. Cargo la conexión de adopciones para poder contar cuántos animales hay.
include '../adopciones_de_animales/conexion.php'; 

/* 3. Bloque try/catch para estadísticas:
   Intento sacar los totales de la base de datos de forma segura. */
try {
    // Lanzo una consulta SQL con COUNT(*) para que la base de datos me diga el número total de animales.
    $consulta_anim = $db->query("SELECT COUNT(*) FROM animales");
    $total_animales = $consulta_anim->fetchColumn(); // Guardo el número resultante en esta variable.
    
    // Cambio la conexión para acceder ahora a la tabla de los criadores.
    include '../criadores_de_animales/conexion.php'; 
    
    // Repito el proceso: cuento cuántos criadores hay registrados en total.
    $consulta_criad = $db->query("SELECT COUNT(*) FROM criadores_de_animales");
    $total_criadores = $consulta_criad->fetchColumn();

} catch (PDOException $e) {
    // Si algo falla con las tablas o la conexión, pongo los contadores a 0 para que la página web no de error.
    $total_animales = 0;
    $total_criadores = 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informes - FindAPet</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles_panel.css">
    </head>
<body>

    <nav class="navbar">
        <div class="nav-links">
            <a href="panel.php">Informes</a>
            <a href="gestion_de_adopciones_de_animales.php">Adopciones</a>
            <a href="gestion_de_criadores_de_animales.php">Criadores</a>
        </div>
        <a href="salir.php" class="btn-salir">Cerrar Sesión</a>
    </nav>

    <div class="container">
        <h1 class="titulo-principal">Informe de Actividad del Sistema</h1>

        <div class="grid-informes">
            
            <div class="tarjeta">
                <div style="font-size: 50px; margin-bottom: 10px;">🐶</div>
                <div class="numero"><?php echo $total_animales; ?></div>
                <h3>Animales Registrados</h3>
            </div>

            <div class="tarjeta">
                <div style="font-size: 50px; margin-bottom: 10px;">🏠</div>
                <div class="numero"><?php echo $total_criadores; ?></div>
                <h3>Criadores Colaboradores</h3>
            </div>
            
        </div>
    </div>

</body>
</html>