<?php
require_once 'autoload.php';

use Dwes\ProyectoVideoclub\Videoclub;

$vc = new Videoclub("Severo 8A");

// Incluir productos (Videoclub asigna los números internamente)
$vc->incluirJuego("God of War", 19.99, "PS4", 1, 1);
$vc->incluirJuego("The Last of Us Part II", 49.99, "PS4", 1, 1);
$vc->incluirDvd("Torrente", 4.5, "es", "16:9");
$vc->incluirDvd("Origen", 4.5, "es,en,fr", "16:9");
$vc->incluirDvd("El Imperio Contraataca", 3, "es,en", "16:9");
$vc->incluirCintaVideo("Los cazafantasmas", 3.5, 107);
$vc->incluirCintaVideo("El nombre de la Rosa", 1.5, 140);

// Listar productos (mostrar en bloque pre para conservar saltos)
echo "<h3>Productos:</h3><pre>";
$vc->listarProductos();
echo "</pre>";

// // Crear socios
// $vc->incluirSocio("Amancio Ortega");
// $vc->incluirSocio("Pablo Picasso", 2);

try {
    $vc->alquilaSocioProducto(2, 2)
    ->alquilaSocioProducto(1, 3)
    ->alquilaSocioProducto(1, 2)
    ->alquilaSocioProducto(1, 6);
} catch (\Exception $e) {
    echo "<br>Error: " . htmlspecialchars($e->getMessage()) . "<br>";
}

// Listar socios y sus resúmenes
echo "<h3>Socios:</h3><pre>";
$vc->listarSocios();
echo "</pre>";

// Mostrar contadores (opcional)
echo "<br>Productos alquilados: " . $vc->getNumProductosAlquilados();
echo "<br>Total alquileres realizados: " . $vc->getNumTotalAlquileres();