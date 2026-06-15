<?php

include 'seguridad.php'; 

include '../criadores_de_animales/conexion.php'; 

 
$res = mysqli_query($conexion, "SELECT * FROM criadores_de_animales ORDER BY id DESC");


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
    
    <style>

        .contenedor-filtros {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .contenedor-filtros input {
            padding: 8px 12px;
            border: 2px solid #ccc;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            flex: 1;
            min-width: 150px;
            outline: none;
            transition: all 0.2s ease;
        }

  
        .contenedor-filtros input:focus {
            border-color: #8a2be2 !important;
            box-shadow: 0 0 8px rgba(138, 43, 226, 0.25) !important;
        }

   
        .caja-principal table {
            table-layout: fixed !important;
            width: 100% !important;
            border-collapse: collapse;
        }

        .caja-principal th, .caja-principal td {
            padding: 16px 10px !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            word-break: break-word !important;
            white-space: normal !important;
            vertical-align: middle !important;
            text-align: center !important;
        }

    
        .foto-logo {
            width: 80px !important;
            height: 80px !important;
            object-fit: cover !important;
            border-radius: 8px !important;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            display: block;
            margin: 0 auto;
        }

  
        .col-info {
            text-align: left !important;
            line-height: 1.5;
            font-size: 14px;
        }

    
        .celda-botones {
            display: flex !important;
            flex-direction: column !important;
            gap: 8px !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .celda-botones .btn-accion {
            display: block !important;
            width: 90% !important;
            box-sizing: border-box !important;
            padding: 10px 14px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            border-radius: 6px !important;
            text-decoration: none !important;
            text-align: center !important;
            margin: 0 auto !important;
        }
    </style>
</head>
<body>

<div class="caja-principal" style="font-family: 'Poppins', sans-serif;">
    <h2>Gestión de Criadores Verificados</h2><br>
    
    <p style="color: #556375; font-size: 15px; line-height: 1.6; margin-bottom: 25px; margin-top: -5px;">
        Bienvenido al panel de control de criadores para <strong>Find a Pet</strong>. Desde este espacio puedes supervisar los perfiles autorizados en la plataforma, aplicar filtros avanzados por zona o especialidad, dar de alta nuevos centros profesionales y mantener al día sus datos de contacto.
    </p><br>

    <div class="contenedor-filtros">
        <input type="text" id="filtroNombre" placeholder="Buscar por criador..." onkeyup="filtrarCriadores()">
        <input type="text" id="filtroLocalidad" placeholder="📍 Buscar por localidad..." onkeyup="filtrarCriadores()">
        <input type="text" id="filtroRaza" placeholder="🐾 Buscar por raza..." onkeyup="filtrarCriadores()">
    </div><br>

    <a href="../../criadores_de_animales/añadir.php" class="btn-nuevo">+ Nuevo Criador</a>

    <table id="tablaCriadores">
        <thead>
            <tr>
                <th style="width: 50px;">ID</th>
                <th style="width: 110px;">Logo</th>
                <th style="width: 18%;">Nombre / Teléfono</th>
                <th style="width: 14%;">Localidad</th>
                <th style="width: 14%;">Raza</th>
                <th style="width: 32%;">Información</th> 
                <th style="width: 140px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            
            <?php 
                 /* 5. Empiezo el bucle para ir mostrando por pantalla cada criador uno a uno en su fila correspondiente.
                    Uso mysqli_fetch_assoc para que me devuelva los datos con el nombre de la columna de la tabla. */
                 while ($fila = mysqli_fetch_assoc($res)) { 
            ?>
            <tr class="fila-criador">
                <td><?php echo htmlspecialchars($fila['id'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                
                <td>
                    <img src="../imagenes/criadores_de_animales/<?php echo htmlspecialchars($fila['logo_del_criador'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="foto-logo">
                </td>
                
                <td class="celda-nombre">
                    <span style="font-size: 16px; font-weight: 600;"><?php echo htmlspecialchars($fila['nombre_del_criador'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span><br>
                    <small style="color: #888;"><?php echo htmlspecialchars($fila['telefono'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                </td>
                
                <td class="celda-localidad">
                    <span class="ciudad"><?php echo htmlspecialchars($fila['localidad'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                </td>
                
                <td class="celda-raza">
                    <span class="etiqueta-raza"><?php echo htmlspecialchars($fila['raza_de_animal'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                </td>
                
                <td class="col-info">
                    <?php 
                         /* Combinamos nl2br junto con htmlspecialchars para procesar de forma segura 
                            los saltos de línea de las cajas de texto extensas */
                         echo nl2br(htmlspecialchars($fila['informacion_del_criador'] ?? '', ENT_QUOTES, 'UTF-8')); 
                    ?>
                </td>
                
                <td>
                    <div class="celda-botones">
                        <a href="../../criadores_de_animales/editar_criadores_de_animales.php?id=<?php echo urlencode($fila['id'] ?? ''); ?>" class="btn-accion btn-modificar">Modificar</a>
                        
                        <a href="../../criadores_de_animales/eliminar_criador.php?id=<?php echo urlencode($fila['id'] ?? ''); ?>" 
                           class="btn-accion btn-eliminar" 
                           onclick="return confirm('¿Estás seguro de eliminar al criador <?php echo addslashes(htmlspecialchars($fila['nombre_del_criador'] ?? '', ENT_QUOTES, 'UTF-8')); ?>?')">
                           Eliminar
                        </a>
                    </div>
                </td>
            </tr>
            <?php } // 6. Aquí cierro el bucle while una vez que se han pintado todos los registros ?>
        </tbody>
    </table>
</div>

    <script>
    function filtrarCriadores() {
        var inputNombre = document.getElementById("filtroNombre").value.toUpperCase();
        var inputLocalidad = document.getElementById("filtroLocalidad").value.toUpperCase();
        var inputRaza = document.getElementById("filtroRaza").value.toUpperCase();
        
        var tabla = document.getElementById("tablaCriadores");
        var filas = tabla.getElementsByClassName("fila-criador");

        for (var i = 0; i < filas.length; i++) {
            var celdaNombre = filas[i].getElementsByClassName("celda-nombre")[0].textContent.toUpperCase();
            var celdaLocalidad = filas[i].getElementsByClassName("celda-localidad")[0].textContent.toUpperCase();
            var celdaRaza = filas[i].getElementsByClassName("celda-raza")[0].textContent.toUpperCase();

            // Comprobación de filtros simultáneos
            var coincideNombre = celdaNombre.indexOf(inputNombre) > -1;
            var coincideLocalidad = celdaLocalidad.indexOf(inputLocalidad) > -1;
            var coincideRaza = celdaRaza.indexOf(inputRaza) > -1;

            if (coincideNombre && coincideLocalidad && coincideRaza) {
                filas[i].style.display = "";
            } else {
                filas[i].style.display = "none";
            }
        }
    }
    </script>
       
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.pathname);
        }
    </script>
</body>
</html>