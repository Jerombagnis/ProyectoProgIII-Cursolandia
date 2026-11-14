<?php
class ManejadorInscripcion {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // metodo para guardar una nueva inscripcion en la bdd y retornar el id de la inscripcion creada. 
    public function guardar($inscripcion) {
        $sql = "INSERT INTO inscripciones (usuario_id, curso_id, estado)
                VALUES ('$inscripcion->usuario_id','$inscripcion->curso_id','$inscripcion->estado')
                ";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudo guardar la inscripción.");
        }

        return mysqli_insert_id($this->conexion);
    }

    // metodo para buscar la inscripcion de un usuario en un curso. Devuelve nscripcion o null
    public function buscarPorUsuarioYCurso($usuario_id, $curso_id) {
        $sql = "SELECT * FROM inscripciones
                WHERE usuario_id = '$usuario_id' AND curso_id = '$curso_id'
                ";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudo buscar la inscripción.");
        }

        $fila = mysqli_fetch_assoc($resultado);
        if (!$fila) {
            return null;
        }

        return new Inscripcion($fila['id'], $fila['usuario_id'], $fila['curso_id'], $fila['estado']);
    }

    // metodo para contar las inscripciones activas de un usuario (pendientes o confirmadas) en cursos que no hayan terminado
    public function contarActivasPorUsuario($usuario_id) {
        $sql = "SELECT COUNT(*) AS total
                FROM inscripciones i
                INNER JOIN cursos c ON c.id = i.curso_id
                WHERE i.usuario_id = '$usuario_id' AND i.estado IN ('pendiente', 'confirmada') AND c.fecha_fin >= CURDATE()
                ";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudo contar las inscripciones.");
        }

        $fila = mysqli_fetch_assoc($resultado);
        return $fila['total'];
    }

    // metodo para contar las inscripciones confirmadas de un curso (controla el cupo)
    public function contarConfirmadasPorCurso($curso_id) {
        $sql = "SELECT COUNT(*) AS total FROM inscripciones
                WHERE curso_id = '$curso_id'AND estado = 'confirmada'
                ";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudo contar los inscriptos.");
        }

        $fila = mysqli_fetch_assoc($resultado);
        return $fila['total'];
    }
}
?>