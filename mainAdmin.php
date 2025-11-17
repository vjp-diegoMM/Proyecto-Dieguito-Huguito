<?php
// ...al principio de cada archivo que use videoclub...
session_start();
require_once 'autoload.php';

if (!isset($_SESSION['videoclub'])) {
    $vc = new Dwes\ProyectoVideoclub\Videoclub("Severo 8A");
    // Puedes añadir algunos socios de ejemplo si quieres:
    $vc->incluirSocio("Amancio Ortega", "amancio", "usuario");
    $vc->incluirSocio("Pablo Picasso", "pablo", "usuario");
    $_SESSION['videoclub'] = serialize($vc);
}
$videoclub = unserialize($_SESSION['videoclub']);
echo "<head><link rel='stylesheet' href='estilos.css'></head>";
echo "<h2>Listado de Clientes</h2>";
echo "<ul>";
foreach ($videoclub->socios as $cliente) {
    echo "<li>";
    echo "Nombre: " . htmlspecialchars($cliente->getNombre());
    echo " | Usuario: " . htmlspecialchars($cliente->getUsuario());
    echo " <a href='formUpdateCliente.php?id=" . $cliente->getNumero() . "'>Editar</a>";
    echo " <a href='removeCliente.php?id=" . $cliente->getNumero() . "' onclick=\"return confirm('¿Seguro que deseas eliminar este cliente?');\">Borrar</a>";
    echo "</li>";
}
echo "</ul>";
echo "<a href='formCreateCliente.php'>Crear nuevo cliente</a>";
?>

