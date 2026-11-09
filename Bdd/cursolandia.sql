CREATE DATABASE IF NOT EXISTS cursolandia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cursolandia;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS pagos_pro;
DROP TABLE IF EXISTS notificaciones;
DROP TABLE IF EXISTS archivos;
DROP TABLE IF EXISTS respuestas;
DROP TABLE IF EXISTS preguntas;
DROP TABLE IF EXISTS inscripciones;
DROP TABLE IF EXISTS curso_etiquetas;
DROP TABLE IF EXISTS usuario_intereses;
DROP TABLE IF EXISTS cursos;
DROP TABLE IF EXISTS etiquetas;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('usuario', 'admin') NOT NULL DEFAULT 'usuario',
    vencimiento_pro DATE NULL,
    antecedentes TEXT NULL,
    imagen_perfil VARCHAR(255) NOT NULL DEFAULT 'default.png',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE etiquetas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE usuario_intereses (
    usuario_id  INT NOT NULL,
    etiqueta_id INT NOT NULL,
    PRIMARY KEY (usuario_id, etiqueta_id),
    CONSTRAINT fk_ui_usuario  FOREIGN KEY (usuario_id)  REFERENCES usuarios(id)  ON DELETE CASCADE,
    CONSTRAINT fk_ui_etiqueta FOREIGN KEY (etiqueta_id) REFERENCES etiquetas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    creador_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT NOT NULL,
    costo DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    cupo INT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    tipo_acceso ENUM('publico', 'privado') NOT NULL DEFAULT 'publico',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_curso_creador FOREIGN KEY (creador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT chk_fechas CHECK (fecha_fin >= fecha_inicio),
    CONSTRAINT chk_curso_costo  CHECK (costo >= 0),
    CONSTRAINT chk_curso_cupo   CHECK (cupo IS NULL OR cupo > 0),
    INDEX idx_cursos_fechas (fecha_inicio, fecha_fin),
    FULLTEXT KEY ft_cursos_titulo (titulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE curso_etiquetas (
    curso_id INT NOT NULL,
    etiqueta_id INT NOT NULL,
    PRIMARY KEY (curso_id, etiqueta_id),
    CONSTRAINT fk_ce_curso FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
    CONSTRAINT fk_ce_etiqueta FOREIGN KEY (etiqueta_id) REFERENCES etiquetas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE archivos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    curso_id        INT NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    ruta            VARCHAR(255) NOT NULL,               -- nombre con el que se guardó en uploads/
    tamano_bytes    INT UNSIGNED NOT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_archivo_curso FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
    CONSTRAINT chk_archivo_tamano CHECK (tamano_bytes > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE inscripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    usuario_id INT NOT NULL,
    estado ENUM('pendiente', 'confirmada', 'rechazada') NOT NULL DEFAULT 'confirmada',
    fecha_inscripcion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_usuario_curso UNIQUE (curso_id, usuario_id),
    CONSTRAINT fk_insc_curso FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
    CONSTRAINT fk_insc_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_insc_usuario_estado (usuario_id, estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE preguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    usuario_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    contenido TEXT NOT NULL,
    estado ENUM('abierta', 'cerrada') NOT NULL DEFAULT 'abierta',
    destacada TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_preg_curso FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
    CONSTRAINT fk_preg_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_preguntas_curso (curso_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE respuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pregunta_id INT NOT NULL,
    usuario_id INT NOT NULL,
    contenido TEXT NOT NULL,
    es_aceptada TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_resp_pregunta FOREIGN KEY (pregunta_id) REFERENCES preguntas(id) ON DELETE CASCADE,
    CONSTRAINT fk_resp_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_respuestas_pregunta (pregunta_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notificaciones (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT NOT NULL,                            -- destinatario
    tipo        ENUM('pregunta_creada', 'respuesta_creada', 'pregunta_cerrada', 'pregunta_eliminada') NOT NULL,
    mensaje     VARCHAR(255) NOT NULL,
    curso_id    INT NULL,
    pregunta_id INT NULL,
    leida       TINYINT(1) NOT NULL DEFAULT 0,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_usuario  FOREIGN KEY (usuario_id)  REFERENCES usuarios(id)  ON DELETE CASCADE,
    CONSTRAINT fk_notif_curso    FOREIGN KEY (curso_id)    REFERENCES cursos(id)    ON DELETE CASCADE,
    CONSTRAINT fk_notif_pregunta FOREIGN KEY (pregunta_id) REFERENCES preguntas(id) ON DELETE SET NULL,
    INDEX idx_notif_usuario (usuario_id, leida, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pagos_pro (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT NOT NULL,
    mensaje     TEXT NULL,
    adjunto     VARCHAR(255) NOT NULL,                   -- comprobante en uploads/
    estado      ENUM('pendiente', 'aprobado', 'rechazado') NOT NULL DEFAULT 'pendiente',
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resuelto_at TIMESTAMP NULL,
    CONSTRAINT fk_pago_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_pagos_estado (estado, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insercion usuarios de prueba

INSERT INTO usuarios (nombre, apellido, email, password, rol, vencimiento_pro) VALUES
('Admin',   'Cursolandia', 'admin@cursolandia.com',  '0192023a7bbd73250516f069df18b500', 'admin',   '2099-12-31'),
('Docente', 'Pro',         'pro@cursolandia.com',    '8be2efedf58bc5ca5ef33aec86a7a217', 'usuario', '2099-12-31'),
('Usuario', 'Gratis',      'user@cursolandia.com',   '401cec94d3ed586d8cb895c10c0f7db6', 'usuario', NULL);

INSERT INTO etiquetas (nombre) VALUES
('PHP'), ('Bases de datos'), ('Java'), ('HTML y CSS'), ('JavaScript'), ('Python');

INSERT INTO usuario_intereses (usuario_id, etiqueta_id) VALUES
(3, 1), (3, 2);

INSERT INTO cursos (creador_id, titulo, descripcion, costo, cupo, fecha_inicio, fecha_fin, tipo_acceso) VALUES
(2, 'Introducción a PHP',      'Primeros pasos con PHP: sintaxis, formularios y conexión a la base de datos.', 0.00,     30,   '2026-10-01', '2026-12-20', 'publico'),
(2, 'MariaDB desde cero',      'Modelado relacional, SQL, claves e índices con MariaDB.',                      15000.00, 20,   '2026-10-15', '2026-12-15', 'publico'),
(2, 'Java: programación POO',  'Clases, herencia y polimorfismo con Java. Curso solo por invitación.',         0.00,     NULL, '2026-09-01', '2026-12-31', 'privado'),
(2, 'HTML y CSS básico',       'Maquetado web con HTML5 y CSS3. Edición ya finalizada.',                       0.00,     NULL, '2026-03-01', '2026-06-30', 'publico');
 
INSERT INTO curso_etiquetas (curso_id, etiqueta_id) VALUES
(1, 1), (1, 4),
(2, 2),
(3, 3),
(4, 4);
 
INSERT INTO inscripciones (curso_id, usuario_id, estado) VALUES
(1, 3, 'confirmada'),
(2, 3, 'pendiente'),
(1, 1, 'confirmada');
 
INSERT INTO preguntas (curso_id, usuario_id, titulo, contenido, estado, destacada) VALUES
(1, 3, '¿Cómo conecto PHP con MariaDB?',      'Estoy probando con mysqli pero no me conecta. ¿Qué me recomiendan usar?', 'abierta', 0),
(1, 3, '¿Qué diferencia hay entre GET y POST?', 'No me queda claro cuándo conviene usar cada uno en un formulario.',      'cerrada', 1);
 
INSERT INTO respuestas (pregunta_id, usuario_id, contenido, es_aceptada) VALUES
(1, 2, 'Te recomiendo PDO con consultas preparadas. Revisá host, usuario y nombre de la base en tu config.', 0),
(2, 2, 'GET manda los datos en la URL y es para consultas; POST los manda en el cuerpo y es para enviar datos.', 1);
 
INSERT INTO notificaciones (usuario_id, tipo, mensaje, curso_id, pregunta_id, leida) VALUES
(2, 'pregunta_creada',  'Usuario Gratis hizo una pregunta en "Introducción a PHP".',  1, 1, 0),
(3, 'respuesta_creada', 'Docente Pro respondió tu pregunta "¿Cómo conecto PHP con MariaDB?".', 1, 1, 0);