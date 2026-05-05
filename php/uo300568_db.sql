CREATE DATABASE IF NOT EXISTS UO300568_DB;
USE UO300568_DB;

CREATE TABLE tipos_recurso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20)
);

CREATE TABLE recursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(8,2) NOT NULL,
    plazas INT NOT NULL,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NOT NULL,
    id_tipo INT NOT NULL,
    FOREIGN KEY (id_tipo) REFERENCES tipos_recurso(id)
);

CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_recurso INT NOT NULL,
    fecha_reserva DATETIME NOT NULL,
    num_plazas INT NOT NULL,
    precio_total DECIMAL(8,2) NOT NULL,
    estado ENUM('confirmada','anulada') DEFAULT 'confirmada',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    FOREIGN KEY (id_recurso) REFERENCES recursos(id)
);

CREATE TABLE valoraciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_recurso INT NOT NULL,
    puntuacion INT NOT NULL CHECK (puntuacion >= 0 AND puntuacion <= 10),
    comentario TEXT,
    fecha DATETIME NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    FOREIGN KEY (id_recurso) REFERENCES recursos(id)
);

-- Tipos de recurso
INSERT INTO tipos_recurso (nombre) VALUES
('Museo'),
('Ruta'),
('Restaurante'),
('Hotel'),
('Espectáculo');

-- Recursos turísticos
INSERT INTO recursos (nombre, descripcion, precio, plazas, fecha_inicio, fecha_fin, id_tipo) VALUES
('Museo de Arte de Girona', 'Visita guiada al museo de arte de la ciudad, con colecciones desde el románico hasta el arte contemporáneo.', 12.00, 30, '2026-07-01 10:00:00', '2026-07-01 13:00:00', 1),
('Camí de Ronda: Calella - Tamariu', 'Ruta de senderismo litoral por la Costa Brava con guía local.', 25.00, 15, '2026-07-05 08:30:00', '2026-07-05 14:00:00', 2),
('Restaurante Can Roca', 'Menú degustación en restaurante tradicional gerundense.', 45.00, 20, '2026-07-10 13:00:00', '2026-07-10 16:00:00', 3),
('Hotel Llegendes de Girona', 'Noche en hotel boutique en el casco histórico con desayuno incluido.', 95.00, 10, '2026-07-15 14:00:00', '2026-07-16 12:00:00', 4),
('Festival Temps de Flors', 'Entrada al festival de flores de Girona con acceso a todos los espacios.', 8.00, 100, '2026-05-10 09:00:00', '2026-05-18 21:00:00', 5);