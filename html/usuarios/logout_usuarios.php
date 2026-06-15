<?php
// 1. Inicio la sesión para poder acceder a ella
session_start();

// 2. Destruyo todas las variables de sesión que existan
$_SESSION = array();

// 3. Si se desea destruir la sesión por completo, elimino también la cookie de sesión.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finalmente, destruyo la sesión en el servidor
session_destroy();

// 5. Redirijo al usuario a la pantalla de login principal
header("Location: login_usuarios.php");
exit();
?>