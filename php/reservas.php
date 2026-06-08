<?php
session_start();
require_once('conexion.php');
require_once('recursos.php');
require_once('usuario.php');
require_once('misreservas.php');
require_once('presupuesto.php');

$db = new Conexion();
$con = $db->getConexion();

$accion = isset($_GET['accion']) ? $_GET['accion'] : 'recursos';

if ($accion === 'logout') {
    session_destroy();
    header("Location: reservas.php");
    exit;
}

if ($accion === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = new Usuario($con);
    $usuario->procesarLogin();
}

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
            <?php if (isset($_SESSION['usuario_id'])): ?> 
                <a href="reservas.php?accion=mis_reservas">Mis reservas</a>
                <a href="reservas.php?accion=logout">Cerrar sesión</a>
            <?php else: ?>
                <a href="reservas.php?accion=registro">Registro</a>
                <a href="reservas.php?accion=login">Iniciar sesión</a>
            <?php endif; ?>
        </nav>

        <?php
        $recursos = new Recursos($con);
        $usuario = new Usuario($con);
        $misReservas = new MisReservas($con);
        $presupuesto = new Presupuesto($con);

        switch ($accion) {
            case 'recursos':
                $recursos->mostrar();
                break;
            case 'registro':
                $usuario->registro();
                break;
            case 'login':
                $usuario->login();
                break;
            case 'reservar':
                $recursos->reservar();
                break;
            case 'mis_reservas':
                $misReservas->mostrar();
                break;
            case 'presupuesto':
                $presupuesto->generar();
                break;
            case 'confirmar':
                $presupuesto->confirmar();
                break;
            case 'anular':
                $misReservas->anular();
                break;
        }
        ?>
    </main>
</body>
</html>

