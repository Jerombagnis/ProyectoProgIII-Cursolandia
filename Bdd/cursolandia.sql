CREATE DATABASE IF NOT EXISTS cursolandia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cursolandia;

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
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

CREATE TABLE curso_etiquetas (
    curso_id INT NOT NULL,
    etiqueta_id INT NOT NULL,
    PRIMARY KEY (curso_id, etiqueta_id),
    CONSTRAINT fk_ce_curso FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
    CONSTRAINT fk_ce_etiqueta FOREIGN KEY (etiqueta_id) REFERENCES etiquetas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE archivos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    curso_id        INT NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    ruta            VARCHAR(255) NOT NULL,               -- nombre con el que se guardó en uploads/
    tamano_bytes    INT UNSIGNED NOT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_archivo_curso FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
    CONSTRAINT chk_archivo_tamano CHECK (tamano_bytes > 0)
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

INSERT INTO usuarios (nombre, apellido, email, password, rol, tipo_plan) VALUES
('Admin', 'Cursolandia', 'admin@cursolandia.com', 'admin123', 'admin', 'pro'),
('Docente', 'Pro', 'pro@cursolandia.com', 'docente123', 'usuario', 'pro'),
('Usuario', 'Gratis', 'user@cursolandia.com', 'usuario123', 'usuario', 'gratuito');