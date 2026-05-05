<?php
function anularReserva($con) {
    if (!isset($_SESSION['usuario_id'])) {
        echo "<p>Debes iniciar sesión.</p>";
        return;
    }

    $id_reserva = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // Comprobar que la reserva pertenece al usuario
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

    // Anular la reserva
    $sql = "UPDATE reservas SET estado = 'anulada' WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id_reserva);
    $stmt->execute();
    $stmt->close();

    // Devolver plazas al recurso
    $sql = "UPDATE recursos SET plazas = plazas + ? WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ii", $reserva['num_plazas'], $reserva['id_recurso']);
    $stmt->execute();
    $stmt->close();

    echo "<p>Reserva anulada correctamente.</p>";
    echo "<a href='reservas.php?accion=mis_reservas'>Volver a mis reservas</a>";
}
?>