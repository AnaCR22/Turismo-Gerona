<?php
function realizarReserva($con) {
    if (!isset($_SESSION['usuario_id'])) {
        echo "<p>Debes iniciar sesión para reservar.</p>";
        return;
    }

    $id_recurso = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // Buscar el recurso
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

    // Si el usuario confirma la reserva
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
            // Actualizar plazas disponibles
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

    // Mostrar presupuesto
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
?>