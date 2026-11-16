<?php
class ManejadorPagoPro {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // metodo para guardar una solicitud nueva (por defecto queda en pendiente)
    public function guardar($pago) {
        $sql = "INSERT INTO pagos_pro (usuario_id, mensaje, adjunto)
                VALUES ('$pago->usuario_id','$pago->mensaje','$pago->adjunto')
                ";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudo guardar la solicitud.");
        }

        return mysqli_insert_id($this->conexion); // retorna el id de la solicitud 
    }

    // metodo que controla si el usuario ya posee una solicitud pendiente mediante true o false
    public function tienePendiente($usuario_id) {
        $sql = "SELECT COUNT(*) AS total FROM pagos_pro
                WHERE usuario_id = '$usuario_id'
                AND estado = 'pendiente'
                ";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudo consultar las solicitudes.");
        }

        $fila = mysqli_fetch_assoc($resultado); // retorna array con el total pendientes
        return ($fila['total'] > 0);
    }

    //metodo para listar todas las solicitudes de un usuario (mas nueva a mas vieja)
    public function listarPorUsuario($usuario_id) {
        $sql = "SELECT * FROM pagos_pro
                WHERE usuario_id = '$usuario_id'
                ORDER BY created_at DESC
                ";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudieron cargar tus solicitudes.");
        }

        $pagos = array(); //array que lista todas las solicitudes del usuario
        while ($fila = mysqli_fetch_assoc($resultado)) { 
            $pagos[] = new PagoPro($fila['id'],$fila['usuario_id'],$fila['mensaje'],$fila['adjunto'],$fila['estado'],$fila['created_at']);
        }
        return $pagos;
    }
}
?>