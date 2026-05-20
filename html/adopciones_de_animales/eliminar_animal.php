<?php 

// 1. Control de acceso y seguridad

// Primero incluyo el archivo de seguridad para que nadie entre a esta página web sin loguearse.
include 'seguridad.php'; 

// Incluyo el archivo de conexión que usa PDO para interactuar con la base de datos de forma segura.
include '../adopciones_de_animales/conexion.php';

// 2. Comprobación del id del animal

// Recojo el ID del animal que se quiere borrar desde la URL.
// Uso $_GET porque el dato viene de un enlace (el icono de la papelera en la tabla).
if (isset($_GET['id'])) {
    
    $id_borrar = $_GET['id'];
    $rol_usuario = $_SESSION['rol']; // Recupero el rol del usuario que tiene la sesión iniciada.

    // --- Bloqueo de seguridad según el rol (gestión de permisos) ---
    
    /* Lógica especial:
       Si el usuario es un 'empleado', aplico una restricción:
       Solo puede borrar animales que ya estén adoptados. Los animales disponibles 
       solo los puede borrar el administrador.
    */
    if ($rol_usuario == 'empleado') {
        
        // Consulto el estado actual del animal en la base de datos antes de borrar nada.
        $consulta = $db->prepare("SELECT adoptado FROM animales WHERE id = :id");
        $consulta->bindParam(':id', $id_borrar);
        $consulta->execute();
        $animal = $consulta->fetch(PDO::FETCH_ASSOC);

        // Si el animal no existe o el campo 'adoptado' NO es 'si', bloqueo el borrado.
        // Uso trim y strtolower para que la comparación sea exacta aunque haya mayúsculas o espacios.
        if (!$animal || trim(strtolower($animal['adoptado'])) !== 'si') {
            
            // Si el empleado intenta borrar un animal no adoptado, le muestro una pantalla con un aviso de error.
            echo "
            <!DOCTYPE html>
            <html lang='es'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;900&display=swap' rel='stylesheet'>
                <style>
                    /* Estilos para que el mensaje de error se vea profesional y amigable */
                    body { 
                        font-family: 'Poppins', sans-serif;
                        margin: 0; padding: 0; background-color: #fafafa;
                    }
                    .fondo-formulario {
                        min-height: 100vh;
                        display: flex; align-items: center; justify-content: center;
                        padding: 20px; box-sizing: border-box;
                    }
                    .caja-formulario {
                        background: #ffffff;
                        width: 100%; max-width: 550px; padding: 40px; 
                        border-radius: 15px; border: 3px solid #9b59b6; 
                        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                        text-align: center;
                    }
                    .icono-prohibido { font-size: 60px; display: block; margin-bottom: 15px; }
                    .caja-formulario h2 { color: #2c2c2c; font-size: 28px; font-weight: 900; margin-bottom: 20px; }
                    .texto-secundario { color: #444; font-size: 17px; line-height: 1.5; margin-bottom: 30px; }
                    .boton {
                        display: block; width: 100%; background-color: #9b59b6;
                        color: white; padding: 15px; border-radius: 30px;
                        font-weight: 600; text-decoration: none; cursor: pointer;
                    }
                    .boton:hover { background-color: #834ca0; }
                </style>
            </head>
            <body class='fondo-formulario'>
                <div class='caja-formulario'>
                    <span class='icono-prohibido'>🚫</span>
                    <h2>Acción no permitida</h2>
                    <p class='texto-secundario'>Como empleado, solo puedes eliminar registros de animales que ya han sido adoptados. Esta función está restringida para el resto de casos por seguridad.</p>
                    <a href='../administrador/gestion_de_adopciones_de_animales.php' class='boton'>Volver a la gestión</a>
                </div>
            </body>
            </html>";
            
            // Cortamos la ejecución aquí para evitar que el script llegue al proceso de borrado.
            exit(); 
        }
    }

    // --- 3. Proceso de borrado final ---

    // Si el usuario tiene permisos (es Admin o es Empleado y el animal está adoptado),
    // preparo la consulta SQL usando marcadores (:id) para evitar inyecciones SQL.
    $sql = "DELETE FROM animales WHERE id = :id";
    $sentencia = $db->prepare($sql);
    
    // Vinculo el ID recogido al marcador de la consulta para procesarlo de forma segura.
    $sentencia->bindParam(':id', $id_borrar);

    // Ejecuto el borrado definitivo en la base de datos.
    if ($sentencia->execute()) {
        
        // Si el borrado tiene éxito, redirijo al usuario al panel de gestión principal.
        header("Location: ../administrador/gestion_de_adopciones_de_animales.php");
        exit();
        
    } else {
        // Mensaje de aviso por si ocurre algún fallo inesperado en la base de datos.
        echo "Lo siento, ha habido un error técnico y no se ha podido borrar el registro.";
    }
}
?>