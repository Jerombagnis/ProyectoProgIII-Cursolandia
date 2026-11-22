<?php
session_start();

include "../Conexiones/conexion.php";
include "../Clases/Curso.php";
include "../Clases/Usuario.php";
include "../Clases/Inscripcion.php";
include "../Clases/Archivo.php";
include "../Clases/Pregunta.php";
include "../Clases/ControlForo.php";
include "../Clases/ControlInscripcion.php";
include "../Clases/ControlArchivo.php";
include "../Manejadores/ManejadorCurso.php";
include "../Manejadores/ManejadorUsuario.php";
include "../Manejadores/ManejadorInscripcion.php";
include "../Manejadores/ManejadorArchivo.php";
include "../Manejadores/ManejadorPregunta.php";
include "../Manejadores/ManejadorRespuesta.php";

$curso = null;
$etiquetas = array ();
$inscripcion = null;
$archivo = array (); 
$puedeVerMaterial = false; 
$puedeParticipar = false; 
$mensaje = "";
$error = "";
$errorInscripcion = "";

// mensaje de inscripcion
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}

try {
    
    //id llega por url, se controla que exista y que sea un numero, sino se lanza una excepcion
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception("El curso no existe.");
    }

    $manejadorCurso = new ManejadorCurso($conexion);
    $manejadorUsuario = new ManejadorUsuario($conexion);
    $manejadorInscripcion = new ManejadorInscripcion($conexion);
    $manejadorArchivo = new ManejadorArchivo($conexion); 
    $manejadorPregunta = new ManejadorPregunta($conexion);
    $manejadorRespuesta = new ManejadorRespuesta($conexion);


    $curso = $manejadorCurso->buscarPorId($_GET['id']);

    if ($curso == null) {
        throw new Exception("El curso no existe.");
    }

    // control que procesa la inscripcion del usuario al curso, si el usuario esta logueado y presiona el boton de inscribirse
    if (isset($_POST['inscribirse']) && isset($_SESSION['usuario_id'])) {
        $control = new ControlInscripcion($manejadorInscripcion, $manejadorCurso, $manejadorUsuario);

        try {
            $estado = $control->inscribir($_SESSION['usuario_id'], $curso->id);

            if ($estado == "confirmada") {
                $_SESSION['mensaje'] = "¡Te inscribiste al curso!";
            } else {
                $_SESSION['mensaje'] = "Tu solicitud quedó pendiente hasta que el creador la confirme.";
            }

            include "../Conexiones/cerrar_conexion.php";
            header("Location: curso.php?id=" . $curso->id); 
            exit;
        } catch (Exception $e) {
            $errorInscripcion = $e->getMessage();
        }
    }

    // si el usuario esta logueado se busca si ya tiene una inscripcion en el curso y la guarda en la variable $inscripcion, sino queda en null
    if (isset($_SESSION['usuario_id'])) {
        $inscripcion = $manejadorInscripcion->buscarPorUsuarioYCurso($_SESSION['usuario_id'], $curso->id);
    }

    $etiquetas = $manejadorCurso->etiquetasDeCurso($curso->id);

    // si el usuario esta logueado, se busca su inscripción y si puede ver el material
    if (isset($_SESSION['usuario_id'])) {
        $inscripcion = $manejadorInscripcion->buscarPorUsuarioYCurso($_SESSION['usuario_id'], $curso->id);

        $controlArchivo = new ControlArchivo($manejadorArchivo, $manejadorCurso, $manejadorUsuario, $manejadorInscripcion);
        $puedeVerMaterial = $controlArchivo->puedeVerMaterial($curso, $_SESSION['usuario_id']);

        if ($puedeVerMaterial) {
            $archivos = $manejadorArchivo->listarPorCurso($curso->id);
        }

        $controlForo = new ControlForo($manejadorPregunta, $manejadorCurso, $manejadorInscripcion, $manejadorRespuesta);
        $puedeParticipar = $controlForo->puedeParticipar($curso, $_SESSION['usuario_id']);

    }    
} catch (Exception $e) {
    $error = $e->getMessage();
}

include "../Conexiones/cerrar_conexion.php";
?>
<?php include "includes/header.php"; ?>

<!-- .contenido de la pagina, si hay un error se muestra el mensaje de error y un boton para volver al inicio, sino se muestran los datos del curso y el boton de inscribirse si corresponde -->

