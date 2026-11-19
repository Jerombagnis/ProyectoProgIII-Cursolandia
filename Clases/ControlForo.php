<?php
class ControlForo {
    private $manejadorPregunta;
    private $manejadorCurso;
    private $manejadorInscripcion;
    private $manejadorRespuesta;

    public function __construct($manejadorPregunta, $manejadorCurso, $manejadorInscripcion, $manejadorRespuesta) {
        $this->manejadorPregunta = $manejadorPregunta;
        $this->manejadorCurso = $manejadorCurso;
        $this->manejadorInscripcion = $manejadorInscripcion;
        $this->manejadorRespuesta = $manejadorRespuesta;
    }

    // metodo para controlar si un usuario puede o no participar del foro de un curso
    public function puedeParticipar($curso, $usuario_id) {
        if ($curso->esCreador($usuario_id)) {
            return true;
        }

        $inscripcion = $this->manejadorInscripcion->buscarPorUsuarioYCurso($usuario_id, $curso->id);
        return ($inscripcion != null && $inscripcion->estaConfirmada());
    }

    // metodo para publicar una pregunta en base a las reglas, al final retorna el id de la pregunta
    public function crearPregunta($pregunta) {
        //valido datos
        $pregunta->validar();

        //valido que el curso exista
        $curso = $this->manejadorCurso->buscarPorId($pregunta->curso_id);
        if ($curso == null) {
            throw new Exception("El curso no existe.");
        }

        // valido quien puede participar en el foro, solo inscirptos y creador del curso
        if (!$this->puedeParticipar($curso, $pregunta->usuario_id)) {
            throw new Exception("Solo el docente y los inscriptos confirmados pueden participar en el foro.");
        }

        // todo ok, guarda pregunta 
        return $this->manejadorPregunta->guardar($pregunta);
    }

        // metodo para publicar una respuesta en base a las regla, al final retorna el id de la repsuesta
    public function responder($respuesta) {
        // valida datos
        $respuesta->validar();

        // valido que la pregunta exista
        $pregunta = $this->manejadorPregunta->buscarPorId($respuesta->pregunta_id);
        if ($pregunta == null) {
            throw new Exception("La pregunta no existe.");
        }

        $curso = $this->manejadorCurso->buscarPorId($pregunta->curso_id);
        if ($curso == null) {
            throw new Exception("El curso no existe.");
        }

        // valido quien puede participar en el foro, solo inscirptos y creador del curso
        if (!$this->puedeParticipar($curso, $respuesta->usuario_id)) {
            throw new Exception("Solo el docente y los inscriptos confirmados pueden participar en el foro.");
        }

        // regla una pregunta cerrada no admite más respuestas
        if ($pregunta->estaCerrada()) {
            throw new Exception("La pregunta está cerrada, no admite más respuestas.");
        }

        // control para no responder 2 veces seguidas
        $ultima = $this->manejadorRespuesta->buscarUltimaPorPregunta($pregunta->id);
        if ($ultima != null && $ultima->esAutor($respuesta->usuario_id)) {
            throw new Exception("No podés responder dos veces seguidas. Esperá a que otra persona responda.");
        }

        // si pasó todo, se guarda
        return $this->manejadorRespuesta->guardar($respuesta);
    }
}
?>
