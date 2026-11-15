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

        <h1 class="mb-1">Solicitudes</h1>
        <p class="text-muted"><?php echo $curso->titulo; ?></p>

        <?php if ($mensaje != "") { ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php } ?>

        <?php if ($errorAccion != "") { ?>
            <div class="alert alert-danger"><?php echo $errorAccion; ?></div>
        <?php } ?>

        <h2 class="h4 mt-4">Pendientes de confirmación</h2>

        <?php if (count($pendientes) == 0) { ?>
            <p>No hay solicitudes pendientes.</p>
        <?php } else { ?>
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Fecha</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendientes as $fila) { ?>
                        <tr>
                            <td><?php echo $fila['nombre']; ?></td>
                            <td><?php echo $fila['email']; ?></td>
                            <td><?php echo $fila['fecha_inscripcion']; ?></td>
                            <td>
                                <form method="post" action="solicitudes.php?id=<?php echo $curso->id; ?>" class="d-inline">
                                    <input type="hidden" name="inscripcion_id" value="<?php echo $fila['id']; ?>">
                                    <button type="submit" name="accion" value="confirmar" class="btn btn-success btn-sm">Confirmar</button>
                                    <button type="submit" name="accion" value="rechazar" class="btn btn-outline-danger btn-sm">Rechazar</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>

        <h2 class="h4 mt-5">Inscriptos confirmados</h2>

        <?php if (count($confirmadas) == 0) { ?>
            <p>Todavía no hay inscriptos confirmados.</p>
        <?php } else { ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($confirmadas as $fila) { ?>
                        <tr>
                            <td><?php echo $fila['nombre']; ?></td>
                            <td><?php echo $fila['email']; ?></td>
                            <td><?php echo $fila['fecha_inscripcion']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>

        <a href="curso.php?id=<?php echo $curso->id; ?>" class="btn btn-secondary">Volver al curso</a>

    <?php } ?>
</div>

<?php include "includes/footer.php"; ?>