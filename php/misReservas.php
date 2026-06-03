<?php
class MisReservas {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function mostrar() {
        if (!isset($_SESSION['usuario_id'])) {
            echo "<p>Debes iniciar sesión para ver tus reservas.</p>";
            return;
        }

        $sql = "SELECT reservas.id, recursos.nombre, reservas.fecha_reserva, reservas.num_plazas, 
                reservas.precio_total, reservas.estado 
                FROM reservas 
                INNER JOIN recursos ON reservas.id_recurso = recursos.id 
                WHERE reservas.id_usuario = ?";
        $stmt =  $this->con->prepare($sql);
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

    public function anular() {
        if (!isset($_SESSION['usuario_id'])) {
            echo "<p>Debes iniciar sesión.</p>";
            return;
        }

        $id_reserva = isset($_GET['id']) ? intval($_GET['id']) : 0;

        $sql = "SELECT reservas.id, reservas.id_recurso, reservas.num_plazas 
                FROM reservas 
                WHERE reservas.id = ? AND reservas.id_usuario = ? AND reservas.estado = 'confirmada'";
        $stmt =  $this->con->prepare($sql);
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
        $stmt =  $this->con->prepare($sql);
        $stmt->bind_param("i", $id_reserva);
        $stmt->execute();
        $stmt->close();

        $sql = "UPDATE recursos SET plazas = plazas + ? WHERE id = ?";
        $stmt =  $this->con->prepare($sql);
        $stmt->bind_param("ii", $reserva['num_plazas'], $reserva['id_recurso']);
        $stmt->execute();
        $stmt->close();

        echo "<p>Reserva anulada correctamente.</p>";
        echo "<a href='reservas.php?accion=mis_reservas'>Volver a mis reservas</a>";
    }
}
?>