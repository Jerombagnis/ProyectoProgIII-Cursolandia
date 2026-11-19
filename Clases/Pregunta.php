<?php
class Pregunta {
    private $id;
    private $curso_id;
    private $usuario_id;
    private $titulo;
    private $contenido;
    private $estado;
    private $destacada;
    private $created_at;

    public function __construct($id, $curso_id, $usuario_id, $titulo, $contenido, $estado, $destacada, $created_at) {
        $this->id = $id;
        $this->curso_id = $curso_id;
        $this->usuario_id = $usuario_id;
        $this->titulo = $titulo;
        $this->contenido = $contenido;
        $this->estado = $estado;
        $this->destacada = $destacada;
        $this->created_at = $created_at;
    }

    public function __get($propiedad) {
        return $this->$propiedad;
    }

    public function estaAbierta() {
        return ($this->estado == "abierta");
    }

    public function estaCerrada() {
        return ($this->estado == "cerrada");
    }

    public function esDestacada() {
        return ($this->destacada == 1);
    }

    public function esAutor($usuario_id) {
        return ($this->usuario_id == $usuario_id);
    }

    // metodo para validar los datos
    public function validar() {
        if (trim($this->titulo) == "") {
            throw new Exception("El título de la pregunta es obligatorio.");
        }
        if (strlen($this->titulo) > 200) {
            throw new Exception("El título no puede tener más de 200 caracteres.");
        }
        if (trim($this->contenido) == "") {
            throw new Exception("Escribí el detalle de tu pregunta.");
        }
    }
}
?>