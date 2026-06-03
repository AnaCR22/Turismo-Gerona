<?php
class Presupuesto {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function generar() {
        if (!isset($_SESSION['usuario_id'])) {
            echo "<p>Debes iniciar sesión.</p>";
            return;
        }

        $id_recurso = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $num_plazas = intval($_POST['num_plazas']);

        $sql = "SELECT * FROM recursos WHERE id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("i", $id_recurso);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 0) {
            echo "<p>Recurso no encontrado.</p>";
            return;
        }

        $recurso = $resultado->fetch_assoc();
        $stmt->close();

        if ($num_plazas < 1 || $num_plazas > $recurso['plazas']) {
            echo "<p>Número de plazas no válido.</p>";
            return;
        }

        $precio_total = $num_plazas * $recurso['precio'];

        $sql = "INSERT INTO presupuestos (id_usuario, id_recurso, num_plazas, precio_unitario, precio_total, fecha) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("iiidd", $_SESSION['usuario_id'], $id_recurso, $num_plazas, $recurso['precio'], $precio_total);
        $stmt->execute();
        $stmt->close();

        echo "<section>";
        echo "<h3>Presupuesto generado</h3>";
        echo "<p>Recurso: " . htmlspecialchars($recurso['nombre']) . "</p>";
        echo "<p>Plazas: " . $num_plazas . "</p>";
        echo "<p>Precio unitario: " . $recurso['precio'] . " €</p>";
        echo "<p>Precio total: " . $precio_total . " €</p>";
        echo "<form method='POST' action='reservas.php?accion=confirmar&id=" . $id_recurso . "'>";
        echo "<input type='hidden' name='num_plazas' value='" . $num_plazas . "' />";
        echo "<input type='hidden' name='precio_total' value='" . $precio_total . "' />";
        echo "<button type='submit' name='confirmar'>Confirmar reserva</button>";
        echo "</form>";
        echo "</section>";
    }

    public function confirmar() {
        if (!isset($_SESSION['usuario_id'])) {
            echo "<p>Debes iniciar sesión.</p>";
            return;
        }

        $id_recurso = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $num_plazas = intval($_POST['num_plazas']);
        $precio_total = floatval($_POST['precio_total']);

        $sql = "INSERT INTO reservas (id_usuario, id_recurso, fecha_reserva, num_plazas, precio_total, estado) 
                VALUES (?, ?, NOW(), ?, ?, 'confirmada')";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("iiid", $_SESSION['usuario_id'], $id_recurso, $num_plazas, $precio_total);

        if ($stmt->execute()) {
            $sql_update = "UPDATE recursos SET plazas = plazas - ? WHERE id = ?";
            $stmt_update = $this->con->prepare($sql_update);
            $stmt_update->bind_param("ii", $num_plazas, $id_recurso);
            $stmt_update->execute();
            $stmt_update->close();

            echo "<p>Reserva confirmada. Precio total: " . $precio_total . " €</p>";
            echo "<a href='reservas.php?accion=mis_reservas'>Ver mis reservas</a>";
        } else {
            echo "<p>Error al realizar la reserva.</p>";
        }
        $stmt->close();
    }
}
?>