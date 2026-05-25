<?php
// 1. Inicio la sesión para poder guardar los datos del usuario si el login es correcto, 
// o los mensajes de error si algo falla.
session_start(); 

// 2. Cargo el archivo que contiene la conexión a la base de datos (objeto $db).
require 'conexion.php'; 

try {
    /* 3. Recogida de datos:
       Recojo el usuario y la contraseña del formulario. 
       Si no llegan, les asigno un valor vacío para evitar errores de PHP. */
    $usuario = $_POST['user'] ?? "";
    $password = $_POST['password'] ?? "";

    /* 4. Validación inicial:
       Si alguno de los campos está vacío, marco errores en la sesión y 
       devuelvo al usuario al login. */
    if ($usuario == "" || $password == "") {
        $_SESSION['errorUsuarioNoExiste'] = "si";
        $_SESSION['errorPasswordIncorrecto'] = "si";
        if ($usuario != "") { $_SESSION['nombreUsuarioTemporal'] = $usuario; }
        header("Location: login.php");
        exit();
    }

    /* 5. Consulta segura (uso de bindParam):
       Preparo la consulta SQL con un marcador (:user) para evitar inyecciones SQL. 
       Es la forma más segura de consultar bases de datos. */
    $sql = "SELECT * FROM usuarios WHERE user = :user";
    $sentencia = $db->prepare($sql); 
    
    // Vinculo la variable PHP con el marcador de la consulta.
    $sentencia->bindParam(':user', $usuario);
    $sentencia->execute();

    // Recupero los datos del usuario encontrado.
    $fila = $sentencia->fetch(PDO::FETCH_ASSOC);

    /* 6. Verificación del usuario:
       Si la consulta no devuelve ninguna fila, es que el nombre de usuario no existe. */
    if (!$fila) {
        $_SESSION['errorUsuarioNoExiste'] = "si";
        $_SESSION['errorPasswordIncorrecto'] = "si";
        header("Location: login.php");
        exit();
    }

    /* 7. Verificación de la contraseña:
       Uso password_verify para comparar la contraseña escrita con el hash 
       encriptado que tengo guardado en la base de datos. */
    if (!password_verify($password, $fila['password'])) {
        $_SESSION['errorPasswordIncorrecto'] = "si";
        $_SESSION['nombreUsuarioTemporal'] = $usuario; // Guardamos el nombre para que no tenga que escribirlo de nuevo.
        header("Location: login.php");
        exit();
    }

    /* 8. Éxito en el login:
       Si se llega aquí, el usuario es válido. Guardo su nombre y su rol en la sesión. */
    $_SESSION['usuarioAutenticado'] = $fila['user'];
    $_SESSION['rol'] = $fila['rol'];

    /* 9. Redirección según el rol:
       Dependiendo de si es 'empleado' o 'administrador', lo mando a un panel o a otro panel. */
    if ($_SESSION['rol'] == 'empleado') {
        header("Location: panel_empleado.php");
    } else {
        header("Location: panel.php");
    }
    exit();

} catch (PDOException $e) {
    /* 10. Control de errores:
       Si hay un problema con la base de datos, el programa se detiene y muestra el fallo. */
    die("Error de conexión: " . $e->getMessage());
}
?>