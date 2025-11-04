<?php
session_start();

$usuarios_validos = [
    'admin' => 'admin',
    'usuario' => 'usuario'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    if (isset($usuarios_validos[$usuario]) && $usuarios_validos[$usuario] === $contrasena) {
        $_SESSION['usuario'] = $usuario;
        header('Location: main.php');
    } else {
        header('Location: index.php?error=1');
    }
} else {
    header('Location: index.php');
}