<div class="container my-4">

    <?php if ($error != "") { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <a href="index.php" class="btn btn-secondary">Volver al inicio</a>
    <?php } else { ?>

        <?php if ($mensaje != "") { ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php } ?>

        <?php if ($errorInscripcion != "") { ?>
            <div class="alert alert-danger"><?php echo $errorInscripcion; ?></div>
        <?php } ?>

        <section class="curso-detail-hero">

            <span class="curso-detail-kicker">DETALLE DEL CURSO</span>

            <h1 class="curso-detail-title">
                <?php echo $curso->titulo; ?>
            </h1>

            <div class="curso-detail-badges">

                <?php if ($curso->esGratuito()) { ?>
                    <span class="curso-badge badge-gratuito">Gratuito</span>
                <?php } else { ?>
                    <span class="curso-badge badge-precio">
                        $<?php echo $curso->costo; ?>
                    </span>
                <?php } ?>

                <?php if ($curso->esPrivado()) { ?>
                    <span class="curso-badge badge-privado">Privado</span>
                <?php } else { ?>
                    <span class="curso-badge badge-publico">Público</span>
                <?php } ?>

                <?php if ($curso->estaActivo() == "proximamente") { ?>
                    <span class="curso-badge badge-proximamente">Próximamente</span>
                <?php } elseif ($curso->estaActivo() == "activo") { ?>
                    <span class="curso-badge badge-activo">Activo</span>
                <?php } else { ?>
                    <span class="curso-badge badge-finalizado">Finalizado</span>
                <?php } ?>

            </div>

            <p class="curso-detail-description">
                <?php echo $curso->descripcion; ?>
            </p>

            <?php if (count($etiquetas) > 0) { ?>
                <div class="curso-tags">
                    <?php foreach ($etiquetas as $etiqueta) { ?>
                        <span class="curso-tag">
                            <?php echo $etiqueta['nombre']; ?>
                        </span>
                    <?php } ?>
                </div>
            <?php } ?>

        </section>

        <div class="curso-info-card">

            <h2 class="curso-subtitle">Información del curso</h2>

            <div class="curso-info-grid">

                <div class="curso-info-item">
                    <span class="curso-info-label">Fecha de inicio</span>
                    <strong><?php echo $curso->fecha_inicio; ?></strong>
                </div>

                <div class="curso-info-item">
                    <span class="curso-info-label">Fecha de finalización</span>
                    <strong><?php echo $curso->fecha_fin; ?></strong>
                </div>

                <div class="curso-info-item">
                    <span class="curso-info-label">Cupo</span>
                    <strong>
                        <?php
                        if ($curso->cupo == "") {
                            echo "Sin límite";
                        } else {
                            echo $curso->cupo;
                        }
                        ?>
                    </strong>
                </div>

            </div>

        </div>

        <aside class="curso-detail-sidebar">

            <div class="curso-action-card">

                <span class="curso-action-kicker">TU EXPERIENCIA DE APRENDIZAJE</span>

                <h2 class="curso-action-title">Inscripción al curso</h2>

                <?php if (!isset($_SESSION['usuario_id'])) { ?>

                    <p>Iniciá sesión para poder inscribirte y acceder al contenido.</p>

                    <a href="login.php" class="btn-cursolandia btn-curso-full">
                        Iniciar sesión
                    </a>

                <?php } elseif ($curso->esCreador($_SESSION['usuario_id'])) { ?>

                    <div class="curso-status-message">
                        Este curso es tuyo.
                    </div>

                    <a href="solicitudes.php?id=<?php echo $curso->id; ?>" class="btn-cursolandia btn-curso-full">
                        Ver solicitudes e inscriptos
                    </a>

                    <a href="material_curso.php?id=<?php echo $curso->id; ?>" class="btn-curso-secondary btn-curso-full">
                        Administrar material
                    </a>

                <?php } elseif ($inscripcion != null) { ?>

                    <?php if ($inscripcion->estaConfirmada()) { ?>

                        <div class="curso-status-message status-confirmada">
                            <strong>¡Ya estás inscripto!</strong>
                            <span>Tenés acceso al curso.</span>
                        </div>

                    <?php } elseif ($inscripcion->estaPendiente()) { ?>

                        <div class="curso-status-message status-pendiente">
                            <strong>Inscripción pendiente</strong>
                            <span>El creador debe confirmar tu solicitud.</span>
                        </div>

                    <?php } else { ?>

                        <div class="curso-status-message status-rechazada">
                            <strong>Solicitud rechazada</strong>
                            <span>El creador rechazó tu inscripción.</span>
                        </div>

                    <?php } ?>

                <?php } elseif ($curso->estaActivo() == "activo") { ?>

                    <p>Ya podés formar parte de este curso. ¡Inscribite!</p>

                    <form method="post" action="curso.php?id=<?php echo $curso->id; ?>">
                        <button type="submit" name="inscribirse" class="btn-cursolandia btn-curso-full">
                            Inscribirme
                        </button>
                    </form>

                <?php } elseif ($curso->estaActivo() == "proximamente") { ?>

                    <div class="curso-status-message status-pendiente">
                        Este curso todavía no está disponible para inscripción.
                    </div>

                <?php } else { ?>

                    <div class="curso-status-message status-rechazada">
                        Este curso finalizó y ya no admite inscripciones.
                    </div>

                <?php } ?>

            </div>

        </aside>

        <?php } ?>

        <?php if ($puedeParticipar) { ?>
            <p class="mt-4">
                <a href="foro.php?id=<?php echo $curso->id; ?>" class="btn btn-outline-primary">Ir al foro del curso</a>
            </p>
        <?php } ?>

        <?php if ($puedeVerMaterial) { ?>

        <div class="curso-material-card">

            <h2 class="curso-subtitle">Material del curso</h2>

            <?php if (count($archivos) == 0) { ?>

                <p class="curso-empty-message">
                    Todavía no hay material publicado.
                </p>

            <?php } else { ?>

                <div class="curso-archivos-list">

                    <?php foreach ($archivos as $archivo) { ?>

                        <div class="curso-archivo-item">

                            <div class="curso-archivo-info">
                                <span class="curso-archivo-name">
                                    <?php echo $archivo->nombre_original; ?>
                                </span>

                                <span class="curso-archivo-size">
                                    <?php echo $archivo->tamanoEnTexto(); ?>
                                </span>
                            </div>

                            <a href="../uploads/cursos/<?php echo $archivo->ruta; ?>"
                            download="<?php echo $archivo->nombre_original; ?>"
                            class="btn-descargar">
                                Descargar
                            </a>

                        </div>

                    <?php } ?>

                </div>

            <?php } ?>

        </div>

            <?php } elseif ($inscripcion != null && $inscripcion->estaConfirmada()) { ?>

                <div class="alert alert-secondary mt-4">
                    El material va a estar disponible cuando empiece el curso (<?php echo $curso->fecha_inicio; ?>).
                </div>

            <?php } ?>

</div>

<?php include "includes/footer.php"; ?>