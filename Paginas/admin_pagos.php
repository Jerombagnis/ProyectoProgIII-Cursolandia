
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

        <div class="admin-pro-alert admin-pro-alert-error"><?php echo $error; ?></div>

    <?php } else { ?>

        <div class="admin-pro-header">
            <div>
                <span class="section-kicker">ADMINISTRACIÓN</span>
                <h1 class="admin-pro-title">Solicitudes Pro</h1>
                <p class="section-description">
                    Revisá los comprobantes y gestioná las solicitudes de suscripción al plan Pro.
                </p>
            </div>
        </div>

        <?php if ($mensaje != "") { ?>

            <div class="admin-pro-alert admin-pro-alert-success"><?php echo $mensaje; ?></div>
        
        <?php } ?>

        <?php if ($errorAccion != "") { ?>

          <div class="admin-pro-alert admin-pro-alert-error"><?php echo $errorAccion; ?></div>

        <?php } ?>

        <?php if (count($pagos) == 0) { ?>

            <div class="admin-pro-empty">
                <div class="admin-pro-empty-icon">✓</div>
                <h2>No hay solicitudes pendientes</h2>
                <p>Cuando un usuario solicite el Plan Pro, su solicitud aparecerá en esta sección.</p>
            </div>

        <?php } else { ?>

            <div class="admin-pro-solicitudes">
                <?php foreach ($pagos as $pago) { ?>
                    <?php $solicitante = $usuarios[$pago->usuario_id]; ?>

                    <div class="admin-pro-card">

                        <div class="admin-pro-card-header">
                            <div>
                                <span class="admin-pro-card-label">SOLICITUD RECIBIDA</span>
                                <p class="admin-pro-fecha"><?php echo $pago->created_at; ?></p>
                            </div>

                            <span class="admin-pro-pendiente">Pendiente de revisión</span>
                        </div>

                        <div class="admin-pro-card-body">

                            <div class="admin-pro-dato">
                                <span class="admin-pro-card-label">USUARIO</span>
                                <h3><?php echo htmlspecialchars($solicitante->nombre . " " . $solicitante->apellido); ?></h3>
                                <p><?php echo htmlspecialchars($solicitante->email); ?></p>
                            </div>

                            <div class="admin-pro-dato">
                                <span class="admin-pro-card-label">PLAN ACTUAL</span>

                                <?php if ($solicitante->vencimientoPro == null) { ?>

                                    <span class="admin-pro-plan admin-pro-plan-gratuito">Plan gratuito</span>

                                <?php } elseif ($solicitante->esPro()) { ?>

                                    <span class="admin-pro-plan admin-pro-plan-activo">
                                        Pro hasta <?php echo $solicitante->vencimientoPro; ?>
                                    </span>

                                <?php } else { ?>

                                    <span class="admin-pro-plan admin-pro-plan-vencido">
                                        Pro vencido el <?php echo $solicitante->vencimientoPro; ?>
                                    </span>

                                <?php } ?>
                            </div>

                            <div class="admin-pro-dato admin-pro-mensaje">
                                <span class="admin-pro-card-label">MENSAJE DEL USUARIO</span>

                                <?php if (trim($pago->mensaje) == "") { ?>
                                    <p class="admin-pro-sin-mensaje">Sin mensaje adicional.</p>
                                <?php } else { ?>
                                    <p><?php echo htmlspecialchars($pago->mensaje); ?></p>
                                <?php } ?>
                            </div>

                        </div>

                        <div class="admin-pro-card-footer">

                            <a href="../Archivos/Comprobantes/<?php echo $pago->adjunto; ?>"
                            target="_blank"
                            class="admin-pro-comprobante">
                                Ver comprobante
                            </a>

                            <div class="admin-pro-acciones">

                                <form method="post" action="admin_pagos.php" class="admin-pro-form-aprobar">
                                    <input type="hidden" name="pago_id" value="<?php echo $pago->id; ?>">

                                    <select name="meses" class="admin-pro-select">
                                        <option value="1">1 mes</option>
                                        <option value="3">3 meses</option>
                                        <option value="12">12 meses</option>
                                    </select>

                                    <button type="submit" name="aprobar" class="admin-pro-btn-aprobar">
                                        Aprobar
                                    </button>
                                </form>

                                <form method="post" action="admin_pagos.php">
                                    <input type="hidden" name="pago_id" value="<?php echo $pago->id; ?>">

                                    <button type="submit" name="rechazar" class="admin-pro-btn-rechazar">
                                        Rechazar
                                    </button>
                                </form>

                            </div>

                        </div>

                    </div>
                <?php } ?>
            </div>

        <?php } ?>

    <?php } ?>

</div>

<?php include "includes/footer.php"; ?>
