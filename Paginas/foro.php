<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

        <h1 class="mb-1">Foro</h1>
        <p class="text-muted"><?php echo $curso->titulo; ?></p>

        <?php if ($mensaje != "") { ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php } ?>

        <h2 class="h4">Hacer una pregunta</h2>

        <?php if ($errorPregunta != "") { ?>
            <div class="alert alert-danger"><?php echo $errorPregunta; ?></div>
        <?php } ?>

        <form method="post" action="foro.php?id=<?php echo $curso->id; ?>" class="mb-5">
            <div class="mb-3">
                <label class="form-label">Título</label>
                <input type="text" name="titulo" class="form-control" value="<?php echo $titulo; ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Detalle</label>
                <textarea name="contenido" class="form-control" rows="4"><?php echo $contenido; ?></textarea>
            </div>
            <button type="submit" name="preguntar" class="btn btn-primary">Publicar pregunta</button>
        </form>

        <h2 class="h4">Preguntas</h2>

        <?php if (count($items) == 0) { ?>
            <p>Todavía no hay preguntas. ¡Hacé la primera!</p>
        <?php } else { ?>
            <div class="list-group mb-4">
                <?php foreach ($items as $item) { ?>
                    <?php $pregunta = $item['pregunta']; ?>
                    <a href="pregunta.php?id=<?php echo $pregunta->id; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h3 class="h6 mb-1">
                                    <?php if ($pregunta->esDestacada()) { ?>
                                        <span class="badge bg-warning text-dark">Destacada</span>
                                    <?php } ?>
                                    <?php echo $pregunta->titulo; ?>
                                </h3>
                                <small class="text-muted">
                                    <?php echo $item['autor']; ?>
                                    <?php if ($curso->esCreador($pregunta->usuario_id)) { ?>
                                        (docente)
                                    <?php } ?>
                                    &middot; <?php echo $pregunta->created_at; ?>
                                </small>
                            </div>
                            <div class="text-end">
                                <?php if ($pregunta->estaCerrada()) { ?>
                                    <span class="badge bg-secondary">Cerrada</span>
                                <?php } ?>
                                <span class="badge text-bg-light border"><?php echo $item['respuestas']; ?> respuesta(s)</span>
                            </div>
                        </div>
                    </a>
                <?php } ?>
            </div>
        <?php } ?>

        <a href="curso.php?id=<?php echo $curso->id; ?>" class="btn btn-secondary">Volver al curso</a>

    <?php } ?>
</div>

<?php include "includes/footer.php"; ?>