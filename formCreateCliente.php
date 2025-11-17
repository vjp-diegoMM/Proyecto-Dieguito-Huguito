<?php
session_start();
?>
<h2>Alta de nuevo cliente</h2>
<form method="post" action="createCliente.php">
    Nombre: <input type="text" name="nombre" required><br>
    Usuario: <input type="text" name="usuario" required><br>
    Contraseña: <input type="password" name="contrasena" required><br>
    <input type="submit" value="Crear cliente">
</form>
<?php
echo "<head><link rel='stylesheet' href='estilos.css'></head>";
if (isset($_SESSION['error_create'])) {
    echo "<p style='color:red'>" . $_SESSION['error_create'] . "</p>";
    unset($_SESSION['error_create']);
}
?>