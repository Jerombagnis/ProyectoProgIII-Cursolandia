<?php
class ManejadorRespuesta {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // metodo para guardar una respuesta, retorna el id 
    public function guardar($respuesta) {
        $sql = "INSERT INTO respuestas (pregunta_id, usuario_id, contenido)
                VALUES (" . $respuesta->pregunta_id . ",
                        " . $respuesta->usuario_id . ",
                        '" . $respuesta->contenido . "')";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudo publicar la respuesta.");
        }

        return mysqli_insert_id($this->conexion);
    }

    // metodo que devuelve la ultima respuesta de una pregunta o null si no hay respuestas 
    public function buscarUltimaPorPregunta($pregunta_id) {
        $sql = "SELECT * FROM respuestas
                WHERE pregunta_id = '$pregunta_id'
                ORDER BY id DESC
                LIMIT 1
                ";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudo consultar las respuestas.");
        }

        $fila = mysqli_fetch_assoc($resultado);
        if (!$fila) {
            return null;
        }

        return new Respuesta($fila['id'], $fila['pregunta_id'], $fila['usuario_id'], $fila['contenido'], $fila['es_aceptada'], $fila['created_at']);
    }


    // metodo para listar las respuestas de una pregunta, en orden de mas antigua a mas nueva
    public function listarPorPregunta($pregunta_id) {
        $sql = "SELECT r.*, u.nombre, u.apellido
                FROM respuestas r
                INNER JOIN usuarios u ON u.id = r.usuario_id
                WHERE r.pregunta_id = '$pregunta_id'
                ORDER BY r.id";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudieron cargar las respuestas.");
        }

        $items = array();
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $respuesta = new Respuesta($fila['id'], $fila['pregunta_id'], $fila['usuario_id'], $fila['contenido'], $fila['es_aceptada'], $fila['created_at']);
            $items[] = array('respuesta' => $respuesta,'autor' => $fila['nombre'] . " " . $fila['apellido']);
        }
        return $items;
    }
}
?>