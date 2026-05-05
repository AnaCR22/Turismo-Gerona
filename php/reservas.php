<?php
session_start();
require_once('conexion.php');

$db = new Conexion();
$con = $db->getConexion();

$accion = isset($_GET['accion']) ? $_GET['accion'] : 'recursos';
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Gerona-Reservas</title>
    <meta name="author" content="Ana Calleja Ramón" />
    <meta name="description" content="Central de reservas turísticas de Gerona" />
    <meta name="keywords" content="Gerona, reservas, turismo" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="../multimedia/favicon-Gerona.ico" />
    <link rel="stylesheet" href="../estilo/estilo.css" />
    <link rel="stylesheet" href="../estilo/layout.css" />
</head>
<body>
    <header>
        <h1><a href="../index.html" title="Página de inicio">Gerona-Desktop</a></h1>
        <nav>
            <a href="../index.html">Inicio</a>
            <a href="../gastronomia.html">Gastronomía</a>
            <a href="../rutas.html">Rutas</a>
            <a href="../meteorologia.html">Meteorología</a>
            <a href="../juego.html">Juego</a>
            <a href="reservas.php" class="activo">Reservas</a>
            <a href="../ayuda.html">Ayuda</a>
        </nav>
    </header>

    <p>Estás en: <a href="../index.html" title="Página de inicio">Inicio</a> &gt;&gt; <strong>Reservas</strong></p>

    <main>
        <h2>Central de Reservas Turísticas</h2>

        <nav>
            <a href="reservas.php?accion=recursos">Recursos</a>
            <a href="reservas.php?accion=registro">Registro</a>
            <a href="reservas.php?accion=login">Iniciar sesión</a>
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <a href="reservas.php?accion=mis_reservas">Mis reservas</a>
                <a href="reservas.php?accion=logout">Cerrar sesión</a>
            <?php endif; ?>
        </nav>

<?php
function mostrarRecursos($con) {
    $sql = "SELECT recursos.id, recursos.nombre, recursos.descripcion, recursos.precio, 
            recursos.plazas, recursos.fecha_inicio, recursos.fecha_fin, tipos_recurso.nombre AS tipo 
            FROM recursos 
            INNER JOIN tipos_recurso ON recursos.id_tipo = tipos_recurso.id";
    $resultado = $con->query($sql);

    if ($resultado->num_rows > 0) {
        while ($recurso = $resultado->fetch_assoc()) {
            echo "<section>";
            echo "<h3>" . htmlspecialchars($recurso['nombre']) . "</h3>";
            echo "<p>" . htmlspecialchars($recurso['descripcion']) . "</p>";
            echo "<p>Tipo: " . htmlspecialchars($recurso['tipo']) . "</p>";
            echo "<p>Precio: " . $recurso['precio'] . " €</p>";
            echo "<p>Plazas disponibles: " . $recurso['plazas'] . "</p>";
            echo "<p>Inicio: " . $recurso['fecha_inicio'] . "</p>";
            echo "<p>Fin: " . $recurso['fecha_fin'] . "</p>";

            if (isset($_SESSION['usuario_id'])) {
                echo "<a href='reservas.php?accion=reservar&id=" . $recurso['id'] . "'>Reservar</a>";
            } else {
                echo "<p>Inicia sesión para reservar</p>";
            }

            echo "</section>";
        }
    } else {
        echo "<p>No hay recursos disponibles.</p>";
    }
}

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

