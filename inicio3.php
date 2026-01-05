<?php
require_once 'vendor/autoload.php';

use Dwes\ProyectoVideoclub\Videoclub;

$vc = new Videoclub("Severo 8A");

// Ejemplos de URLs de Metacritic (búscalas manualmente y ajusta si es necesario)
$vc->incluirJuego('https://www.metacritic.com/game/playstation-4/god-of-war', "God of War", 19.99, "PS4", 1, 1);
$vc->incluirJuego('https://www.metacritic.com/game/playstation-4/the-last-of-us-part-ii', "The Last of Us Part II", 49.99, "PS4", 1, 1);
$vc->incluirDvd('https://www.metacritic.com/movie/torrente', "Torrente", 4.5, "es", "16:9");
$vc->incluirDvd('https://www.metacritic.com/movie/inception', "Origen", 4.5, "es,en,fr", "16:9");
$vc->incluirDvd('https://www.metacritic.com/movie/the-empire-strikes-back', "El Imperio Contraataca", 3, "es,en", "16:9");
$vc->incluirCintaVideo('https://www.metacritic.com/movie/ghostbusters', "Los cazafantasmas", 3.5, 107);
$vc->incluirCintaVideo('https://www.metacritic.com/movie/the-name-of-the-rose', "El nombre de la Rosa", 1.5, 140);

// crear socio y realizar alquileres de ejemplo
$vc->incluirSocio('Juan Pérez', 'juan', 'secreto', 5);
$vc->alquilaSocioProducto(1, 1);
$vc->alquilaSocioProducto(1, 2);

// listar productos y socios
echo "<h2>Productos</h2>";
$vc->listarProductos();

echo "<h2>Socios</h2>";
$vc->listarSocios();

// después de los alquileres: mostrar, para un socio concreto, sus alquileres con la puntuación de Metacritic
$socioIdx = 1; // ajustar si es necesario
$cliente = $vc->socios[$socioIdx - 1] ?? null;
if ($cliente) {
    echo "<h3>Alquileres del socio " . htmlspecialchars($cliente->getNombre()) . ":</h3><ul>";
    foreach ($cliente->getAlquileres() as $al) {
        $titulo = method_exists($al, 'getTitulo') ? $al->getTitulo() : 'Sin título';
        $puntuacion = null;
        if (method_exists($al, 'getPuntuacion')) {
            $puntuacion = $al->getPuntuacion();
        }
        echo "<li>" . htmlspecialchars($titulo) . " - Puntuación Metacritic: " . ($puntuacion !== null ? htmlspecialchars((string)$puntuacion) : "N/D") . "</li>";
    }
    echo "</ul>";
}