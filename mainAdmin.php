<?php
session_start();

require_once 'app/Cliente.php';
use Dwes\ProyectoVideoclub\Cliente;

if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] !== 'admin') {
    header('Location: index.php');
    exit;
}

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

    <h3>Listado de Clientes</h3>
    <ul>
        <?php foreach ($clientes as $cliente): ?>
            <li>
                <?php
                echo $cliente->getNombre() . ' - Usuario: ' . $cliente->getUsuario();
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
