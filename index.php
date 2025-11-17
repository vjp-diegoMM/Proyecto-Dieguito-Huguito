<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio de sesión</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <?php
    if (isset($_GET['error'])) {
        echo '<p style="color:red;font-weight:bold;">Usuario o contraseña incorrectos.</p>';
    }
    ?>
    <form method="post" action="login.php">
        Usuario: <input type="text" name="usuario" required><br>
        Contraseña: <input type="password" name="contrasena" required><br>
        <input type="submit" value="Iniciar Sesión">
    </form>
</body>
</html>