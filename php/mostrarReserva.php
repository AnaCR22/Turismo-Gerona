<?php
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
?>