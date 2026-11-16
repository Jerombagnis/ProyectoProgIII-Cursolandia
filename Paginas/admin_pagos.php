
<?php

session_start();

// control para usuarios logueados, si no estas logueado redirige al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include "../Conexiones/conexion.php";
include "../Clases/Usuario.php";
include "../Manejadores/ManejadorUsuario.php";
include "../Clases/PagoPro.php";
include "../Manejadores/ManejadorPagoPro.php";
include "../Clases/ControlPagoPro.php";

$titulo = "Panel de administración";
$error = "";
$pagos = array();
$usuarios = array();
$mensaje = "";
$errorAccion = "";

// si existe un mensaje en la sesion, lo guarda en $mensaje y lo limpia de sesion
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}

try {
    $manejadorUsuario = new ManejadorUsuario($conexion);

    // control de usuario administrador, si no es admin no puede acceder
    $usuario = $manejadorUsuario->buscarPorId($_SESSION['usuario_id']);
    if ($usuario == null || !$usuario->esAdmin()) {
        throw new Exception("No tenés permiso para ver esta página.");
    }

    $manejadorPagoPro = new ManejadorPagoPro($conexion);
    $pagos = $manejadorPagoPro->listarPendientes();
    $control = new ControlPagoPro($manejadorPagoPro);

    // control si el formulario se envio a traves del boton "rechazar"
    if (isset($_POST['rechazar'])) {
        try {
            $control->rechazar($_POST['pago_id']);

            $_SESSION['mensaje'] = "La solicitud fue rechazada.";

            include "../Conexiones/cerrar_conexion.php";
            header("Location: admin_pagos.php");
            exit;
        } catch (Exception $e) {
            $errorAccion = $e->getMessage();
        }
    }

    // control si el formulario se envio a traves del boton "aprobar"
    if (isset($_POST['aprobar'])) {
        try {
            $control->aprobar($_POST['pago_id'], $_POST['meses']);

            $_SESSION['mensaje'] = "La solicitud fue aprobada y el plan Pro quedó extendido.";

            include "../Conexiones/cerrar_conexion.php";
            header("Location: admin_pagos.php");
            exit;
        } catch (Exception $e) {
            $errorAccion = $e->getMessage();
        }
    }

    // guardo el usuario de cada solicitud pendiente, usando como indice el id del usuario
    foreach ($pagos as $pago) {
        $usuarios[$pago->usuario_id] = $manejadorUsuario->buscarPorId($pago->usuario_id);
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

    <?php } else { ?>

        <h1 class="mb-1">Solicitudes Pro</h1>
        <p class="text-muted">Administración de solicitudes de suscripción al plan Pro.</p>

        <?php if ($mensaje != "") { ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php } ?>

        <?php if ($errorAccion != "") { ?>
            <div class="alert alert-danger"><?php echo $errorAccion; ?></div>
        <?php } ?>

        <?php if (count($pagos) == 0) { ?>

            <p>No hay solicitudes pendientes.</p>

        <?php } else { ?>

            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Plan actual</th>
                            <th>Mensaje</th>
                            <th>Comprobante</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($pagos as $pago) { ?>
                            <?php $solicitante = $usuarios[$pago->usuario_id]; ?>

                            <tr>

                                <td><?php echo $pago->created_at; ?></td>

                                <td>
                                    <?php echo htmlspecialchars($solicitante->nombre . " " . $solicitante->apellido); ?>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($solicitante->email); ?>
                                    </small>
                                </td>

                                <td>
                                    <?php if ($solicitante->vencimientoPro == null) { ?>

                                        <span class="badge bg-secondary">Plan gratuito</span>

                                    <?php } elseif ($solicitante->esPro()) { ?>

                                        <span class="badge bg-warning text-dark">
                                            Pro hasta <?php echo $solicitante->vencimientoPro; ?>
                                        </span>

                                    <?php } else { ?>

                                        <span class="badge bg-secondary">
                                            Pro vencido el <?php echo $solicitante->vencimientoPro; ?>
                                        </span>

                                    <?php } ?>
                                </td>

                                <td>
                                    <?php if (trim($pago->mensaje) == "") { ?>

                                        <span class="text-muted">Sin mensaje</span>

                                    <?php } else { ?>

                                        <?php echo htmlspecialchars($pago->mensaje); ?>

                                    <?php } ?>
                                </td>

                                <td>
                                    <a href="../Archivos/Comprobantes/<?php echo $pago->adjunto; ?>"
                                       target="_blank"
                                       class="btn btn-outline-primary btn-sm">
                                        Ver comprobante
                                    </a>
                                </td>

                                <td>

                                    <form method="post" action="admin_pagos.php" class="d-flex flex-wrap gap-1 mb-2">

                                        <input type="hidden" name="pago_id" value="<?php echo $pago->id; ?>">

                                        <select name="meses" class="form-select form-select-sm w-auto">
                                            <option value="1">1 mes</option>
                                            <option value="3">3 meses</option>
                                            <option value="12">12 meses</option>
                                        </select>

                                        <button type="submit" name="aprobar" class="btn btn-success btn-sm">
                                            Aprobar
                                        </button>

                                    </form>

                                    <form method="post" action="admin_pagos.php">

                                        <input type="hidden" name="pago_id" value="<?php echo $pago->id; ?>">

                                        <button type="submit" name="rechazar" class="btn btn-outline-danger btn-sm">
                                            Rechazar
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>

        <?php } ?>

    <?php } ?>

</div>

<?php include "includes/footer.php"; ?>
