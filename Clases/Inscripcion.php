<?php
class Inscripcion {
    private $id;
    private $usuario_id;
    private $curso_id;
    private $estado;

    public function __construct($id, $usuario_id, $curso_id, $estado) {
        $this->id = $id;
        $this->usuario_id = $usuario_id;
        $this->curso_id = $curso_id;
        $this->estado = $estado;
    }

    // metodo magico para obtener el valor de un atributo privado
    public function __get($propiedad) {
        return $this->$propiedad;
    }

    //metodos para obtener el estado de la inscripcion
    public function estaPendiente() {
        return ($this->estado == "pendiente");
    }

    public function estaConfirmada() {
        return ($this->estado == "confirmada");
    }

    public function estaRechazada() {
        return ($this->estado == "rechazada");
    }
}
?>