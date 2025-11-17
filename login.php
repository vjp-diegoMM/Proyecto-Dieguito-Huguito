<?php

namespace Dwes\ProyectoVideoclub;

session_start();

$usuarios_validos = [
    'usuario' => 'usuario',
    'admin' => 'admin'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    // //Nacho
    // $_SESSION['usuario'] = $usuario;

    if ($usuarios_validos['admin'] === $contrasena && $usuarios_validos['admin'] === $usuario) {

        $clientes = [
            [
                'nombre' => 'Hugo',
                'usuario' => 'Huguito'
            ],
            [
                'nombre' => 'Diego',
                'usuario' => 'Dieguito'
            ]
        ];

        $soportes = [
            ['titulo' => 'Pelicula A', 'tipo' => 'DVD'],
            ['titulo' => 'Pelicula B', 'tipo' => 'BluRay']
        ];

        $_SESSION['clientes'] = $clientes;
        $_SESSION['soportes'] = $soportes;

        header('Location: mainAdmin.php');
        exit;
    }

    $usuario_bien = false;
    if (isset($usuarios_validos[$usuario]) && $usuarios_validos[$usuario] === $contrasena) {
        $usuario_bien = true;
        header('Location: main.php');
        exit;
    }
    
    if ($usuario_bien) {
        header('Location: main.php');
        exit;
    } else {
        header('Location: index.php?error=1');
    }

    // if ($usuarios_validos[$usuario] === $contrasena && $usuarios_validos['usuario'] === $usuario) {
    //     $_SESSION['usuario'] = $usuario;
    //     header('Location: mainCliente.php');
    //     exit;
    // } else {
    //     header('Location: index.php?error=1');
    // }
}
