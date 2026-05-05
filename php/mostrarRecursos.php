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
?>