<?php
function mostrarRegistro($con) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registro'])) {
        $nombre = htmlspecialchars($_POST['nombre']);
        $email = htmlspecialchars($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $telefono = htmlspecialchars($_POST['telefono']);

        $sql = "INSERT INTO usuarios (nombre, email, password, telefono) VALUES (?, ?, ?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ssss", $nombre, $email, $password, $telefono);

        if ($stmt->execute()) {
            echo "<p>Registro completado. Ya puedes iniciar sesión.</p>";
        } else {
            echo "<p>Error: el email ya está registrado.</p>";
        }
        $stmt->close();
        return;
    }

    echo "<section>";
    echo "<h3>Registro de usuario</h3>";
    echo "<form method='POST' action='reservas.php?accion=registro'>";
    echo "<label>Nombre: <input type='text' name='nombre' required /></label>";
    echo "<label>Email: <input type='email' name='email' required /></label>";
    echo "<label>Contraseña: <input type='password' name='password' required /></label>";
    echo "<label>Teléfono: <input type='text' name='telefono' /></label>";
    echo "<button type='submit' name='registro'>Registrarse</button>";
    echo "</form>";
    echo "</section>";
}
?>