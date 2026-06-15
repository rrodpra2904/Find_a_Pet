<?php 

include 'seguridad.php'; 
?>

<?php 

include '../adopciones_de_animales/conexion.php'; 


$res = mysqli_query($conexion, "SELECT * FROM animales ORDER BY id DESC");

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

        .contenedor-filtros input, .contenedor-filtros select {
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

        .contenedor-filtros input:focus, .contenedor-filtros select:focus {
            border-color: #8a2be2 !important; 
            box-shadow: 0 0 8px rgba(138, 43, 226, 0.2) !important; 
        }

    
        .div-table table {
            table-layout: fixed !important;
            width: 100% !important;
            border-collapse: collapse;
        }

        .div-table th, .div-table td {
            padding: 18px 10px !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            word-break: break-word !important;
            white-space: normal !important;
            vertical-align: middle !important;
            text-align: center !important;
        }

  
        .img-animal {
            width: 110px !important;
            height: 110px !important;
            object-fit: cover !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            display: block;
            margin: 0 auto;
        }

        .texto-desc {
            text-align: left !important;
            line-height: 1.5;
            font-size: 14px;
        }

   
        .celda-botones {
            display: flex !important;
            flex-direction: column !important; 
            gap: 10px !important;              
            align-items: center !important;
            justify-content: center !important;
            border: none !important;
        }

        .celda-botones .boton-accion {
            display: block !important;
            width: 85% !important;             
            box-sizing: border-box !important;
            padding: 12px 18px !important;     
            font-size: 14px !important;
            font-weight: 600 !important;
            border-radius: 6px !important;
            text-decoration: none !important;
            text-align: center !important;
            margin: 0 auto !important;         
        }
    </style>
</head>
<body>

<div class="contenedor-principal" style="font-family: 'Poppins', sans-serif;">
    <h2>Panel de Gestión de Animales</h2>
    
    <p style="color: #556375; font-size: 15px; line-height: 1.6; margin-bottom: 25px; margin-top: -5px;">
        Bienvenido al panel interno de control de <strong>Find a Pet</strong>. Desde aquí dispones de herramientas avanzadas para revisar el listado completo de mascotas registradas, filtrar los datos en tiempo real, actualizar expedientes de adopción o dar de alta nuevos animales en la plataforma de manera segura.
    </p>

    <div class="contenedor-filtros">
        <input type="text" id="filtroNombre" placeholder="Buscar por nombre..." onkeyup="filtrarTabla()">
        <input type="text" id="filtroRaza" placeholder="Buscar por raza..." onkeyup="filtrarTabla()">
        <input type="number" id="filtroEdad" placeholder="Buscar por edad..." onchange="filtrarTabla()" onkeyup="filtrarTabla()">
        <select id="filtroAdoptado" onchange="filtrarTabla()">
            <option value="">¿Adoptado? (Todos)</option>
            <option value="Si">Sí</option>
            <option value="No">No</option>
        </select>
    </div>

    <a href="../adopciones_de_animales/añadir.php" class="boton-nuevo">Añadir un animal</a>

    <div class="div-table">
        <table id="tablaAnimales">
            <thead>
                <tr>
                    <th style="width: 140px;">Imagen</th>
                    <th style="width: 12%;">Nombre</th>
                    <th style="width: 12%;">Raza</th>
                    <th style="width: 10%;">Edad</th>
                    <th style="width: 34%;">Descripción</th>
                    <th style="width: 12%;">Adopción</th>
                    <th style="width: 160px;">Acciones</th>
                </tr>
            </thead>
            
            <tbody>
                <?php 

                    while ($row = mysqli_fetch_assoc($res)) { 
                        
  
                        $foto = $row['imagen'];
                        $ruta_admin = "../imagenes/adopciones_de_animales/" . $foto;
                        $ruta_usuarios = "../usuarios/imagenes/animales/" . $foto;
                        
                        if (!empty($foto) && file_exists($ruta_admin)) {
                            $ruta_final = $ruta_admin;
                        } else {
                            $ruta_final = $ruta_usuarios;
                        }
                       
                ?>
                <tr class="fila-animal">
                    <td>
                        <img src="<?php echo htmlspecialchars($ruta_final); ?>" class="img-animal">
                    </td>
                    
                    <td class="celda-nombre"><strong><?php echo htmlspecialchars($row['nombre'] ?? ''); ?></strong></td>
                    <td class="celda-raza"><?php echo htmlspecialchars($row['raza'] ?? ''); ?></td>
                    <td class="celda-edad"><?php echo htmlspecialchars($row['edad'] ?? ''); ?> años</td>
                    
                    <td class="texto-desc"><?php echo nl2br(htmlspecialchars($row['descripcion'] ?? '')); ?></td>
                    
                    <td class="celda-adoptado">
                        <span style="font-weight: bold; color: <?php echo ($row['adoptado'] == 'Si') ? '#01923e' : '#ad5605'; ?>;">
                            <?php echo htmlspecialchars($row['adoptado'] ?? ''); ?>
                        </span>
                    </td>
                    
                    <td>
                        <div class="celda-botones">
                            <a href="../adopciones_de_animales/editar_animal.php?id=<?php echo urlencode($row['id']); ?>" class="boton-accion boton-editar">Modificar</a>
                            
                            <a href="../adopciones_de_animales/eliminar_animal.php?id=<?php echo urlencode($row['id']); ?>" 
                               class="boton-accion boton-borrar" 
                               onclick="return confirm('¿Seguro que quieres borrar a <?php echo addslashes(htmlspecialchars($row['nombre'] ?? '')); ?>?')">Eliminar</a>
                        </div>
                    </td>
                </tr>
                <?php }  ?>
            </tbody>
        </table>
    </div>
</div>

    <script>
    function filtrarTabla() {
        var inputNombre = document.getElementById("filtroNombre").value.toUpperCase();
        var inputRaza = document.getElementById("filtroRaza").value.toUpperCase();
        var inputEdad = document.getElementById("filtroEdad").value;
        var inputAdoptado = document.getElementById("filtroAdoptado").value.toUpperCase();
        
        var tabla = document.getElementById("tablaAnimales");
        var filas = tabla.getElementsByClassName("fila-animal");

        for (var i = 0; i < filas.length; i++) {
            var celdaNombre = filas[i].getElementsByClassName("celda-nombre")[0].textContent.toUpperCase();
            var celdaRaza = filas[i].getElementsByClassName("celda-raza")[0].textContent.toUpperCase();
            var celdaEdad = filas[i].getElementsByClassName("celda-edad")[0].textContent.toUpperCase();
            var celdaAdoptado = filas[i].getElementsByClassName("celda-adoptado")[0].textContent.toUpperCase();

            var coincideNombre = celdaNombre.indexOf(inputNombre) > -1;
            var coincideRaza = celdaRaza.indexOf(inputRaza) > -1;
            var coincideEdad = inputEdad === "" || celdaEdad.indexOf(inputEdad) > -1;
            var coincideAdoptado = inputAdoptado === "" || celdaAdoptado.trim() === inputAdoptado;

            if (coincideNombre && coincideRaza && coincideEdad && coincideAdoptado) {
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