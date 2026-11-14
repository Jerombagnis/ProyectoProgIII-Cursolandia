<?php
class ControlInscripcion {
    private $manejadorInscripcion;
    private $manejadorCurso;
    private $manejadorUsuario;

    public function __construct($manejadorInscripcion, $manejadorCurso, $manejadorUsuario) {
        $this->manejadorInscripcion = $manejadorInscripcion;
        $this->manejadorCurso = $manejadorCurso;
        $this->manejadorUsuario = $manejadorUsuario;
    }

  // metodo que inscribe un usuario en un curso y hace un control de las reglas. al final retorna el estado de la inscripcion (pendiente o confirmada)
    public function inscribir($usuario_id, $curso_id) {

        $usuario = $this->manejadorUsuario->buscarPorId($usuario_id); // obtiene un usuario por su id, si no existe lanza una excepcion
        if ($usuario == null) {
            throw new Exception("El usuario no existe.");
        }

        $curso = $this->manejadorCurso->buscarPorId($curso_id); // obtiene un curso por su id, si no existe lanza una excepcion
        if ($curso == null) {
            throw new Exception("El curso no existe.");
        }

        if ($curso->esCreador($usuario_id)) {  //control de que el usuario no sea el creador del curso
            throw new Exception("No podés inscribirte a tu propio curso.");
        }

        if ($curso->estaActivo() != "activo") { //control de que el curso este activo, si esta finalizado o proximamente no se puede inscribir
            throw new Exception("El curso no está activo.");
        }

        
        $existente = $this->manejadorInscripcion->buscarPorUsuarioYCurso($usuario_id, $curso_id); //control de que el usuario no tenga una inscripcion existente en el curso
        if ($existente != null) {
            if ($existente->estaRechazada()) { 
                throw new Exception("El creador rechazó tu solicitud para este curso.");
            }
            throw new Exception("Ya tenés una inscripción en este curso.");
        }

        if (!$usuario->esPro()) { //control de que el usuario no tenga más de 3 inscripciones activas (pendientes o confirmadas) en cursos que no hayan terminado   
            $activas = $this->manejadorInscripcion->contarActivasPorUsuario($usuario_id);
            if ($activas >= 3) {
                throw new Exception("Solo puedes tener 3 inscripciones activas en cursos que no hayan terminado. Para inscribirte a más cursos, pasate a Pro.");
            }
        }

        if ($curso->cupo != "") { // control de que el curso no haya superado el cupo de inscripciones confirmadas
            $confirmadas = $this->manejadorInscripcion->contarConfirmadasPorCurso($curso_id);
            if ($confirmadas >= $curso->cupo) {
                throw new Exception("El curso no tiene más cupo.");
            }
        }

        if ($curso->esGratuito()) { //si es gratuito, la inscripcion se confirma automaticamente, sino queda pendiente que el creador la confirme
            $estado = "confirmada";
        } else {
            $estado = "pendiente";
        }

        $inscripcion = new Inscripcion(null, $usuario_id, $curso_id, $estado); 
        $this->manejadorInscripcion->guardar($inscripcion);

        return $estado;
    }
}
?>