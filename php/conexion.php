<?php
class Conexion {
    private $servidor = "localhost";
    private $usuario = "DBUSER2026";
    private $password = "DBPWD2026";
    private $baseDatos = "UO300568_DB";
    private $conexion;

    public function __construct() {
        $this->conexion = new mysqli(
            $this->servidor,
            $this->usuario,
            $this->password,
            $this->baseDatos
        );

        if ($this->conexion->connect_error) {
            die("Error de conexión: " . $this->conexion->connect_error);
        }

        $this->conexion->set_charset("utf8");
    }

    public function getConexion() {
        return $this->conexion;
    }

    public function cerrar() {
        $this->conexion->close();
    }
}
?>