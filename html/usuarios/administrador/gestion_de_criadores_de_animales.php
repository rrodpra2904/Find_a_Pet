<?php
// 1. Control de seguridad: Importo el archivo que verifica si el usuario es administrador.
// Pongo seguridad.php para que en caso de que no se haya iniciado sesión con un 
// usuario con rol administrador no pueda entrar por la URL a la página web de gestión 
// de criadores de animales sin que haya iniciado sesión como administrador.
include 'seguridad.php'; 

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
    
    <style>
        /* --- ESTILOS DE LOS FILTROS (ESTILO TABLÓN CON ENFOQUE MORADO) --- */
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

        /* EFECTO DE COLOR MORADO AL HACER CLIC EN LOS FILTROS */
        .contenedor-filtros input:focus {
            border-color: #8a2be2 !important;
            box-shadow: 0 0 8px rgba(138, 43, 226, 0.25) !important;
        }

        /* --- CONTROL ESTRICTO DE LA TABLA --- */
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

        /* AJUSTE PARA EL LOGO */
        .foto-logo {
            width: 80px !important;
            height: 80px !important;
            object-fit: cover !important;
            border-radius: 8px !important;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            display: block;
            margin: 0 auto;
        }

        /* ALINEACIÓN DE LA COLUMNA DE INFORMACIÓN */
        .col-info {
            text-align: left !important;
            line-height: 1.5;
            font-size: 14px;
        }

        /* --- BOTONES GRANDES Y BIEN DISTRIBUIDOS --- */
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

<div class="caja-principal">
    <h2>Gestión de Criadores Verificados</h2>

    <div class="contenedor-filtros">
        <input type="text" id="filtroNombre" placeholder="Buscar por criador..." onkeyup="filtrarCriadores()">
        <input type="text" id="filtroLocalidad" placeholder="📍 Buscar por localidad..." onkeyup="filtrarCriadores()">
        <input type="text" id="filtroRaza" placeholder="🐾 Buscar por raza..." onkeyup="filtrarCriadores()">
    </div>

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
                <td><?php echo $fila['id']; ?></td>
                
                <td>
                    <img src="../imagenes/criadores_de_animales/<?php echo $fila['logo_del_criador']; ?>" class="foto-logo">
                </td>
                
                <td class="celda-nombre">
                    <span style="font-size: 16px; font-weight: 600;"><?php echo $fila['nombre_del_criador']; ?></span><br>
                    <small style="color: #888;"><?php echo $fila['telefono']; ?></small>
                </td>
                
                <td class="celda-localidad">
                    <span class="ciudad"><?php echo $fila['localidad']; ?></span>
                </td>
                
                <td class="celda-raza">
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
                    <div class="celda-botones">
                        <a href="../../criadores_de_animales/editar_criadores_de_animales.php?id=<?php echo $fila['id']; ?>" class="btn-accion btn-modificar">Modificar</a>
                        
                        <a href="../../criadores_de_animales/eliminar_criador.php?id=<?php echo $fila['id']; ?>" 
                           class="btn-accion btn-eliminar" 
                           onclick="return confirm('¿Estás seguro de eliminar este criador?')">
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