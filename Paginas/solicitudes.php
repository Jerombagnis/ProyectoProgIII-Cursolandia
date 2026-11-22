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
include "../Clases/ControlInscripcion.php";
include "../Manejadores/ManejadorUsuario.php";
include "../Manejadores/ManejadorCurso.php";
include "../Manejadores/ManejadorInscripcion.php";

$curso = null;
$pendientes = array();
$confirmadas = array();
$mensaje = "";
$error = "";
$errorAccion = "";

// Si hay un mensaje en la sesión, lo guardo en una variable mensaje y lo elimino de la sesión
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}

try {
    // id lo obtiene de la url, por eso se valida que exista y que sea un número
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception("El curso no existe.");
    }

    $manejadorUsuario = new ManejadorUsuario($conexion);
    $manejadorCurso = new ManejadorCurso($conexion);
    $manejadorInscripcion = new ManejadorInscripcion($conexion);

    $curso = $manejadorCurso->buscarPorId($_GET['id']);
    if ($curso == null) {
        throw new Exception("El curso no existe.");
    }

    // controlo que el usuario logueado sea el creador del curso, sino no puede ver las solicitudes
    if (!$curso->esCreador($_SESSION['usuario_id'])) {
        throw new Exception("No tenés permiso para ver las solicitudes de este curso.");
    }

    // controlo si se envió un formulario para confirmar o rechazar una solicitud
    if (isset($_POST['accion']) && isset($_POST['inscripcion_id']) && is_numeric($_POST['inscripcion_id'])) {
        $control = new ControlInscripcion($manejadorInscripcion, $manejadorCurso, $manejadorUsuario);

        try {
            if ($_POST['accion'] == "confirmar") {
                $control->confirmar($_POST['inscripcion_id'], $_SESSION['usuario_id']);
                $_SESSION['mensaje'] = "Solicitud confirmada.";
            } elseif ($_POST['accion'] == "rechazar") {
                $control->rechazar($_POST['inscripcion_id'], $_SESSION['usuario_id']);
                $_SESSION['mensaje'] = "Solicitud rechazada.";
            }

            include "../Conexiones/cerrar_conexion.php";
            header("Location: solicitudes.php?id=" . $curso->id);
            exit;
        } catch (Exception $e) {
            $errorAccion = $e->getMessage();
        }
    }

    $pendientes = $manejadorInscripcion->listarPorCurso($curso->id, "pendiente");
    $confirmadas = $manejadorInscripcion->listarPorCurso($curso->id, "confirmada");
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

        <div class="solicitudes-header">

            <div>
                <span class="section-kicker">GESTIÓN DEL CURSO</span>
                <h1 class="solicitudes-title">Solicitudes e inscriptos</h1>
                <p class="section-description">
                    Administrá las solicitudes de inscripción de:
                    <strong><?php echo $curso->titulo; ?></strong>
                </p>
            </div>

            <a href="curso.php?id=<?php echo $curso->id; ?>" class="btn-curso-secondary">
                Volver al curso
            </a>

        </div>    

        <?php if ($mensaje != "") { ?>
            <div class="solicitudes-alert solicitudes-alert-success">
                <?php echo $mensaje; ?>
            </div>
        <?php } ?>

        <?php if ($errorAccion != "") { ?>
            <div class="solicitudes-alert solicitudes-alert-error">
                <?php echo $errorAccion; ?>
            </div>
        <?php } ?>

        <section class="solicitudes-section">

            <div class="solicitudes-section-header">
                <div>
                    <span class="section-kicker">REVISIÓN</span>
                    <h2 class="solicitudes-section-title">Pendientes de confirmación</h2>
                </div>

                <span class="solicitudes-count">
                    <?php echo count($pendientes); ?>
                </span>
            </div>

            <?php if (count($pendientes) == 0) { ?>

                <div class="solicitudes-empty">
                    No hay solicitudes pendientes por el momento.
                </div>

            <?php } else { ?>

                <div class="solicitudes-grid">

                    <?php foreach ($pendientes as $fila) { ?>

                        <article class="solicitud-card">

                            <div class="solicitud-card-info">
                                <h3><?php echo $fila['nombre']; ?></h3>
                                <p><?php echo $fila['email']; ?></p>

                                <span class="solicitud-fecha">
                                    Inscripción: <?php echo $fila['fecha_inscripcion']; ?>
                                </span>
                            </div>

                            <div class="solicitud-card-actions">

                                <form method="post" action="solicitudes.php?id=<?php echo $curso->id; ?>">

                                    <input type="hidden" name="inscripcion_id" value="<?php echo $fila['id']; ?>">

                                    <button type="submit" name="accion" value="confirmar" class="btn-confirmar">
                                        Confirmar
                                    </button>

                                    <button type="submit" name="accion" value="rechazar" class="btn-rechazar">
                                        Rechazar
                                    </button>

                                </form>

                            </div>

                        </article>

                    <?php } ?>

                </div>

            <?php } ?>

        </section>

        <section class="solicitudes-section">

            <div class="solicitudes-section-header">
                <div>
                    <span class="section-kicker">ALUMNOS</span>
                    <h2 class="solicitudes-section-title">Inscriptos confirmados</h2>
                </div>

                <span class="solicitudes-count solicitudes-count-confirmadas">
                    <?php echo count($confirmadas); ?>
                </span>
            </div>

            <?php if (count($confirmadas) == 0) { ?>

                <div class="solicitudes-empty">
                    Todavía no hay alumnos inscriptos en este curso.
                </div>

            <?php } else { ?>

                <div class="table-responsive solicitudes-table-container">

                    <table class="table solicitudes-table align-middle">

                        <thead>
                            <tr>
                                <th>Alumno</th>
                                <th>Correo electrónico</th>
                                <th>Fecha de inscripción</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($confirmadas as $fila) { ?>

                                <tr>
                                    <td class="solicitudes-alumno">
                                        <?php echo $fila['nombre']; ?>
                                    </td>

                                    <td><?php echo $fila['email']; ?></td>

                                    <td><?php echo $fila['fecha_inscripcion']; ?></td>
                                </tr>

                            <?php } ?>

                        </tbody>

                    </table>

                </div>

            <?php } ?>

        </section>

    <?php } ?>
</div>

<?php include "includes/footer.php"; ?>