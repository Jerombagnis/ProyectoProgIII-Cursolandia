<?php
class PagoPro {
    private $id;
    private $usuario_id;
    private $mensaje;
    private $adjunto;
    private $estado;
    private $created_at;

    public function __construct($id, $usuario_id, $mensaje, $adjunto, $estado, $created_at) {
        $this->id = $id;
        $this->usuario_id = $usuario_id;
        $this->mensaje = $mensaje;
        $this->adjunto = $adjunto;
        $this->estado = $estado;
        $this->created_at = $created_at;
    }

    public function __get($propiedad) {
        return $this->$propiedad;
    }

    public function estaPendiente() {
        return ($this->estado == "pendiente");
    }

    public function estaAprobado() {
        return ($this->estado == "aprobado");
    }

    public function estaRechazado() {
        return ($this->estado == "rechazado");
    }

    // metodo para validar el comprobante subido ($archivo es el arreglo de $_FILES). si está bien devuelve la extensión en minúsculas, si no lanza una excepción.
    public static function validarComprobante($archivo) {
        if ($archivo['error'] == 4) {
            throw new Exception("Adjuntá el comprobante de pago.");
        }
        if ($archivo['error'] != 0) {  
            throw new Exception("No se pudo subir el comprobante. Probá con un archivo más liviano.");
        }
        if ($archivo['size'] > 5 * 1024 * 1024) { 
            throw new Exception("El comprobante no puede pesar más de 5 MB.");
        }

        $partes = explode(".", $archivo['name']);
        $extension = strtolower(end($partes));

        if (!in_array($extension, array("jpg", "jpeg", "png", "pdf"))) {
            throw new Exception("El comprobante tiene que ser una imagen (JPG o PNG) o un PDF.");
        }

        return $extension; 
    }
}
?>