<?php
class Recursos {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function mostrar() {
        $sql = "SELECT recursos.id, recursos.nombre, recursos.descripcion, recursos.precio, 
            recursos.plazas, recursos.fecha_inicio, recursos.fecha_fin, tipos_recurso.nombre AS tipo 
            FROM recursos 
            INNER JOIN tipos_recurso ON recursos.id_tipo = tipos_recurso.id";
        $resultado = $this->con->query($sql);

        if ($resultado->num_rows > 0) {
            while ($recurso = $resultado->fetch_assoc()) {
                echo "<section>";
                echo "<h3>" . htmlspecialchars($recurso['nombre']) . "</h3>";
                echo "<p>" . htmlspecialchars($recurso['descripcion']) . "</p>";
                echo "<ul>";
                echo "<li>Tipo: " . htmlspecialchars($recurso['tipo']) . "</li>";
                echo "<li>Precio: " . $recurso['precio'] . " €</li>";
                echo "<li>Plazas disponibles: " . $recurso['plazas'] . "</li>";
                echo "<li>Fecha inicio: " . $recurso['fecha_inicio'] . "</li>";
                echo "<li>Fecha Fin: " . $recurso['fecha_fin'] . "</li>";
                echo "</ul>";

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

    public function reservar() {
        if (!isset($_SESSION['usuario_id'])) {
            echo "<p>Debes iniciar sesión para reservar.</p>";
            return;
        }

        $id_recurso = isset($_GET['id']) ? intval($_GET['id']) : 0;

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

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
            $num_plazas = intval($_POST['num_plazas']);
            $precio_total = $num_plazas * $recurso['precio'];

            if ($num_plazas < 1 || $num_plazas > $recurso['plazas']) {
                echo "<p>Número de plazas no válido.</p>";
                return;
            }

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
            return;
        }

        echo "<section>";
        echo "<h3>Reservar: " . htmlspecialchars($recurso['nombre']) . "</h3>";
        echo "<p>Precio por plaza: " . $recurso['precio'] . " €</p>";
        echo "<p>Plazas disponibles: " . $recurso['plazas'] . "</p>";
        echo "<p>Fecha: " . $recurso['fecha_inicio'] . " — " . $recurso['fecha_fin'] . "</p>";
        echo "<form method='POST' action='reservas.php?accion=presupuesto&id=" . $id_recurso . "'>";
        echo "<label>Número de plazas: <input type='number' name='num_plazas' min='1' max='" . $recurso['plazas'] . "' value='1' required /></label>";
        echo "<button type='submit' name='presupuesto'>Generar presupuesto</button>";
        echo "</form>";
        echo "</section>";
    }
}
?>