<?php

session_start();

// control para usuarios logueados, si no estas logueado redirige al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include "../Conexiones/conexion.php";
include "../Clases/Usuario.php";
include "../Clases/PagoPro.php";
include "../Clases/ControlPagoPro.php";
include "../Manejadores/ManejadorUsuario.php";
include "../Manejadores/ManejadorPagoPro.php";

$usuario = null;
$pagos = array();
$mensaje_usuario = "";
$mensaje = "";
$error = "";
$errorSolicitud = "";

// si existe un mensaje en la sesion, la guarda en $mensaje y lo limpia de sesion
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}

try {
    $manejadorUsuario = new ManejadorUsuario($conexion);
    $manejadorPagoPro = new ManejadorPagoPro($conexion);
    $control = new ControlPagoPro($manejadorPagoPro);

    $usuario = $manejadorUsuario->buscarPorId($_SESSION['usuario_id']);
    if ($usuario == null) {
        throw new Exception("El usuario no existe.");
    }

    // control si el formulario se envio a traves del boton "solicitar"
    if (isset($_POST['solicitar'])) {
        $mensaje_usuario = trim($_POST['mensaje']);

        try {
            // controlo que el archivo sea valido y devuelvo la extension
            $extension = PagoPro::validarComprobante($_FILES['comprobante']);

            // nombre aleatorio para el archivo
            $nombreArchivo = md5($usuario->id . time() . rand()) . "." . $extension;
            $destino = "../Archivos/Comprobantes/" . $nombreArchivo;

            if (!copy($_FILES['comprobante']['tmp_name'], $destino)) {
                throw new Exception("No se pudo guardar el comprobante en el servidor.");
            }

            //registro la solicitud, si hay un error entonces se borra el archivo recien copiado
            try {
                $pago = new PagoPro(null, $usuario->id, $mensaje_usuario, $nombreArchivo, "pendiente", null);
                $control->solicitar($pago);
            } catch (Exception $e) {
                unlink($destino);
                throw $e;
            }

            $_SESSION['mensaje'] = "Tu solicitud fue enviada. El administrador la va a revisar.";

            include "../Conexiones/cerrar_conexion.php";
            header("Location: solicitar_pro.php");
            exit;
        } catch (Exception $e) {
            $errorSolicitud = $e->getMessage();
        }
    }

    $pagos = $manejadorPagoPro->listarPorUsuario($usuario->id);
} catch (Exception $e) {
    $error = $e->getMessage();
}

include "../Conexiones/cerrar_conexion.php";
?>
<?php include "includes/header.php"; ?>

<div class="container my-4">

    <?php if ($error != "") { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php } else { ?>

        <h1 class="mb-3">Plan Pro</h1>

        <?php if ($mensaje != "") { ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php } ?>

        <p>
            <!-- si es pro se muestra hasta cuando es pro, sino muestra plan gratuito -->
            <?php if ($usuario->esPro()) { ?>
                <span class="badge bg-warning text-dark">Plan Pro hasta <?php echo $usuario->vencimientoPro; ?></span>
            <?php } else { ?>
                <span class="badge bg-secondary">Plan gratuito</span>
            <?php } ?>
        </p>

        <p>
            Con el plan Pro no tenés límite de cursos ni de inscripciones, ni de archivos por curso.
            Además, tus cursos aparecen como destacados en la portada.
        </p>

        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h5">Cómo pagar</h2>
                <ol class="mb-0">
                    <li>Hacé la transferencia al alias <strong>cursolandia.mp</strong>.</li>
                    <li>Guardá el comprobante del pago.</li>
                    <li>Completá este formulario y adjuntalo. El administrador lo revisa y activa tu plan.</li>
                </ol>
            </div>
        </div>

        <?php if ($errorSolicitud != "") { ?>
            <div class="alert alert-danger"><?php echo $errorSolicitud; ?></div>
        <?php } ?>

        <form method="post" action="solicitar_pro.php" enctype="multipart/form-data" class="mb-5">
            <div class="mb-3">
                <label class="form-label">Mensaje (opcional)</label>
                <textarea name="mensaje" class="form-control" rows="3"><?php echo $mensaje_usuario; ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Comprobante de pago</label>
                <input type="file" name="comprobante" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                <div class="form-text">JPG, PNG o PDF. Máximo 5 MB.</div>
            </div>

            <button type="submit" name="solicitar" class="btn btn-primary">Enviar solicitud</button>
        </form>

        <h2 class="h4">Mis solicitudes</h2>

        <?php if (count($pagos) == 0) { ?>
            <p>Todavía no enviaste ninguna solicitud.</p>
        <?php } else { ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pagos as $pago) { ?>
                        <tr>
                            <td><?php echo $pago->created_at; ?></td>
                            <td>
                                <?php if ($pago->estaPendiente()) { ?>
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                <?php } elseif ($pago->estaAprobado()) { ?>
                                    <span class="badge bg-success">Aprobada</span>
                                <?php } else { ?>
                                    <span class="badge bg-danger">Rechazada</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>

    <?php } ?>
</div>

<?php include "includes/footer.php"; ?>