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