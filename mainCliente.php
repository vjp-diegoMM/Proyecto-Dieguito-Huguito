<?php
session_start();

if (!isset($_SESSION['usuario']) || !isset($_SESSION['cliente_actual'])) {
    header('Location: index.php');
    exit;
}

$cliente = $_SESSION['cliente_actual'];
$alquileres = $cliente->getAlquileres(); // Si es objeto Cliente
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Cliente</title>
</head>
<body>
    <h2>Bienvenido, <?php echo $cliente->getNombre(); ?>!</h2>

    <h3>Alquileres</h3>
    <ul>
        <?php foreach ($alquileres as $alquiler): ?>
            <li><?php echo $alquiler; ?></li>
        <?php endforeach; ?>
    </ul>

    <a href="index.php">Cerrar sesión</a>
</body>
</html>
