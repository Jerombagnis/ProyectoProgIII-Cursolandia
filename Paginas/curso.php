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

        <h1><?php echo $curso->titulo; ?></h1>

        <p>
            <?php if ($curso->esGratuito()) { ?>
                <span class="badge bg-success">Gratuito</span>
            <?php } else { ?>
                <span class="badge bg-warning text-dark">$<?php echo $curso->costo; ?></span>
            <?php } ?>

            <?php if ($curso->esPrivado()) { ?>
                <span class="badge bg-secondary">Privado</span>
            <?php } else { ?>
                <span class="badge bg-primary">Público</span>
            <?php } ?>

            <?php if ($curso->estaActivo() == "proximamente") { ?>
                <span class="badge bg-info">Próximamente</span>
            <?php } else if ($curso->estaActivo() == "activo") { ?>
                <span class="badge bg-success">Activo</span>
            <?php } else { ?>   
                <span class="badge bg-danger">Finalizado</span>
            <?php } ?>
        </p>

        <p><?php echo $curso->descripcion; ?></p>

        <?php if (count($etiquetas) > 0) { ?>
            <p>
                <?php foreach ($etiquetas as $etiqueta) { ?>
                    <span class="badge rounded-pill text-bg-light border"><?php echo $etiqueta['nombre']; ?></span>
                <?php } ?>
            </p>
        <?php } ?>

        <ul class="list-group mb-3">
            <li class="list-group-item"><strong>Inicio:</strong> <?php echo $curso->fecha_inicio; ?></li>
            <li class="list-group-item"><strong>Fin:</strong> <?php echo $curso->fecha_fin; ?></li>
            <li class="list-group-item">
                <strong>Cupo:</strong>
                <?php
                if ($curso->cupo == "") {
                    echo "Sin límite";
                } else {
                    echo $curso->cupo;
                }
                ?>
            </li>
        </ul>

        <?php if (!isset($_SESSION['usuario_id'])) { ?>

            <a href="login.php" class="btn btn-primary">Iniciá sesión para inscribirte</a>

        <?php } elseif ($curso->esCreador($_SESSION['usuario_id'])) { ?>
        
                    <div class="alert alert-info">Este curso es tuyo.</div>
                    <a href="solicitudes.php?id=<?php echo $curso->id; ?>" class="btn btn-outline-primary">Ver solicitudes e inscriptos</a>
                    <a href="material_curso.php?id=<?php echo $curso->id; ?>" class="btn btn-outline-primary">Administrar material</a>

        <?php } elseif ($inscripcion != null) { ?>

            <?php if ($inscripcion->estaConfirmada()) { ?>
                <div class="alert alert-success">Estás inscripto en este curso.</div>
            <?php } elseif ($inscripcion->estaPendiente()) { ?>
                <div class="alert alert-warning">Tu inscripción está pendiente de confirmación.</div>
            <?php } else { ?>
                <div class="alert alert-danger">El creador rechazó tu solicitud.</div>
            <?php } ?>

        <?php } elseif ($curso->estaActivo() == "activo") { ?>

            <form method="post" action="curso.php?id=<?php echo $curso->id; ?>">
                <button type="submit" name="inscribirse" class="btn btn-primary">Inscribirme</button>
            </form>

        <?php } elseif ($curso->estaActivo() == "proximamente") { ?>

            <div class="alert alert-info">Este curso está próximo a activarse.</div>

        <?php } else { ?>

            <div class="alert alert-secondary">Este curso no está activo, no se puede inscribir.</div>

        <?php } ?>

        <?php } ?>

        <?php if ($puedeParticipar) { ?>
            <p class="mt-4">
                <a href="foro.php?id=<?php echo $curso->id; ?>" class="btn btn-outline-primary">Ir al foro del curso</a>
            </p>
        <?php } ?>

        <?php if ($puedeVerMaterial) { ?>

            <h2 class="h4 mt-5">Material del curso</h2>

            <?php if (count($archivos) == 0) { ?>
                        <p>Todavía no hay material publicado.</p>
                    <?php } else { ?>
                        <ul class="list-group">
                            <?php foreach ($archivos as $archivo) { ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="../uploads/cursos/<?php echo $archivo->ruta; ?>" download="<?php echo $archivo->nombre_original; ?>">
                                        <?php echo $archivo->nombre_original; ?>
                                    </a>
                                    <span class="text-muted"><?php echo $archivo->tamanoEnTexto(); ?></span>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } ?>

            <?php } elseif ($inscripcion != null && $inscripcion->estaConfirmada()) { ?>

                <div class="alert alert-secondary mt-4">
                    El material va a estar disponible cuando empiece el curso (<?php echo $curso->fecha_inicio; ?>).
                </div>

            <?php } ?>

</div>

<?php include "includes/footer.php"; ?>