<?php
class Curso {
    private $id;
    private $creador_id;
    private $titulo;
    private $descripcion;
    private $costo;
    private $cupo;
    private $fecha_inicio;
    private $fecha_fin;
    private $tipo_acceso;

    public function __construct($id, $creador_id, $titulo, $descripcion, $costo, $cupo, $fecha_inicio, $fecha_fin, $tipo_acceso) {
        $this->id = $id;
        $this->creador_id = $creador_id;
        $this->titulo = $titulo;
        $this->descripcion = $descripcion;
        $this->costo = $costo;
        $this->cupo = $cupo;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
        $this->tipo_acceso = $tipo_acceso;
    }

    public function __get($propiedad) {
        return $this->$propiedad;
    }

    public function estaActivo() {
        $hoy = date("Y-m-d");

        if($hoy <= $this->fecha_inicio) {
            return "proximamente";
        } else if ($hoy >= $this->fecha_inicio && $hoy <= $this->fecha_fin) {
            return "activo";
        } else {
            return "finalizado";
        }
    }

    public function esGratuito() {
        return ($this->costo == 0);
    }

    public function esPrivado() {
        return ($this->tipo_acceso == "privado");
    }

    public function esCreador($usuario_id) {
        return ($this->creador_id == $usuario_id);
    }

    public function validar() {
        if (trim($this->titulo) == "") {
            throw new Exception("El título es obligatorio.");
        }
        if (trim($this->descripcion) == "") {
            throw new Exception("La descripción es obligatoria.");
        }
        if (!is_numeric($this->costo) || $this->costo < 0) {
            throw new Exception("El costo no puede ser negativo.");
        }
        if ($this->cupo != "" && (!is_numeric($this->cupo) || $this->cupo <= 0)) {
            throw new Exception("El cupo tiene que ser mayor a 0 (o dejarlo vacío).");
        }
        if ($this->fecha_inicio == "" || $this->fecha_fin == "") {
            throw new Exception("Las fechas de inicio y fin son obligatorias.");
        }
        if ($this->fecha_fin < $this->fecha_inicio) {
            throw new Exception("La fecha de fin no puede ser anterior a la de inicio.");
        }
        if ($this->tipo_acceso != "publico" && $this->tipo_acceso != "privado") {
            throw new Exception("El tipo de acceso no es válido.");
        }
    }
}
?>