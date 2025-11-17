<?php
session_start();
require_once 'autoload.php';

$id = $_POST['id'] ?? null;
$nombre = trim($_POST['nombre'] ?? '');
$usuario = trim($_POST['usuario'] ?? '');
$contrasena = trim($_POST['contrasena'] ?? '');

if ($nombre === '' || $usuario === '' || $contrasena === '') {
    $_SESSION['error_update'] = "Todos los campos son obligatorios.";
    header("Location: formUpdateCliente.php?id=$id");
    exit;
}

$videoclub = unserialize($_SESSION['videoclub']);

// Buscar y actualizar cliente
foreach ($videoclub->socios as $cliente) {
    if ($cliente->getNumero() == $id) {
        $cliente->setNombre($nombre);
        $cliente->setUsuario($usuario);
        $cliente->setContrasena($contrasena);
        break;
    }
}
$_SESSION['videoclub'] = serialize($videoclub);

header('Location: mainAdmin.php');
exit;
?>
<style>
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f4f6f8;
    margin: 0;
    padding: 0;
}
h2 {
    color: #2c3e50;
    margin-top: 30px;
}
form {
    background: #fff;
    padding: 20px 30px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(44,62,80,0.08);
    max-width: 400px;
    margin: 30px auto;
}
input[type="text"], input[type="password"] {
    width: 95%;
    padding: 8px;
    margin: 8px 0 16px 0;
    border: 1px solid #ccc;
    border-radius: 4px;
}
input[type="submit"] {
    background: #2980b9;
    color: #fff;
    border: none;
    padding: 10px 18px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}
input[type="submit"]:hover {
    background: #3498db;
}
ul {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(44,62,80,0.08);
    max-width: 600px;
    margin: 30px auto;
    list-style: none;
}
li {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}
a {
    color: #2980b9;
    text-decoration: none;
    margin-left: 10px;
}
a:hover {
    text-decoration: underline;
}
.error {
    color: #e74c3c;
    background: #fff;
    border: 1px solid #e74c3c;
    padding: 10px;
    border-radius: 4px;
    max-width: 400px;
    margin: 20px auto;
    text-align: center;
}
</style>