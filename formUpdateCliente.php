<?php
session_start();
require_once 'autoload.php';

$videoclub = unserialize($_SESSION['videoclub']);
$id = $_GET['id'] ?? null;
$cliente = null;
foreach ($videoclub->socios as $c) {
    if ($c->getNumero() == $id) {
        $cliente = $c;
        break;
    }
}
if (!$cliente) {
    echo "Cliente no encontrado.";
    exit;
}
?>
<h2>Editar cliente</h2>
<form method="post" action="updateCliente.php">
    <input type="hidden" name="id" value="<?= htmlspecialchars($cliente->getNumero()) ?>">
    Nombre: <input type="text" name="nombre" value="<?= htmlspecialchars($cliente->getNombre()) ?>" required><br>
    Usuario: <input type="text" name="usuario" value="<?= htmlspecialchars($cliente->getUsuario()) ?>" required><br>
    Contraseña: <input type="password" name="contrasena" value="<?= htmlspecialchars($cliente->getContrasena()) ?>" required><br>
    <input type="submit" value="Actualizar cliente">
</form>
<?php
if (isset($_SESSION['error_update'])) {
    echo "<p style='color:red'>" . $_SESSION['error_update'] . "</p>";
    unset($_SESSION['error_update']);
}
?>