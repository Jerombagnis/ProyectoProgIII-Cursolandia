<?php
class Respuesta {
    private $id;
    private $pregunta_id;
    private $usuario_id;
    private $contenido;
    private $es_aceptada;
    private $created_at;

    public function __construct($id, $pregunta_id, $usuario_id, $contenido, $es_aceptada, $created_at) {
        $this->id = $id;
        $this->pregunta_id = $pregunta_id;
        $this->usuario_id = $usuario_id;
        $this->contenido = $contenido;
        $this->es_aceptada = $es_aceptada;
        $this->created_at = $created_at;
    }

    public function __get($propiedad) {
        return $this->$propiedad;
    }

    public function estaAceptada() {
        return ($this->es_aceptada == 1);
    }

    public function esAutor($usuario_id) {
        return ($this->usuario_id == $usuario_id);
    }

    public function validar() {
        if (trim($this->contenido) == "") {
            throw new Exception("La respuesta no puede estar vacía.");
        }
    }
}
?>