<?php
session_start();
require_once 'vendor/autoload.php';

if (!isset($_SESSION['videoclub'])) {
    $vc = new Dwes\ProyectoVideoclub\Videoclub("Severo 8A");
    $_SESSION['videoclub'] = serialize($vc);
}
$videoclub = unserialize($_SESSION['videoclub']);

$nombre = trim($_POST['nombre'] ?? '');
$usuario = trim($_POST['usuario'] ?? '');
$contrasena = trim($_POST['contrasena'] ?? '');

if ($nombre === '' || $usuario === '' || $contrasena === '') {
    $_SESSION['error_create'] = "Todos los campos son obligatorios.";
    header('Location: formCreateCliente.php');
    exit;
}

// Comprobar si el usuario ya existe
foreach ($videoclub->socios as $cliente) {
    if ($cliente->getUsuario() === $usuario) {
        $_SESSION['error_create'] = "El usuario ya existe.";
        header('Location: formCreateCliente.php');
        exit;
    }
}

// Crear cliente y añadirlo
$videoclub->incluirSocio($nombre, $usuario, $contrasena);
$_SESSION['videoclub'] = serialize($videoclub);

header('Location: mainAdmin.php');
exit;