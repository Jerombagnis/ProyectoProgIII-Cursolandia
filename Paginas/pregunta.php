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


    if (isset($_POST['cambiar_destacada'])) {
        try {
            $control->cambiarDestacada($pregunta->id, $_SESSION['usuario_id']);
            $_SESSION['mensaje'] = "Se actualizó el destacado de la pregunta.";
        } catch (Exception $e) {
            $_SESSION['mensaje'] = $e->getMessage();
        }

        header("Location: pregunta.php?id=" . $pregunta->id);
        exit;
    }

    if (isset($_POST['cambiar_estado'])) {
        try {
            $control->cambiarEstado($pregunta->id, $_SESSION['usuario_id']);
            $_SESSION['mensaje'] = "Se actualizó el estado de la pregunta.";
        } catch (Exception $e) {
            $_SESSION['mensaje'] = $e->getMessage();
        }

        header("Location: pregunta.php?id=" . $pregunta->id);
        exit;
    }

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

        <div class="pregunta-header">
            <div>
                <span class="section-kicker">COMUNIDAD DEL CURSO</span>
                <h1 class="pregunta-page-title">Detalle de la pregunta</h1>
                <p class="section-description"><?php echo $curso->titulo; ?></p>
            </div>

            <a href="foro.php?id=<?php echo $curso->id; ?>" class="btn-curso-secondary">
                Volver al foro
            </a>
        </div>

        <?php if ($mensaje != "") { ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php } ?>

        <div class="pregunta-titulo-container">
            <div class="pregunta-etiquetas">
                <?php if ($pregunta->esDestacada()) { ?>
                    <span class="curso-badge badge-finalizado">★ Destacada</span>
                <?php } ?>

                <?php if ($pregunta->estaCerrada()) { ?>
                    <span class="curso-badge badge-rechazada">Cerrada</span>
                <?php } else { ?>
                    <span class="curso-badge badge-activo">Abierta</span>
                <?php } ?>
            </div>

            <h2 class="pregunta-titulo">
                <?php echo $pregunta->titulo; ?>
            </h2>
        </div>

        <?php if ($curso->esCreador($_SESSION['usuario_id'])) { ?>
            <div class="pregunta-acciones">
                <form method="POST">
                    <button type="submit" name="cambiar_destacada" class="btn btn-outline-primary">
                        <?php echo $pregunta->esDestacada() ? "Quitar destacado" : "Destacar pregunta"; ?>
                    </button>
                </form>

                <form method="POST">
                    <button type="submit" name="cambiar_estado" class="btn <?php echo $pregunta->estaAbierta() ? 'btn-outline-danger' : 'btn-outline-success'; ?>">
                        <?php echo $pregunta->estaAbierta() ? "Cerrar pregunta" : "Reabrir pregunta"; ?>
                    </button>
                </form>
            </div>
        <?php } ?>

        <p class="text-muted pregunta-autor-info">
            <?php echo $autor; ?>
            <?php if ($curso->esCreador($pregunta->usuario_id)) { ?>
                (docente)
            <?php } ?>
            &middot; <?php echo $pregunta->created_at; ?>
        </p>

        <div class="pregunta-contenido-card">
            <div class="pregunta-contenido">
                <?php echo $pregunta->contenido; ?>
            </div>
        </div>

        <div class="respuestas-header">
            <div>
                <span class="section-kicker">INTERCAMBIO DE IDEAS</span>
                <h2 class="respuestas-title">Respuestas</h2>
            </div>

            <span class="respuestas-count">
                <?php echo count($items); ?>
            </span>
        </div>

        <?php if (count($items) == 0) { ?>
            <p>Todavía nadie respondió esta pregunta.</p>
        <?php } else { ?>
            <?php foreach ($items as $item) { ?>
                <?php $respuesta = $item['respuesta']; ?>
                <div class="respuesta-card <?php echo $respuesta->estaAceptada() ? 'respuesta-aceptada' : ''; ?>">

                    <div class="respuesta-card-header">
                        <div class="respuesta-autor">
                            <strong><?php echo $item['autor']; ?></strong>

                            <?php if ($curso->esCreador($respuesta->usuario_id)) { ?>
                                <span class="curso-badge badge-activo">Docente</span>
                            <?php } ?>
                        </div>

                        <div class="respuesta-meta">
                            <?php if ($respuesta->estaAceptada()) { ?>
                                <span class="respuesta-badge-aceptada">✓ Respuesta aceptada</span>
                            <?php } ?>

                            <span><?php echo $respuesta->created_at; ?></span>
                        </div>
                    </div>

                    <div class="respuesta-contenido">
                        <?php echo $respuesta->contenido; ?>
                    </div>

                </div>                
            <?php } ?>
        <?php } ?>

        <div class="responder-header">
            <span class="section-kicker">PARTICIPÁ DE LA CONVERSACIÓN</span>
            <h2 class="responder-title">Tu respuesta</h2>
            <p class="section-description">Compartí tus conocimientos o ayudá a resolver la consulta.</p>
        </div>

        <?php if ($errorRespuesta != "") { ?>
            <div class="foro-alert foro-alert-error">
                <?php echo $errorRespuesta; ?>
            </div>
        <?php } ?>

        <?php if ($pregunta->estaCerrada()) { ?>

            <div class="foro-alert foro-alert-info">
                Esta pregunta está cerrada y no admite más respuestas.
            </div>

        <?php } elseif ($ultimaEsMia) { ?>

            <div class="foro-alert foro-alert-info">
                Ya respondiste. Cuando otra persona responda, vas a poder escribir de nuevo.
            </div>

        <?php } else { ?>

        <div class="responder-card">
            <form method="post" action="pregunta.php?id=<?php echo $pregunta->id; ?>" class="responder-form">

                <div class="responder-form-group">
                    <label for="contenido-respuesta" class="foro-label">Tu respuesta</label>

                    <textarea
                        id="contenido-respuesta"
                        name="contenido"
                        class="form-control"
                        rows="5"
                        placeholder="Escribí tu respuesta..."
                    ><?php echo $contenido; ?></textarea>
                </div>

                <button type="submit" name="responder" class="btn-cursolandia">
                    Publicar respuesta
                </button>

            </form>
        </div>

        <?php } ?>

    <?php } ?>
</div>

<?php include "includes/footer.php"; ?>