<?php
class ControlArchivo {
    private $manejadorArchivo;
    private $manejadorCurso;
    private $manejadorUsuario;
    private $manejadorInscripcion;

    public function __construct($manejadorArchivo, $manejadorCurso, $manejadorUsuario, $manejadorInscripcion) {
        $this->manejadorArchivo = $manejadorArchivo;
        $this->manejadorCurso = $manejadorCurso;
        $this->manejadorUsuario = $manejadorUsuario;
        $this->manejadorInscripcion = $manejadorInscripcion;
    }

    // metodo para rgistrar un archivo nuevo en un curso aplicando todas las reglas. al final retorna el id.
    public function subir($archivo, $usuario_id) {
        // control de que el curso exista y que el usuario sea le creador del mismo. 
        $curso = $this->manejadorCurso->buscarPorId($archivo->curso_id);
        if ($curso == null) {
            throw new Exception("El curso no existe.");
        }
        if (!$curso->esCreador($usuario_id)) {
            throw new Exception("Solo el creador del curso puede subir material.");
        }

        $usuario = $this->manejadorUsuario->buscarPorId($usuario_id);
        if ($usuario == null) {
            throw new Exception("El usuario no existe.");
        }

        // control limites cantidad y tamaño en base a plan gratuito o pro
        if (!$usuario->esPro()) {
            $cantidad = $this->manejadorArchivo->contarPorCurso($curso->id);
            if ($cantidad >= 5) {
                throw new Exception("Con el plan gratuito podés subir hasta 5 archivos por curso. Pasate a Pro para subir más.");
            }

            if ($archivo->tamano_bytes > 5 * 1024 * 1024) {
                throw new Exception("Con el plan gratuito cada archivo puede pesar hasta 5 MB.");
            }

            $usado = $this->manejadorArchivo->sumarTamanoPorCurso($curso->id);
            if ($usado + $archivo->tamano_bytes > 10 * 1024 * 1024) {
                throw new Exception("Con el plan gratuito el material de un curso no puede superar los 10 MB en total.");
            }
        }


        return $this->manejadorArchivo->guardar($archivo); //si cumple con todas las reglas, se guarda 
    }

    // metodo para borrar un archivo por el creador del curso, cuando lo borra retorna el nombre para que la pagina lo borre.
    public function eliminar($archivo_id, $usuario_id) {
        $archivo = $this->manejadorArchivo->buscarPorId($archivo_id);
        if ($archivo == null) {
            throw new Exception("El archivo no existe.");
        }

        $curso = $this->manejadorCurso->buscarPorId($archivo->curso_id);
        if ($curso == null) {
            throw new Exception("El curso no existe.");
        }
        if (!$curso->esCreador($usuario_id)) {
            throw new Exception("No tenés permiso para eliminar este archivo.");
        }

        $this->manejadorArchivo->eliminar($archivo_id);

        return $archivo->ruta;
    }

    // metodo para controlar si un usuario puede ver o no material (si esta o no inscirpto en el curso)
    public function puedeVerMaterial($curso, $usuario_id) {
        // si es creador lo ve siempre
        if ($curso->esCreador($usuario_id)) {
            return true;
        }

        // usuarios inscriptos lo ven siempre (aun cuando el curso haya finalizado)
        if (!$curso->haComenzado()) {
            return false;
        }

        // solo usuarios confirmados (inscriptos)
        $inscripcion = $this->manejadorInscripcion->buscarPorUsuarioYCurso($usuario_id, $curso->id);
        return ($inscripcion != null && $inscripcion->estaConfirmada());
    }
}
?>