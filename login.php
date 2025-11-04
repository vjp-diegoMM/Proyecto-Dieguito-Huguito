<?php
namespace Dwes\ProyectoVideoclub;
session_start();

$usuarios_validos = [
    'admin' => 'admin',
    'usuario' => 'usuario'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    if (isset($usuarios_validos['usuario']) && $usuarios_validos['usuario'] === $contrasena) {
        $_SESSION['usuario'] = $usuario;
        header('Location: main.php');
    } if (isset($usuarios_validos['admin']) && $usuarios_validos['admin'] === $contrasena) {
        // $_SESSION['usuario'] = $usuario;
        // header('Location: main.php');
        
    }else {
        header('Location: index.php?error=1');
    }
}