<?php
function mostrarLogin($con) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        $email = htmlspecialchars($_POST['email']);
        $password = $_POST['password'];

        $sql = "SELECT id, nombre, password FROM usuarios WHERE email = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            if (password_verify($password, $usuario['password'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                header("Location: reservas.php?accion=recursos");
                exit;
            } else {
                echo "<p>Contraseña incorrecta.</p>";
            }
        } else {
            echo "<p>No existe una cuenta con ese email.</p>";
        }
        $stmt->close();
        return;
    }

    echo "<section>";
    echo "<h3>Iniciar sesión</h3>";
    echo "<form method='POST' action='reservas.php?accion=login'>";
    echo "<label>Email: <input type='email' name='email' required /></label>";
    echo "<label>Contraseña: <input type='password' name='password' required /></label>";
    echo "<button type='submit' name='login'>Entrar</button>";
    echo "</form>";
    echo "</section>";
}
?>