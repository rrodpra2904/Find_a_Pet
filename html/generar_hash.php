<?php
// Este archivo lo he hecho para que me genera mi contraseña en hash, y lo dejo aquí puesto para que
// podais entrar con mi usuario que es administrador y mi contraseña.
$myPassword = "45678987"; 

// Genero el hash de la contraseña que he puesto, para luego ponerlo en mi base de datos, de esta forma
// la contraseña del usuario no se vera en la tabla de usuario que he creado en la base de datos
// Find a Pet
echo password_hash($myPassword, PASSWORD_DEFAULT);
?>