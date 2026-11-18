<?php
class ManejadorArchivo {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // metodo para guardar un archivo nuevo y devuelve su id
    public function guardar($archivo) {
        $sql = "INSERT INTO archivos (curso_id, nombre_original, ruta, tamano_bytes)
                VALUES ('$archivo->curso_id','$archivo->nombre_original','$archivo->ruta','$archivo->tamano_bytes')
                ";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudo guardar el archivo.");
        }

        return mysqli_insert_id($this->conexion);
    }

    // metodo para buscar un archivo por id. Devuelve un Archivo o  si no existe
    public function buscarPorId($id) {
        $sql = "SELECT * FROM archivos WHERE id = '$id'
                ";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudo buscar el archivo.");
        }

        $fila = mysqli_fetch_assoc($resultado);
        if (!$fila) {
            return null;
        }

        return new Archivo($fila['id'], $fila['curso_id'], $fila['nombre_original'], $fila['ruta'], $fila['tamano_bytes'], $fila['created_at']);
    }

    // metodo para listar los archivos de un curso del más antiguo al más nuevo
    public function listarPorCurso($curso_id) {
        $sql = "SELECT * FROM archivos
                WHERE curso_id = '$curso_id'
                ORDER BY created_at";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudo cargar el material del curso.");
        }

        $archivos = array();
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $archivos[] = new Archivo($fila['id'], $fila['curso_id'], $fila['nombre_original'], $fila['ruta'], $fila['tamano_bytes'], $fila['created_at']);
        }
        return $archivos;
    }

    // metodo para contar cuántos archivos tiene un curso
    public function contarPorCurso($curso_id) {
        $sql = "SELECT COUNT(*) AS total FROM archivos WHERE curso_id = " . $curso_id;

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudo contar los archivos.");
        }

        $fila = mysqli_fetch_assoc($resultado);
        return $fila['total'];
    }

    // metodo para obtener el total de bytes de archivos de un curso
    public function sumarTamanoPorCurso($curso_id) {
        $sql = "SELECT COALESCE(SUM(tamano_bytes), 0) AS total FROM archivos WHERE curso_id = '$curso_id'";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudo calcular el espacio usado.");
        }

        $fila = mysqli_fetch_assoc($resultado);
        return $fila['total'];
    }

    // metodo para eliminar un archivo (el archivo físico se encarga de borrarlo la página)
    public function eliminar($id) {
        $sql = "DELETE FROM archivos WHERE id = '$id'";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudo eliminar el archivo.");
        }
    }
}
?>