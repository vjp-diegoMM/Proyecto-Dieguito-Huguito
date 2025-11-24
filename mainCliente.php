<?php
session_start();
require_once 'vendor/autoload.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

$cliente = unserialize($_SESSION['usuario']);

echo "<h2>Bienvenido, " . htmlspecialchars($cliente->getNombre()) . "</h2>";
echo "<h3>Alquileres:</h3>";
echo "<ul>";
foreach ($cliente->getAlquileres() as $alquiler) {
    echo "<li>" . htmlspecialchars($alquiler->getTitulo()) . " (Nº " . $alquiler->getNumero() . ")</li>";
}
echo "</ul>";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel Cliente</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>
    <h2>Bienvenido, <?php echo $cliente->getNombre(); ?>!</h2>

    <h3>Alquileres</h3>
    <ul>
        <?php foreach ($alquileres as $alquiler): ?>
            <li><?php echo $alquiler; ?></li>
        <?php endforeach; ?>
    </ul>
    <h3>Alquileres de <?php echo $cliente->getNombre(); ?>:</h3>
    <?php
    $alquileres = $cliente->getAlquileres();
    foreach ($alquileres as $alquiler) {
        echo "Soporte: " . $alquiler->getTitulo() . " (Nº " . $alquiler->getNumero() . ")<br>";
    }
    ?>

    <h3>Información del Cliente</h3>
    <?php
    foreach ($videoclub->socios as $cliente) {
        echo "Nombre: " . $cliente->getNombre();
        echo " | Usuario: " . $cliente->getUsuario();
        echo "<br>";
    }
    ?>

    <a href="index.php">Cerrar sesión</a>
</body>

</html>