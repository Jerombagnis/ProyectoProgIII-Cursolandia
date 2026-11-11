<?php
class ManejadorCurso {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // guarda curso nuevo y devuelve el id que le dio la base
    public function guardar($curso) {
        if ($curso->cupo == "") {
            $cupo = "NULL";
        } else {
            $cupo = $curso->cupo;
        }

        $sql = "INSERT INTO cursos (creador_id, titulo, descripcion, costo, cupo, fecha_inicio, fecha_fin, tipo_acceso)
                VALUES ('$curso->creador_id', '$curso->titulo','$curso->descripcion','$curso->costo',$cupo,'$curso->fecha_inicio',' $curso->fecha_fin','$curso->tipo_acceso')
                ";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudo guardar el curso.");
        }

        $sql = "SELECT id FROM cursos
                WHERE creador_id = '$curso->creador_id'
                ORDER BY id DESC
                LIMIT 1";

        $resultado = mysqli_query($this->conexion, $sql);
        $fila = mysqli_fetch_assoc($resultado);

        return $fila['id'];
    }

    // buscar curso por id, si existe lo retorna, sino devuelve null
    public function buscarPorId($id) {
        $sql = "SELECT * FROM cursos WHERE id = " . $id;

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudo buscar el curso.");
        }

        $fila = mysqli_fetch_assoc($resultado);
        if (!$fila) {
            return null;
        }

        return new Curso($fila['id'],$fila['creador_id'],$fila['titulo'],$fila['descripcion'],$fila['costo'],$fila['cupo'],$fila['fecha_inicio'],$fila['fecha_fin'],$fila['tipo_acceso']);
    }

    // control cantidad de cursos vigentes por creador
    public function contarVigentesPorCreador($creador_id) {
        $sql = "SELECT COUNT(*) AS total FROM cursos
                WHERE creador_id = " . $creador_id . "
                AND fecha_fin >= CURDATE()";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudo contar los cursos.");
        }

        $fila = mysqli_fetch_assoc($resultado);
        return $fila['total'];
    }
}
?>