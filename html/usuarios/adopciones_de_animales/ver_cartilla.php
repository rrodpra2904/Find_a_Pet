<?php 
// Incluyo el archivo de seguridad para comprobar que el usuario ha pasado por el formulario previo.
include("../administrador/seguridad_formulario_adopciones.php"); 

// Conexión a la base de datos
include 'conexion.php'; 

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cojo el ID que llega por la URL (?id=71)
$id_animal = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Inicializo variables vacías por defecto
$nombre = "";
$especie = "No especificada";
$raza = "No especificada";
$edad = "-";
$sexo = "-";
$chip = "No asignado / Sin chip";
$rabia = "No";
$desparasitado = "No";
$vacuna_nombre = "Ninguna";
$vacuna_fecha = "-";
$vacuna_lote = "-";
$observaciones = "Sin observaciones.";

if ($id_animal > 0) {
    // Busco los datos del animal seleccionado
    $sql = "SELECT * FROM animales WHERE id = $id_animal";
    $res = mysqli_query($conexion, $sql);

    if ($f = mysqli_fetch_assoc($res)) {

        $nombre  = isset($f['nombre']) ? $f['nombre'] : "";
        $raza    = isset($f['raza']) ? $f['raza'] : "No especificada";
        $edad    = !empty($f['edad']) ? $f['edad'] : "-";
        
        // CONTROL DE SEXO
        if (!empty($f['sexo'])) {
            $sexo = $f['sexo'];
        }
        
        // 1. CONTROL DE ESPECIE
        if (!empty($f['especie'])) {
            $especie = $f['especie'];
        } elseif (!empty($f['especie_categoria'])) {
            $especie = $f['especie_categoria'];
        }
        
        // 2. CONTROL DE MICROCHIP
        if (!empty($f['codigo_microchip'])) {
            $chip = $f['codigo_microchip'];
        } elseif (!empty($f['num_chip'])) {
            $chip = $f['num_chip'];
        } elseif (!empty($f['chip'])) {
            $chip = $f['chip'];
        }
        
        // 3. CONTROL DE VACUNA DE RABIA
        if (isset($f['vacuna_rabia'])) {
            $v = strtolower($f['vacuna_rabia']);
            $rabia = ($v == 'si' || $v == '1' || $v == 'sí' || strpos($v, 'inmunizado') !== false) ? "Sí" : $f['vacuna_rabia'];
        } elseif (isset($f['vacuna_de_rabia'])) {
            $v = strtolower($f['vacuna_de_rabia']);
            $rabia = ($v == 'si' || $v == '1' || $v == 'sí' || strpos($v, 'inmunizado') !== false) ? "Sí" : $f['vacuna_de_rabia'];
        }
        
        // 4. CONTROL DE DESPARASITACIÓN
        if (isset($f['desparasitacion'])) {
            $d = strtolower($f['desparasitacion']);
            $desparasitado = ($d == 'si' || $d == '1' || $d == 'sí' || strpos($d, 'al día') !== false) ? "Sí" : $f['desparasitacion'];
        } elseif (isset($f['desparasitado'])) {
            $d = strtolower($f['desparasitado']);
            $desparasitado = ($d == 'si' || $d == '1' || $d == 'sí') ? "Sí" : $f['desparasitado'];
        }
        
        // 5. CONTROL DE VACUNACIÓN (Fármaco, Fecha, Lote)
        if (!empty($f['farmaco_vacuna'])) $vacuna_nombre = $f['farmaco_vacuna'];
        elseif (!empty($f['vacuna_administrada'])) $vacuna_nombre = $f['vacuna_administrada'];
        elseif (!empty($f['vacuna_nombre'])) $vacuna_nombre = $f['vacuna_nombre'];
        
        if (!empty($f['fecha_aplicacion'])) $vacuna_fecha = $f['fecha_aplicacion'];
        elseif (!empty($f['vacuna_fecha'])) $vacuna_fecha = $f['vacuna_fecha'];
        
        if (!empty($f['codigo_de_lote'])) $vacuna_lote = $f['codigo_de_lote'];
        elseif (!empty($f['codigo_lote'])) $vacuna_lote = $f['codigo_lote'];
        elseif (!empty($f['vacuna_lote'])) $vacuna_lote = $f['vacuna_lote'];
        
        // 6. CONTROL DE OBSERVACIONES
        if (!empty($f['notas_veterinarias'])) $observaciones = $f['notas_veterinarias'];
        elseif (!empty($f['observaciones_clinicas'])) $observaciones = $f['observaciones_clinicas'];
        elseif (!empty($f['observaciones_medicas'])) $observaciones = $f['observaciones_medicas'];

    } else {
        echo "<script>alert('Mascota no encontrada.'); window.location.href='adopciones_de_animales.php';</script>";
        exit();
    }
} else {
    header("Location: adopciones_de_animales.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cartilla Veterinaria - <?php echo htmlspecialchars($nombre); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #fcf8fe;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 20px;
        }
        .cartilla-contenedor {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(155, 89, 182, 0.12);
            border: 2px solid #e9d5ff;
        }
        .cartilla-header {
            text-align: center;
            border-bottom: 2px solid #9b59b6;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .cartilla-header h2 { color: #8e44ad; margin: 0; font-size: 24px; }
        .seccion-titulo {
            font-weight: bold;
            color: #9b59b6;
            border-bottom: 1px dashed #ebdff2;
            padding-bottom: 5px;
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 17px;
        }
        .dato-linea { margin: 10px 0; font-size: 16px; color: #2c3e50; }
        .dato-linea b { color: #1e293b; }
        .cuadro-texto {
            background: #fdfaf0; 
            padding: 12px; 
            border-radius: 6px;
            border-left: 4px solid #9b59b6;
            margin-top: 5px;
            color: #555;
            font-size: 15px;
        }
        .btn-volver {
            display: inline-block;
            background: #9b59b6;
            color: white;
            padding: 10px 25px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 25px;
            transition: background 0.2s ease;
        }
        .btn-volver:hover { background: #8e44ad; }
        .text-center { text-align: center; }
    </style>
</head>
<body>

    <div class="cartilla-contenedor">
        <div class="cartilla-header">
            <h2>📋 Cartilla Veterinaria</h2>
            <p style="color: #7f8c8d; margin: 5px 0 0 0;">Historial Clínico de la Mascota</p>
        </div>

        <div class="seccion-titulo">🐾 Datos Identificativos</div>
        <div class="dato-linea"><b>Nombre:</b> <?php echo htmlspecialchars($nombre); ?></div>
        <div class="dato-linea"><b>Especie:</b> <?php echo htmlspecialchars($especie); ?></div>
        <div class="dato-linea"><b>Raza:</b> <?php echo htmlspecialchars($raza); ?></div>
        <div class="dato-linea"><b>Edad:</b> <?php echo htmlspecialchars($edad); ?> años</div>
        <div class="dato-linea"><b>Sexo:</b> <?php echo htmlspecialchars($sexo); ?></div>

        <div class="seccion-titulo">🩺 Identificación Sanitaria</div>
        <div class="dato-linea"><b>Número de Chip:</b> <?php echo htmlspecialchars($chip); ?></div>
        <div class="dato-linea"><b>Vacuna de la Rabia:</b> <?php echo htmlspecialchars($rabia); ?></div>
        <div class="dato-linea"><b>Desparasitado:</b> <?php echo htmlspecialchars($desparasitado); ?></div>

        <div class="seccion-titulo">💉 Registro de Vacunación</div>
        <div class="dato-linea"><b>Vacuna:</b> <?php echo htmlspecialchars($vacuna_nombre); ?></div>
        <div class="dato-linea"><b>Fecha de aplicación:</b> <?php echo htmlspecialchars($vacuna_fecha); ?></div>
        <div class="dato-linea"><b>Número de Lote:</b> <?php echo htmlspecialchars($vacuna_lote); ?></div>
        
        <div class="seccion-titulo">📝 Observaciones Clínicas</div>
        <div class="cuadro-texto"><?php echo nl2br(htmlspecialchars($observaciones)); ?></div>

        <div class="text-center">
            <a href="adopciones_de_animales.php" class="btn-volver">↩ Volver al Tablón</a>
        </div>
    </div>

</body>
</html>