function realizarReserva($con) {
    if (!isset($_SESSION['usuario_id'])) {
        echo "<p>Debes iniciar sesión para reservar.</p>";
        return;
    }

    $id_recurso = isset($_GET['id']) ? intval($_GET['id']) : 0;

    $sql = "SELECT * FROM recursos WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id_recurso);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        echo "<p>Recurso no encontrado.</p>";
        return;
    }

    $recurso = $resultado->fetch_assoc();
    $stmt->close();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
        $num_plazas = intval($_POST['num_plazas']);
        $precio_total = $num_plazas * $recurso['precio'];

        if ($num_plazas < 1 || $num_plazas > $recurso['plazas']) {
            echo "<p>Número de plazas no válido.</p>";
            return;
        }

        $sql = "INSERT INTO reservas (id_usuario, id_recurso, fecha_reserva, num_plazas, precio_total, estado) 
                VALUES (?, ?, NOW(), ?, ?, 'confirmada')";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("iiid", $_SESSION['usuario_id'], $id_recurso, $num_plazas, $precio_total);

        if ($stmt->execute()) {
            $sql_update = "UPDATE recursos SET plazas = plazas - ? WHERE id = ?";
            $stmt_update = $con->prepare($sql_update);
            $stmt_update->bind_param("ii", $num_plazas, $id_recurso);
            $stmt_update->execute();
            $stmt_update->close();

            echo "<p>Reserva confirmada. Precio total: " . $precio_total . " €</p>";
            echo "<a href='reservas.php?accion=mis_reservas'>Ver mis reservas</a>";
        } else {
            echo "<p>Error al realizar la reserva.</p>";
        }
        $stmt->close();
        return;
    }

    echo "<section>";
    echo "<h3>Reservar: " . htmlspecialchars($recurso['nombre']) . "</h3>";
    echo "<p>Precio por plaza: " . $recurso['precio'] . " €</p>";
    echo "<p>Plazas disponibles: " . $recurso['plazas'] . "</p>";
    echo "<p>Fecha: " . $recurso['fecha_inicio'] . " — " . $recurso['fecha_fin'] . "</p>";
    echo "<form method='POST' action='reservas.php?accion=reservar&id=" . $id_recurso . "'>";
    echo "<label>Número de plazas: <input type='number' name='num_plazas' min='1' max='" . $recurso['plazas'] . "' value='1' required /></label>";
    echo "<p>Presupuesto: <strong>" . $recurso['precio'] . " € x plazas seleccionadas</strong></p>";
    echo "<button type='submit' name='confirmar'>Confirmar reserva</button>";
    echo "</form>";
    echo "</section>";
}

function mostrarReservas($con) {
    if (!isset($_SESSION['usuario_id'])) {
        echo "<p>Debes iniciar sesión para ver tus reservas.</p>";
        return;
    }

    $sql = "SELECT reservas.id, recursos.nombre, reservas.fecha_reserva, reservas.num_plazas, 
            reservas.precio_total, reservas.estado 
            FROM reservas 
            INNER JOIN recursos ON reservas.id_recurso = recursos.id 
            WHERE reservas.id_usuario = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        echo "<p>No tienes reservas.</p>";
        return;
    }

    echo "<section>";
    echo "<h3>Mis reservas</h3>";

    while ($reserva = $resultado->fetch_assoc()) {
        echo "<article>";
        echo "<h4>" . htmlspecialchars($reserva['nombre']) . "</h4>";
        echo "<p>Fecha de reserva: " . $reserva['fecha_reserva'] . "</p>";
        echo "<p>Plazas: " . $reserva['num_plazas'] . "</p>";
        echo "<p>Precio total: " . $reserva['precio_total'] . " €</p>";
        echo "<p>Estado: " . $reserva['estado'] . "</p>";

        if ($reserva['estado'] === 'confirmada') {
            echo "<a href='reservas.php?accion=anular&id=" . $reserva['id'] . "'>Anular reserva</a>";
        }

        echo "</article>";
    }

    echo "</section>";
    $stmt->close();
}

function anularReserva($con) {
    if (!isset($_SESSION['usuario_id'])) {
        echo "<p>Debes iniciar sesión.</p>";
        return;
    }

    $id_reserva = isset($_GET['id']) ? intval($_GET['id']) : 0;

    $sql = "SELECT reservas.id, reservas.id_recurso, reservas.num_plazas 
            FROM reservas 
            WHERE reservas.id = ? AND reservas.id_usuario = ? AND reservas.estado = 'confirmada'";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ii", $id_reserva, $_SESSION['usuario_id']);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        echo "<p>Reserva no encontrada o ya anulada.</p>";
        return;
    }

    $reserva = $resultado->fetch_assoc();
    $stmt->close();

    $sql = "UPDATE reservas SET estado = 'anulada' WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id_reserva);
    $stmt->execute();
    $stmt->close();

    $sql = "UPDATE recursos SET plazas = plazas + ? WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ii", $reserva['num_plazas'], $reserva['id_recurso']);
    $stmt->execute();
    $stmt->close();

    echo "<p>Reserva anulada correctamente.</p>";
    echo "<a href='reservas.php?accion=mis_reservas'>Volver a mis reservas</a>";
}

switch ($accion) {
    case 'recursos':
        mostrarRecursos($con);
        break;
    case 'registro':
        mostrarRegistro($con);
        break;
    case 'login':
        mostrarLogin($con);
        break;
    case 'logout':
        session_destroy();
        header("Location: reservas.php");
        exit;
    case 'reservar':
        realizarReserva($con);
        break;
    case 'mis_reservas':
        mostrarReservas($con);
        break;
    case 'anular':
        anularReserva($con);
        break;
}
?>