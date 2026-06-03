<?php
class Usuario {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function registro() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registro'])) {
            $nombre = htmlspecialchars($_POST['nombre']);
            $email = htmlspecialchars($_POST['email']);
            // encriptamos contraseña
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $telefono = htmlspecialchars($_POST['telefono']);

            $sql = "INSERT INTO usuarios (nombre, email, password, telefono) VALUES (?, ?, ?, ?)";
            $stmt =  $this->con->prepare($sql);
            $stmt->bind_param("ssss", $nombre, $email, $password, $telefono); //4 parámetros de tipo string

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

    public function procesarLogin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
            $email = htmlspecialchars($_POST['email']);
            $password = $_POST['password'];

            $sql = "SELECT id, nombre, password FROM usuarios WHERE email = ?";
            $stmt = $this->con->prepare($sql);
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
                } 
            }

            // Si falla -> va al login con error
            header("Location: reservas.php?accion=login&error=1");
            exit;
        }
    }
    public function login() {
        if (isset($_GET['error'])) {
            echo "<p>Email o contraseña incorrectos.</p>";
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
}
?>