<?php
// Incluyo el archivo de conexión para poder conectarme con la base de datos de forma segura.
include("conexion_db.php");

/* 1. RECOGIDA Y LIMPIEZA DE DATOS:
   Recojo los datos que vienen del formulario mediante el método POST. Uso trim() para eliminar 
   espacios vacíos innecesarios al principio y al final, evitando que se guarden datos "sucios" 
   o con fallos de formato en la base de datos. */
$nombre      = trim($_POST['nombre']);
$apellidos   = trim($_POST['apellidos']);
$telefono    = trim($_POST['telefono']);
$email       = trim($_POST['email']);
$direccion   = trim($_POST['direccion']);
$tipo_animal = trim($_POST['tipo_animal']);

/* 2. ASIGNACIÓN AUTOMÁTICA DE ROL:
   Asigno el rol de 'cliente' de forma manual. Así, cada vez que alguien rellene este 
   compromiso de adopción, el sistema sabrá que es un cliente y no un administrador o empleado. */
$rol = 'cliente'; 

/* 3. PREPARACIÓN DE LA CONSULTA SQL:
   Preparo la estructura de la consulta INSERT. No incluyo el 'id' ni la 'fecha' 
   porque la base de datos está configurada para generarlos automáticamente (Auto-increment). */
$sql = "INSERT INTO formulario_de_compromiso_adopciones_de_animales 
        (nombre, apellidos, telefono, email, direccion, tipo_animal, rol) 
        VALUES (:nombre, :apellidos, :telefono, :email, :direccion, :tipo_animal, :rol)";

// Uso la variable de conexión $db para preparar la sentencia y mejorar la seguridad.
$sentencia = $db->prepare($sql);

/* 4. VINCULACIÓN DE VARIABLES:
   Utilizo bindParam para vincular las variables PHP con los marcadores de la consulta. 
   Este paso es vital para evitar ataques maliciosos que intenten manipular nuestra base de datos. */
$sentencia->bindParam(':nombre', $nombre);
$sentencia->bindParam(':apellidos', $apellidos);
$sentencia->bindParam(':telefono', $telefono);
$sentencia->bindParam(':email', $email);
$sentencia->bindParam(':direccion', $direccion);
$sentencia->bindParam(':tipo_animal', $tipo_animal);
$sentencia->bindParam(':rol', $rol);

/* 5. EJECUCIÓN Y REDIRECCIÓN:
   Ejecutamos la orden de inserción. Si todo sale bien, redirijoal usuario para 
   confirmar que el proceso ha terminado. */
if ($sentencia->execute()) {
    
    // Si se guarda correctamente, redirigimos a la página principal de la sección de adopciones.
    header("Location: ../adopciones_de_animales/adopciones_de_animales.php");
    exit(); 
    
} else {
    
    // Si ocurre un fallo técnico en el servidor o la base de datos, muestro un mensaje de aviso.
    echo "Algo ha fallado al guardar en la base de datos.";
}
?>