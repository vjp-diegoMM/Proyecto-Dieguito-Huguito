<?php
session_start();

require_once 'autoload.php';
require_once 'app/Cliente.php';

use Dwes\ProyectoVideoclub\Cliente;

$clientes = $_SESSION['clientes'] ?? [];
$soportes = $_SESSION['soportes'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel Administrador</title>
</head>

<body>
    <h2>Bienvenido Administrador</h2>

    <h2>Listado de Clientes</h2>
    <ul>
        <?php foreach ($clientes as $cliente) :?>
            <li>
                <?php
                echo "Nombre: " . $cliente['nombre'];
                echo " | Usuario: " . $cliente['usuario'];
                ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <h3>Listado de Soportes</h3>
    <ul>
        <?php foreach ($soportes as $soporte): ?>
            <li><?php echo $soporte['titulo'] . ' (' . $soporte['tipo'] . ')'; ?></li>
        <?php endforeach; ?>
    </ul>

    <a href="index.php">Cerrar sesión</a>
</body>

</html>

