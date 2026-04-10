-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS cotizador_mvc;
USE cotizador_mvc;

-- Tabla 1: Servicios (El Catálogo)
CREATE TABLE servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    categoria VARCHAR(50) NOT NULL
);

-- Insertar los 12 servicios base
INSERT INTO servicios (nombre, descripcion, precio, categoria) VALUES
('Landing Page Profesional', 'Diseño de landing page optimizada para conversión.', 450.00, 'Desarrollo Web'),
('Sitio Web Corporativo', 'Desarrollo de sitio web empresarial.', 1200.00, 'Desarrollo Web'),
('Tienda Online', 'Implementación completa de e-commerce.', 2800.00, 'Desarrollo Web'),
('Sistema Web Personalizado', 'Sistema empresarial a medida.', 4500.00, 'Desarrollo Web'),
('Gestión de Redes Sociales', 'Administración mensual de redes.', 350.00, 'Marketing Digital'),
('Campaña Publicitaria', 'Publicidad digital segmentada.', 600.00, 'Marketing Digital'),
('Optimización SEO', 'Mejora posicionamiento en buscadores.', 900.00, 'Marketing Digital'),
('Estrategia Integral Marketing', 'Plan completo de marketing digital.', 2000.00, 'Marketing Digital'),
('Soporte Técnico Mensual', 'Soporte continuo para sistemas.', 250.00, 'Soporte y Consultoría'),
('Auditoría Seguridad Web', 'Evaluación de vulnerabilidades.', 750.00, 'Soporte y Consultoría'),
('Consultoría Tecnológica', 'Asesoramiento tecnológico profesional.', 1500.00, 'Soporte y Consultoría'),
('Mantenimiento Web Anual', 'Mantenimiento preventivo anual.', 1100.00, 'Soporte y Consultoría');

-- 1. Crear la tabla de Usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Tamaño de 255 es ideal para hashes de PHP
    rol ENUM('admin', 'cliente') NOT NULL DEFAULT 'cliente',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Tabla 2: Cotizaciones (Cabecera)
CREATE TABLE cotizaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    cliente_nombre VARCHAR(100) NOT NULL,
    cliente_empresa VARCHAR(100) NOT NULL,
    cliente_email VARCHAR(100) NOT NULL,
    cliente_telefono VARCHAR(20) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    descuento DECIMAL(10, 2) NOT NULL,
    iva DECIMAL(10, 2) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    fecha_generacion DATE NOT NULL,
    fecha_validez DATE NOT NULL,
    estado VARCHAR(20) DEFAULT 'Válida',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ON DELETE CASCADE
);

-- Tabla 3: Detalle de Cotizaciones (Los ítems del carrito guardados)
CREATE TABLE cotizacion_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cotizacion_id INT NOT NULL,
    servicio_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (servicio_id) REFERENCES servicios(id)
);


-- 2. Insertar usuarios de prueba
-- La contraseña para ambos es: password123
-- (El hash fue generado con password_hash() de PHP)
INSERT INTO usuarios (nombre, email, password, rol) VALUES 
('Super Admin', 'admin@admin.com', '$2y$10$yFjZz.RXZx/X1Lp.p1Y/Ou5/N/0G9x1P2l9y5v3w3n1e8h1D8V2S.', 'admin'),
('Cliente Prueba', 'cliente@cliente.com', '$2y$10$yFjZz.RXZx/X1Lp.p1Y/Ou5/N/0G9x1P2l9y5v3w3n1e8h1D8V2S.', 'cliente');

