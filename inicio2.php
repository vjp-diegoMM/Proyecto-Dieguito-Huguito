<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dwes\ProyectoVideoclub\CintaVideo;
use Dwes\ProyectoVideoclub\Dvd;
use Dwes\ProyectoVideoclub\Juego;
use Dwes\ProyectoVideoclub\Cliente;

// Crear clientes
$cliente1 = new Cliente("Bruce Wayne", 23);
$cliente2 = new Cliente("Clark Kent", 33);

echo "ID cliente1: " . $cliente1->getNumero() . "<br>";
echo "ID cliente2: " . $cliente2->getNumero() . "<br><br>";

// Crear soportes concretos
$soporte1 = new CintaVideo("Los cazafantasmas", 1, 3.5, 107);
$soporte2 = new Juego("The Last of Us Part II", 2, 49.99, "PS4", 1, 1);
$soporte3 = new Dvd("Origen", 3, 4.5, "es,en,fr", "16:9");

// Alquilar con encadenamiento; capturar excepciones que lancen los métodos
try {
    $cliente1->alquilar($soporte1)
        ->alquilar($soporte2)
        ->alquilar($soporte3);
    echo "Alquileres realizados correctamente.<br>";
} catch (\Exception $e) {
    echo "Error al alquilar: " . htmlspecialchars($e->getMessage()) . "<br>";
}

echo "<br>Alquileres actuales del cliente1:<br>";
echo "<pre>";
$cliente1->listarAlquileres();
echo "</pre>";

// Devolver un soporte y mostrar estado
try {
    $cliente1->devolver(2);
    echo "<br>Devolución realizada.<br>";
} catch (\Exception $e) {
    echo "Error al devolver: " . htmlspecialchars($e->getMessage()) . "<br>";
}

echo "<br>Alquileres tras devolución:<br>";
echo "<pre>";
$cliente1->listarAlquileres();
echo "</pre>";
