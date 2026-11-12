<?php
class ControlCurso {
    private $manejadorCurso;
    private $manejadorUsuario;

    public function __construct($manejadorCurso, $manejadorUsuario) {
        $this->manejadorCurso = $manejadorCurso;
        $this->manejadorUsuario = $manejadorUsuario;
    }

    public function crear($curso, $etiquetas) {
        $curso->validar();

        // busca el usuario creador del curso
        $usuario = $this->manejadorUsuario->buscarPorId($curso->creador_id);
        if ($usuario == null) {
            throw new Exception("El usuario creador no existe.");
        }

        // control de cantidad de cursos de creador
        if (!$usuario->esPro()) {
            $vigentes = $this->manejadorCurso->contarVigentesPorCreador($usuario->id);
            if ($vigentes >= 3) {
                throw new Exception("Solo podes tener hasta 3 cursos activos. Adquiere el plan pro para crear más.");
            }
        }

        $id = $this->manejadorCurso->guardar($curso);
        $this->manejadorCurso->guardarEtiquetas($id, $etiquetas);

        return $id;
    }
}
?>