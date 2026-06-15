<?php
session_start();
include("../conexion_db.php");

// Verifico que el cliente esté logueado
if (!isset($_SESSION['usuario_id'])) {
    die("Error: Debes iniciar sesión para registrar una mascota.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_cliente = $_SESSION['usuario_id'];
    $nombre_animal = $_POST['nombre_animal'];
    $especie = $_POST['especie'];
    $raza = $_POST['raza'];
    $sexo = $_POST['sexo'];
    $edad = $_POST['edad'];
    $peso = $_POST['peso'];
    $esterilizado = $_POST['esterilizado'];
    $microchip = $_POST['microchip'];
    $ubicacion = $_POST['ubicacion'];
    $telefono = $_POST['telefono_contacto'];
    $vacunas = $_POST['cartilla_vacunas'];
    $desparasitacion = $_POST['desparasitacion'];
    $observaciones = $_POST['observaciones'];

    // Gestión de la FOTO
    $nombre_foto = $_FILES['foto_mascota']['name'];
    $ruta_temporal = $_FILES['foto_mascota']['tmp_name'];
    $carpeta_destino = "imagenes/mascotas/";
    
    // Creo un nombre único para la foto (ID_Cliente + Nombre original)
    $nombre_final_foto = $id_cliente . "_" . time() . "_" . $nombre_foto;
    $ruta_completa = $carpeta_destino . $nombre_final_foto;

    if (move_uploaded_file($ruta_temporal, $ruta_completa)) {
        // Si la foto se subió bien, lo guardo todo en la base de datos
        try {
            $sql = "INSERT INTO mascotas_clientes 
                    (id_cliente, nombre_animal, especie, raza, sexo, edad, peso, esterilizado, microchip, ubicacion, telefono_contacto, foto_mascota, cartilla_vacunas, desparasitacion, observaciones) 
                    VALUES 
                    (:id_c, :nom, :esp, :raza, :sexo, :edad, :peso, :est, :chip, :ubi, :tel, :foto, :vac, :desp, :obs)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id_c' => $id_cliente,
                ':nom'  => $nombre_animal,
                ':esp'  => $especie,
                ':raza' => $raza,
                ':sexo' => $sexo,
                ':edad' => $edad,
                ':peso' => $peso,
                ':est'  => $esterilizado,
                ':chip' => $microchip,
                ':ubi'  => $ubicacion,
                ':tel'  => $telefono,
                ':foto' => $ruta_completa, // Guardo la ruta para el <img>
                ':vac'  => $vacunas,
                ':desp' => $desparasitacion,
                ':obs'  => $observaciones
            ]);

            header("Location: mis_mascotas.php?mensaje=registrada");
        } catch (PDOException $e) {
            echo "Error al guardar: " . $e->getMessage();
        }
    } else {
        echo "Error: No se pudo subir la imagen. Revisa los permisos de la carpeta.";
    }
}
?>