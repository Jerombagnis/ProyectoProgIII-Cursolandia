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


    public function listarEtiquetas() {
        $sql = "SELECT id, nombre FROM etiquetas ORDER BY nombre";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudieron cargar las etiquetas.");
        }

        $etiquetas = array();
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $etiquetas[] = $fila;
        }
        return $etiquetas;
    }

    // guardar etiquetas de un curso 
    public function guardarEtiquetas($curso_id, $etiquetas_ids) {
        foreach ($etiquetas_ids as $etiqueta_id) {
            if (is_numeric($etiqueta_id)) {
                $sql = "INSERT INTO curso_etiquetas (curso_id, etiqueta_id)
                        VALUES ('$curso_id', '$etiqueta_id')";

                $resultado = mysqli_query($this->conexion, $sql);
                if (!$resultado) {
                    throw new Exception("No se pudieron guardar las etiquetas del curso.");
                }
            }
        }
    }

    // obtener etiquetas de un curso
    public function etiquetasDeCurso($curso_id) {
        $sql = "SELECT e.id, e.nombre
                FROM etiquetas e
                INNER JOIN curso_etiquetas ce ON ce.etiqueta_id = e.id
                WHERE ce.curso_id = '$curso_id'
                ORDER BY e.nombre";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudieron cargar las etiquetas del curso.");
        }

        $etiquetas = array();
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $etiquetas[] = $fila;
        }
        return $etiquetas;
    }    

    // metodo que lista cursos publicos no finalizados y retorna el resultado 
    public function listarPublicos() {
        $sql = "SELECT * FROM cursos
                WHERE tipo_acceso = 'publico' AND fecha_fin >= CURDATE()
                ORDER BY fecha_inicio";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudieron cargar los cursos.");
        }

        return $this->armarCursos($resultado);
    }

    // metodo para listar cursos destacados (3 maximo) de forma aleatoria, asi evita la sobrecarga de cursos recomendados
    public function listarDestacados() {
        $sql = "SELECT c.*
                FROM cursos c
                INNER JOIN usuarios u ON u.id = c.creador_id
                WHERE c.tipo_acceso = 'publico' AND c.fecha_fin >= CURDATE() AND u.vencimiento_pro >= CURDATE()
                ORDER BY RAND ()
                LIMIT 3";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudieron cargar los cursos destacados.");
        }

        return $this->armarCursos($resultado);
    }

    // metodo para buscar cursos por titulo o etiquetas que posea, solo muestra publicos no finalizados
    public function buscarPublicos($texto) {
        $sql = "SELECT DISTINCT c.*
                FROM cursos c
                LEFT JOIN curso_etiquetas ce ON ce.curso_id = c.id
                LEFT JOIN etiquetas e ON e.id = ce.etiqueta_id
                WHERE c.tipo_acceso = 'publico' AND c.fecha_fin >= CURDATE() AND (c.titulo LIKE '%" . $texto . "%' OR e.nombre LIKE '%" . $texto . "%')
                ORDER BY c.fecha_inicio";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudo realizar la búsqueda.");
        }

        return $this->armarCursos($resultado);
    }

    // metodo para listar cursos recomendados para un usuario, basado en sus intereses y etiquetas de los cursos, solo muestra publicos no finalizados y que no haya creado ni se haya inscripto
    public function listarRecomendados($usuario_id) {
        $sql = "SELECT c.*, COUNT(*) AS coincidencias
                FROM cursos c
                INNER JOIN curso_etiquetas ce ON ce.curso_id = c.id
                INNER JOIN usuario_intereses ui ON ui.etiqueta_id = ce.etiqueta_id
                WHERE ui.usuario_id = '$usuario_id'
                AND c.tipo_acceso = 'publico'
                AND c.fecha_fin >= CURDATE()
                AND c.creador_id <> '$usuario_id'
                AND c.id NOT IN (SELECT curso_id FROM inscripciones WHERE usuario_id = '$usuario_id')
                GROUP BY c.id
                ORDER BY coincidencias DESC, c.fecha_inicio
                LIMIT 3
                ";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudieron cargar los cursos recomendados.");
        }

        return $this->armarCursos($resultado);
    }

    // metodo para listar cursos creados por un usuario
    public function listarPorCreador($creador_id) {
        $sql = "SELECT * 
                FROM cursos
                WHERE creador_id = '$creador_id'
                ORDER BY fecha_inicio DESC";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudieron cargar tus cursos.");
        }

        return $this->armarCursos($resultado);
    }

    // metodo privado para armar un array de objetos Curso a partir del resultado de una consulta
    private function armarCursos($resultado) {
        $cursos = array();
        while ($fila = mysqli_fetch_assoc($resultado)) {   
            $cursos[] = new Curso(
                $fila['id'],
                $fila['creador_id'],
                $fila['titulo'],
                $fila['descripcion'],
                $fila['costo'],
                $fila['cupo'],
                $fila['fecha_inicio'],
                $fila['fecha_fin'],
                $fila['tipo_acceso']
            );
        }
        return $cursos;
    }
}
?>