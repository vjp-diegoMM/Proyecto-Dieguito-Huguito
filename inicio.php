<?php
// Incluimos el archivo de autocarga de clases
require_once __DIR__ . '/vendor/autoload.php';

// Importamos las clases que vamos a utilizar
use Dwes\ProyectoVideoclub\CintaVideo;
use Dwes\ProyectoVideoclub\Juego;

// Creamos una nueva cinta de video con título "Tenet", id 22, precio 3.0 y duración 150
$soporte1 = new CintaVideo("Tenet", 22, 3.0, 150);

// Mostramos información de la cinta
echo "<strong>" . htmlspecialchars($soporte1->getTitulo()) . "</strong><br>";
echo "Precio: " . $soporte1->getPrecio() . " euros<br>";
echo "Precio IVA incluido: " . $soporte1->getPrecioConIVA() . " euros<br>";
echo "<pre>";
$soporte1->muestraResumen();
echo "</pre>";

// Creamos un nuevo juego con título, id, precio, consola, jugadores mínimos y máximos
$miJuego = new Juego("The Last of Us Part II", 26, 49.99, "PS4", 1, 1);

// Mostramos información del juego
echo "<strong>" . $miJuego->getTitulo() . "</strong>";
echo "<br>Precio: " . $miJuego->getPrecio() . " euros";
echo "<br>Precio IVA incluido: " . $miJuego->getPrecioConIVA() . " euros";
$miJuego->muestraResumen();
?>