<?php
session_start();
require '../conexion_db.php'; 

if (!isset($_SESSION['usuarioAutenticado']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: login_usuarios.php");
    exit();
}

$nombreUsuario = $_SESSION['usuarioAutenticado'];

// Traer los datos del tablón de anuncios de adopciones
try {
    $queryTablon = $db->query("SELECT * FROM tablon_de_adopciones ORDER BY id DESC");
    $animalesTablon = $queryTablon->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $animalesTablon = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario - Find A Pet</title>
    <link rel="stylesheet" href="../styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; }
        .navbar { background-color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .navbar h1 { margin: 0; font-size: 20px; color: #2c3e50; }
        .nav-links a { text-decoration: none; color: #7f8c8d; margin-left: 20px; font-size: 14px; font-weight: 500; }
        .nav-links a.logout { color: #e74c3c; }
        
        .contenedor-principal { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        
        /* Sistema de Pestañas */
        .pestanas-menu { display: flex; gap: 10px; margin-bottom: 25px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        .btn-pestana { background: none; border: none; padding: 10px 20px; font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 600; color: #94a3b8; cursor: pointer; transition: all 0.3s; border-radius: 6px; }
        .btn-pestana.activa { color: #9b59b6; background-color: #f3e8ff; }
        .contenido-pestana { display: none; }
        .contenido-pestana.activa { display: block; }

        .section-header { text-align: left; margin-bottom: 30px; background: white; padding: 25px; border-radius: 12px; border: 1px solid #edf2f7; }
        .section-header h2 { margin: 0 0 10px 0; color: #2c3e50; font-size: 22px; }
        .section-header p { margin: 0; color: #64748b; font-size: 14px; line-height: 1.6; }
        
        .btn-action-primary { background-color: #9b59b6; color: white; border: none; padding: 11px 22px; border-radius: 8px; cursor: pointer; font-weight: 500; font-size: 13.5px; display: inline-block; text-decoration: none; transition: background 0.2s; }
        .btn-action-primary:hover { background-color: #8e44ad; }

        .status-box-empty { text-align: center; background: white; padding: 50px; border-radius: 12px; border: 1px solid #edf2f7; }
        .status-box-empty h4 { margin: 0 0 5px 0; color: #334155; font-size: 16px; }
        .status-box-empty p { margin: 0; color: #94a3b8; font-size: 13.5px; }
    </style>
</head>
<body>

<nav class="navbar">
    <h1>🐾 Find A Pet — Panel Cliente</h1>
    <div class="nav-links">
        <span style="font-size: 14px; color: #334155;">Bienvenido, <strong><?php echo htmlspecialchars($nombreUsuario); ?></strong></span>
        <a href="logout.php" class="logout">Cerrar Sesión</a>
    </div>
</nav>

<div class="contenedor-principal">

    <div class="pestanas-menu">
        <button class="btn-pestana activa" onclick="cambiarPestana('tablon')">📋 Tablón de Avisos</button>
        <button class="btn-pestana" onclick="cambiarPestana('adopciones')"> Compromiso de Adopción</button>
    </div>

    <div id="tablon" class="contenido-pestana activa">
        <div class="section-header">
            <h2>Tablón de adopciones de animales</h2>
            <p>Échale un vistazo a los animales disponibles antes de que aparezcan en la página pública de adopciones. También puedes subir tú mismo un animal perdido que hayas encontrado o uno del que no puedas hacerte cargo por cualquier motivo. Antes de poder adoptarlos, primero tendremos que revisarlos y darles el visto bueno (<strong>los verás destacados en color verde</strong>).</p>
            
            <div style="margin-top: 15px; background: #e8f5e9; border-left: 4px solid #2e7d32; padding: 15px; border-radius: 4px; font-size: 13.5px; color: #1b5e20; line-height: 1.5;">
                <strong>🐾 ¿Cómo funciona el proceso para adoptar desde aquí?</strong><br>
                1. Elige uno de los animales que ya estén verificados (los marcados en color verde).<br>
                2. Si es tu primera vez, entra en la sección "Compromiso de Adopción" y rellena el formulario.<br>
                3. Cuando envíes el formulario, te facilitaremos el contacto de la persona responsable para proceder.<br>
                4. Una vez validado, se retirará el aviso.
            </div>
        </div>
        
        <div style="text-align: left; margin-bottom: 25px;">
            <a href="subir_animal.php" class="btn-action-primary">➕ Subir un animal (Encontrado o caso particular)</a>
        </div>

        <?php if (empty($animalesTablon)): ?>
            <div class="status-box-empty">
                <span style="font-size: 40px; display: block; margin-bottom: 10px;">📭</span>
                <h4>El tablón está vacío ahora mismo</h4>
                <p>No hay ninguna mascota publicada en este momento.</p>
            </div>
        <?php else: ?>
            <div class="contenedor-animales-tablon" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 20px; text-align: left;">
                <?php foreach ($animalesTablon as $animal): ?>
                    <?php 
                        // Borde verde y fondo sutil si está verificado, si no normal
                        $estiloCard = ($animal['verificado'] == 1) ? 'border: 2px solid #2ecc71; background-color: #e8f8f5;' : 'border: 1px solid #edf2f7; background: white;';
                    ?>
                    <div class="card-animal-tablon" style="<?php echo $estiloCard; ?> padding: 0; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); overflow: hidden; display: flex; flex-direction: column;">
                        
                        <div style="width: 100%; height: 180px; background-color: #f1f5f9; overflow: hidden;">
                            <?php if (!empty($animal['foto']) && file_exists("./imagenes/animales/" . $animal['foto'])): ?>
                                <img src="./imagenes/animales/<?php echo htmlspecialchars($animal['foto']); ?>" alt="Foto" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                            <?php else: ?>
                                <img src="../imagenes/findapet.jpeg" alt="Por defecto" style="width: 100%; height: 100%; object-fit: cover; display: block; opacity: 0.5;">
                            <?php endif; ?>
                        </div>

                        <div style="padding: 20px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <h4 style="color: #2c3e50; margin: 0 0 10px 0; font-size: 17px; font-weight: 600; text-transform: capitalize;"><?php echo htmlspecialchars($animal['nombre']); ?></h4>
                                <p style="font-size: 13.5px; margin: 0 0 5px 0; color: #475569;"><strong>Especie:</strong> <?php echo htmlspecialchars($animal['especie']); ?></p>
                                <p style="font-size: 13.5px; margin: 0 0 5px 0; color: #475569;"><strong>Raza:</strong> <?php echo htmlspecialchars($animal['raza'] ?: 'Mestizo'); ?></p>
                                <p style="font-size: 12.5px; color: #7f8c8d; margin: 0 0 15px 0;">📍 Encontrado en: <?php echo htmlspecialchars($animal['ubicacion']); ?></p>
                            </div>
                            
                            <div>
                                <?php if ($animal['verificado'] == 1): ?>
                                    <span style="color: #27ae60; font-weight: bold; font-size: 11.5px; display: inline-block; background: #d4edda; padding: 4px 8px; border-radius: 4px;">✅ Verificado por Admin</span>
                                <?php else: ?>
                                    <span style="color: #e67e22; font-weight: bold; font-size: 11.5px; display: inline-block; background: #fff3cd; padding: 4px 8px; border-radius: 4px;">⏳ Pendiente de revisión</span>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="adopciones" class="contenido-pestana">
        <div class="section-header">
            <h2>Formulario de Compromiso de Adopción</h2>
            <p>Rellena este formulario si estás interesado en adoptar formalmente.</p>
        </div>
        </div>

</div>

<script>
function cambiarPestana(idPestana) {
    document.querySelectorAll('.contenido-pestana').forEach(p => p.classList.remove('activa'));
    document.querySelectorAll('.btn-pestana').forEach(b => b.classList.remove('activa'));
    
    document.getElementById(idPestana).classList.add('activa');
    event.currentTarget.classList.add('activa');
}
</script>
</body>
</html>