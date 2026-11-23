<?php
class ManejadorPregunta {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // metodo para guardar una pregunta nueva, retorna el id de la misma 
    public function guardar($pregunta) {
        $sql = "INSERT INTO preguntas (curso_id, usuario_id, titulo, contenido)
                VALUES ('$pregunta->curso_id','$pregunta->usuario_id','$pregunta->titulo','$pregunta->contenido')
                ";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudo publicar la pregunta.");
        }

        return mysqli_insert_id($this->conexion);
    }

    // metodo para buscar una pregunta por su id, retorna la pregunta o null si no existe
    public function buscarPorId($id) {
        $sql = "SELECT * FROM preguntas WHERE id = '$id'";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudo buscar la pregunta.");
        }

        $fila = mysqli_fetch_assoc($resultado);
        if (!$fila) {
            return null;
        }

        return new Pregunta($fila['id'],$fila['curso_id'],$fila['usuario_id'],$fila['titulo'],$fila['contenido'],$fila['estado'],$fila['destacada'],$fila['created_at']);
    }

    // metodo para lista preguntas de un curso, ordena por destacada primero y luego por fecha mas reciente
    // por cada pregunta obtiene el nombre del usuario que la realizo y la cantidad de respuestas 
    public function listarPorCurso($curso_id) {
        $sql = "SELECT p.*, u.nombre, u.apellido,
                       (SELECT COUNT(*) FROM respuestas r WHERE r.pregunta_id = p.id) AS cantidad_respuestas
                FROM preguntas p
                INNER JOIN usuarios u ON u.id = p.usuario_id
                WHERE p.curso_id = '$curso_id'
                ORDER BY p.destacada DESC, p.created_at DESC";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudieron cargar las preguntas.");
        }

        $items = array();
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $pregunta = new Pregunta($fila['id'],$fila['curso_id'],$fila['usuario_id'],$fila['titulo'],$fila['contenido'],$fila['estado'],$fila['destacada'],$fila['created_at']);
            $items[] = array('pregunta' => $pregunta,'autor' => $fila['nombre'] . " " . $fila['apellido'],'respuestas' => $fila['cantidad_respuestas']);
        }
        return $items;
    }

    //metodo para poder destacar una pregunta
    public function cambiarDestacada($pregunta_id, $destacada) {
    $sql = "UPDATE preguntas
            SET destacada = '$destacada'
            WHERE id = '$pregunta_id'";

    $resultado = mysqli_query($this->conexion, $sql);

    if (!$resultado) {
        throw new Exception("No se pudo actualizar el destacado de la pregunta.");
    }
}
}
?>