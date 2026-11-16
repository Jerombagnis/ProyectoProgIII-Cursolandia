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
}
?>