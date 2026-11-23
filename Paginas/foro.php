<?php

session_start();


// solo usuarios logueados
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include "../Conexiones/conexion.php";
include "../Clases/Curso.php";
include "../Clases/Inscripcion.php";
include "../Clases/Pregunta.php";
include "../Clases/ControlForo.php";
include "../Manejadores/ManejadorCurso.php";
include "../Manejadores/ManejadorInscripcion.php";
include "../Manejadores/ManejadorPregunta.php";
include "../Manejadores/ManejadorRespuesta.php";


$curso = null;
$items = array();
$titulo = "";
$contenido = "";
$mensaje = "";
$error = "";
$errorPregunta = "";

if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}

try {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception("El curso no existe.");
    }

    $manejadorCurso = new ManejadorCurso($conexion);
    $manejadorInscripcion = new ManejadorInscripcion($conexion);
    $manejadorPregunta = new ManejadorPregunta($conexion);
    $manejadorRespuesta = new ManejadorRespuesta($conexion);
    $control = new ControlForo($manejadorPregunta, $manejadorCurso, $manejadorInscripcion, $manejadorRespuesta);

    $curso = $manejadorCurso->buscarPorId($_GET['id']);
    if ($curso == null) {
        throw new Exception("El curso no existe.");
    }

    // control solo docente y los inscriptos confirmados entran al foro
    if (!$control->puedeParticipar($curso, $_SESSION['usuario_id'])) {
        throw new Exception("No tenés acceso al foro de este curso. Es solo para el docente y los inscriptos confirmados.");
    }

    if (isset($_POST['preguntar'])) {
        // cambio la comilla simple por ’ para que no rompa la consulta
        $titulo = str_replace("'", "’", trim($_POST['titulo']));
        $contenido = str_replace("'", "’", trim($_POST['contenido']));

        try {
            $pregunta = new Pregunta(null, $curso->id, $_SESSION['usuario_id'], $titulo, $contenido, "abierta", 0, null);
            $id = $control->crearPregunta($pregunta);

            $_SESSION['mensaje'] = "Pregunta publicada.";

            include "../Conexiones/cerrar_conexion.php";
            header("Location: pregunta.php?id=" . $id);
            exit;
        } catch (Exception $e) {
            $errorPregunta = $e->getMessage();
        }
    }

    $items = $manejadorPregunta->listarPorCurso($curso->id);
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

        <div class="foro-header">

            <div>
                <span class="section-kicker">COMUNIDAD DEL CURSO</span>

                <h1 class="foro-title">Foro de consultas</h1>

                <p class="section-description">
                    <?php echo $curso->titulo; ?>
                </p>
            </div>

            <a href="curso.php?id=<?php echo $curso->id; ?>" class="btn-curso-secondary">
                Volver al curso
            </a>

        </div>

        <?php if ($mensaje != "") { ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php } ?>

        <section class="foro-form-card">

            <div class="foro-section-heading">
                <div>
                    <span class="section-kicker">PARTICIPÁ</span>
                    <h2 class="foro-section-title">Hacer una pregunta</h2>
                    <p class="foro-description">
                        Compartí tus dudas con el docente y los demás participantes del curso.
                    </p>
                </div>
            </div>

            <?php if ($errorPregunta != "") { ?>
                <div class="foro-alert foro-alert-error">
                    <?php echo $errorPregunta; ?>
                </div>
            <?php } ?>

            <form method="post" action="foro.php?id=<?php echo $curso->id; ?>" class="foro-form">

                <div class="foro-form-group">
                    <label class="foro-label">Título</label>
                    <input
                        type="text"
                        name="titulo"
                        class="form-control"
                        value="<?php echo $titulo; ?>"
                        placeholder="¿Sobre qué tema tenés una duda?"
                    >
                </div>

                <div class="foro-form-group">
                    <label class="foro-label">Detalle</label>
                    <textarea
                        name="contenido"
                        class="form-control"
                        rows="4"
                        placeholder="Desarrollá tu pregunta con el mayor detalle posible..."
                    ><?php echo $contenido; ?></textarea>
                </div>

                <button type="submit" name="preguntar" class="btn-cursolandia">
                    Publicar pregunta
                </button>

            </form>

        </section>

        <div class="foro-listado-header">
            <div>
                <span class="section-kicker">INTERCAMBIO DE CONOCIMIENTOS</span>
                <h2 class="foro-section-title">Preguntas del curso</h2>
            </div>

            <span class="foro-total-preguntas">
                <?php echo count($items); ?>
                <?php echo count($items) == 1 ? 'pregunta' : 'preguntas'; ?>
            </span>
        </div>

        <?php if (count($items) == 0) { ?>

            <div class="foro-empty-state">
                <div class="foro-empty-icon">
                    <i class="bi bi-chat-square-text"></i>
                </div>

                <h3>Todavía no hay preguntas</h3>

                <p>
                    Este espacio está esperando su primera consulta.
                    ¡Animate a participar!
                </p>
            </div>

        <?php } else { ?>

            <div class="foro-preguntas-lista">

                <?php foreach ($items as $item) { ?>
                    <?php $pregunta = $item['pregunta']; ?>

                    <a href="pregunta.php?id=<?php echo $pregunta->id; ?>"class="foro-pregunta-card">

                        <div class="foro-pregunta-contenido">

                            <div class="foro-pregunta-etiquetas">
                                <?php if ($pregunta->esDestacada()) { ?>
                                    <span class="foro-badge foro-badge-destacada">
                                        Destacada
                                    </span>
                                <?php } ?>

                                <?php if ($pregunta->estaCerrada()) { ?>
                                    <span class="foro-badge foro-badge-cerrada">
                                        Cerrada
                                    </span>
                                <?php } ?>
                            </div>

                            <h3 class="foro-pregunta-titulo">
                                <?php echo $pregunta->titulo; ?>
                            </h3>

                            <div class="foro-pregunta-meta">

                                <span>
                                    <?php echo $item['autor']; ?>

                                    <?php if ($curso->esCreador($pregunta->usuario_id)) { ?>
                                        <span class="foro-autor-docente">Docente</span>
                                    <?php } ?>
                                </span>

                                <span class="foro-meta-separador">·</span>

                                <span><?php echo $pregunta->created_at; ?></span>

                            </div>

                        </div>

                        <div class="foro-pregunta-respuestas">
                            <span class="foro-respuestas-numero">
                                <?php echo $item['respuestas']; ?>
                            </span>

                            <span class="foro-respuestas-texto">
                                <?php echo $item['respuestas'] == 1 ? 'respuesta' : 'respuestas'; ?>
                            </span>
                        </div>

                    </a>

                <?php } ?>

            </div>

        <?php } ?>

    <?php } ?>
</div>

<?php include "includes/footer.php"; ?>