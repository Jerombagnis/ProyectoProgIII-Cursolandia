<?php

session_start();

// Solo pueden entrar usuarios logueados
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include "../Conexiones/conexion.php";
include "../Clases/Usuario.php";
include "../Clases/Curso.php";
include "../Clases/Inscripcion.php";
include "../Clases/Pregunta.php";
include "../Clases/Respuesta.php";
include "../Clases/ControlForo.php";
include "../Manejadores/ManejadorUsuario.php";
include "../Manejadores/ManejadorCurso.php";
include "../Manejadores/ManejadorInscripcion.php";
include "../Manejadores/ManejadorPregunta.php";
include "../Manejadores/ManejadorRespuesta.php";

$pregunta = null;
$curso = null;
$autor = "";
$items = array();
$contenido = "";
$ultimaEsMia = false;
$mensaje = "";
$error = "";
$errorRespuesta = "";

// Mensaje que quedó guardado de una acción exitosa
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}

try {
    // El id de la pregunta viene por la URL
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception("La pregunta no existe.");
    }

    $manejadorUsuario = new ManejadorUsuario($conexion);
    $manejadorCurso = new ManejadorCurso($conexion);
    $manejadorInscripcion = new ManejadorInscripcion($conexion);
    $manejadorPregunta = new ManejadorPregunta($conexion);
    $manejadorRespuesta = new ManejadorRespuesta($conexion);
    $control = new ControlForo($manejadorPregunta, $manejadorCurso, $manejadorInscripcion, $manejadorRespuesta);

    $pregunta = $manejadorPregunta->buscarPorId($_GET['id']);
    if ($pregunta == null) {
        throw new Exception("La pregunta no existe.");
    }

    $curso = $manejadorCurso->buscarPorId($pregunta->curso_id);
    if ($curso == null) {
        throw new Exception("El curso no existe.");
    }

    // Solo el docente y los inscriptos confirmados pueden ver la pregunta
    if (!$control->puedeParticipar($curso, $_SESSION['usuario_id'])) {
        throw new Exception("No tenés acceso al foro de este curso.");
    }

    // Nombre del autor de la pregunta
    $usuarioAutor = $manejadorUsuario->buscarPorId($pregunta->usuario_id);
    $autor = $usuarioAutor->nombre . " " . $usuarioAutor->apellido;

    // Si apretó el botón de responder (el autor sale de la sesión)
    if (isset($_POST['responder'])) {
        // La comilla simple se cambia por ’ para que no rompa la consulta
        $contenido = str_replace("'", "’", trim($_POST['contenido']));

        try {
            $respuesta = new Respuesta(null, $pregunta->id, $_SESSION['usuario_id'], $contenido, 0, null);
            $control->responder($respuesta);

            $_SESSION['mensaje'] = "Respuesta publicada.";

            include "../Conexiones/cerrar_conexion.php";
            header("Location: pregunta.php?id=" . $pregunta->id);
            exit;
        } catch (Exception $e) {
            $errorRespuesta = $e->getMessage();
        }
    }

    // Respuestas de la pregunta
    $items = $manejadorRespuesta->listarPorPregunta($pregunta->id);

    // Si la última respuesta es del usuario que mira, todavía no puede volver a responder
    if (count($items) > 0) {
        $ultimo = end($items);
        $ultimaEsMia = $ultimo['respuesta']->esAutor($_SESSION['usuario_id']);
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

include "../Conexiones/cerrar_conexion.php";
?>
<?php include "includes/header.php"; ?>

<div class="container my-4">

    <?php if ($error != "") { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <a href="index.php" class="btn btn-secondary">Volver al inicio</a>
    <?php } else { ?>

        <p class="text-muted mb-1">
            <a href="foro.php?id=<?php echo $curso->id; ?>">&larr; Foro de <?php echo $curso->titulo; ?></a>
        </p>

        <?php if ($mensaje != "") { ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php } ?>

        <h1 class="h3">
            <?php if ($pregunta->esDestacada()) { ?>
                <span class="badge bg-warning text-dark">Destacada</span>
            <?php } ?>
            <?php echo $pregunta->titulo; ?>
        </h1>

        <p class="text-muted">
            <?php echo $autor; ?>
            <?php if ($curso->esCreador($pregunta->usuario_id)) { ?>
                (docente)
            <?php } ?>
            &middot; <?php echo $pregunta->created_at; ?>
            <?php if ($pregunta->estaCerrada()) { ?>
                <span class="badge bg-secondary">Cerrada</span>
            <?php } else { ?>
                <span class="badge bg-success">Abierta</span>
            <?php } ?>
        </p>

        <div class="card mb-4">
            <div class="card-body" style="white-space: pre-wrap;"><?php echo $pregunta->contenido; ?></div>
        </div>

        <h2 class="h5 mb-3"><?php echo count($items); ?> respuesta(s)</h2>

        <?php if (count($items) == 0) { ?>
            <p>Todavía nadie respondió esta pregunta.</p>
        <?php } else { ?>
            <?php foreach ($items as $item) { ?>
                <?php $respuesta = $item['respuesta']; ?>
                <?php if ($respuesta->estaAceptada()) { ?>
                    <div class="card border-success mb-3">
                <?php } else { ?>
                    <div class="card mb-3">
                <?php } ?>
                    <div class="card-header bg-transparent d-flex justify-content-between">
                        <span>
                            <strong><?php echo $item['autor']; ?></strong>
                            <?php if ($curso->esCreador($respuesta->usuario_id)) { ?>
                                <span class="badge bg-primary">Docente</span>
                            <?php } ?>
                        </span>
                        <small class="text-muted">
                            <?php if ($respuesta->estaAceptada()) { ?>
                                <span class="badge bg-success">Respuesta aceptada</span>
                            <?php } ?>
                            <?php echo $respuesta->created_at; ?>
                        </small>
                    </div>
                    <div class="card-body" style="white-space: pre-wrap;"><?php echo $respuesta->contenido; ?></div>
                </div>
            <?php } ?>
        <?php } ?>

        <h2 class="h5 mt-4">Responder</h2>

        <?php if ($errorRespuesta != "") { ?>
            <div class="alert alert-danger"><?php echo $errorRespuesta; ?></div>
        <?php } ?>

        <?php if ($pregunta->estaCerrada()) { ?>

            <div class="alert alert-secondary">Esta pregunta está cerrada, no admite más respuestas.</div>

        <?php } elseif ($ultimaEsMia) { ?>

            <div class="alert alert-info">Ya respondiste. Cuando otra persona responda, vas a poder escribir de nuevo.</div>

        <?php } else { ?>

            <form method="post" action="pregunta.php?id=<?php echo $pregunta->id; ?>">
                <div class="mb-3">
                    <textarea name="contenido" class="form-control" rows="4" placeholder="Escribí tu respuesta"><?php echo $contenido; ?></textarea>
                </div>
                <button type="submit" name="responder" class="btn btn-primary">Publicar respuesta</button>
            </form>

        <?php } ?>

    <?php } ?>
</div>

<?php include "includes/footer.php"; ?>