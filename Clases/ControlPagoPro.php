<?php
class ControlPagoPro {
    private $manejadorPagoPro;

    public function __construct($manejadorPagoPro) {
        $this->manejadorPagoPro = $manejadorPagoPro;
    }

    // metodo para rgistrar una solicitud pro y que devuelve el id de dicha solicitud.
    public function solicitar($pago) {
        // control para que haya solo una solicitud pendiente por
        if ($this->manejadorPagoPro->tienePendiente($pago->usuario_id)) {
            throw new Exception("Ya tenés una solicitud pendiente. Esperá a que el administrador la resuelva.");
        }

        return $this->manejadorPagoPro->guardar($pago);
    }

    // metodo para rechazar una solicitud plan pro 
    public function rechazar($id) {
        if (!is_numeric($id)) {
            throw new Exception("La solicitud no es válida.");
        }

        if ($this->manejadorPagoPro->rechazar($id) == 0) {
            throw new Exception("La solicitud no existe o ya fue resuelta.");
        }
    }
    
    // metodo para confirmar una solicitud pro 
    public function aprobar($id, $meses) {
        if (!is_numeric($id)) {
            throw new Exception("La solicitud no es válida.");
        }
        if (!is_numeric($meses) || !in_array((int) $meses, array(1, 3, 12))) {
            throw new Exception("La duración elegida no es válida.");
        }

        if ($this->manejadorPagoPro->aprobar($id, $meses) == 0) {
            throw new Exception("La solicitud no existe o ya fue resuelta.");
        }
    }
}
?>