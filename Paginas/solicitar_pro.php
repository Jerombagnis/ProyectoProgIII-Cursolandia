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
        <div class="pro-alert pro-alert-error"><?php echo $error; ?></div>
    <?php } else { ?>

        <div class="pro-page-header">
            <div>
                <span class="section-kicker">MEJORÁ TU EXPERIENCIA</span>
                <h1 class="pro-page-title">Plan Pro</h1>
                <p class="section-description">
                    Desbloqueá todas las posibilidades de Cursolandia y llevá tus cursos al siguiente nivel.
                </p>
            </div>
        </div>

        <?php if ($mensaje != "") { ?>
            <div class="pro-alert pro-alert-success"><?php echo $mensaje; ?></div>
        <?php } ?>

        <div class="pro-estado-card">
            <?php if ($usuario->esPro()) { ?>
                <span class="pro-estado-badge pro-estado-activo">Plan Pro activo</span>
                <p>Tu plan está vigente hasta el <?php echo $usuario->vencimientoPro; ?>.</p>
            <?php } else { ?>
                <span class="pro-estado-badge pro-estado-gratuito">Plan gratuito</span>
                <p>Actualmente estás utilizando el plan gratuito de Cursolandia.</p>
            <?php } ?>
        </div>

        <div class="pro-beneficios">
            <div class="pro-beneficio">
                <span class="pro-beneficio-icono">∞</span>
                <div>
                    <h3>Cursos ilimitados</h3>
                    <p>Creá todos los cursos que quieras, sin restricciones.</p>
                </div>
            </div>

            <div class="pro-beneficio">
                <span class="pro-beneficio-icono">✓</span>
                <div>
                    <h3>Inscripciones ilimitadas</h3>
                    <p>Recibí todas las solicitudes de inscripción que necesites.</p>
                </div>
            </div>

            <div class="pro-beneficio">
                <span class="pro-beneficio-icono">↗</span>
                <div>
                    <h3>Más archivos por curso</h3>
                    <p>Compartí más material y recursos con tus estudiantes.</p>
                </div>
            </div>

            <div class="pro-beneficio">
                <span class="pro-beneficio-icono">★</span>
                <div>
                    <h3>Cursos destacados</h3>
                    <p>Tus cursos tendrán mayor visibilidad en la portada.</p>
                </div>
            </div>
        </div>

        <div class="pro-pago-card">
            <div class="pro-pago-header">
                <span class="section-kicker">ACTIVACIÓN DEL PLAN</span>
                <h2>Cómo pagar</h2>
                <p>Seguí estos tres pasos para solicitar tu suscripción Pro.</p>
            </div>

            <ol class="pro-pasos-pago">
                <li>
                    <span class="pro-paso-numero">1</span>
                    <div>
                        <h3>Realizá la transferencia</h3>
                        <p>Transferí el importe correspondiente al alias:</p>
                        <strong class="pro-alias">cursolandia.mp</strong>
                    </div>
                </li>
                <li>
                    <span class="pro-paso-numero">2</span>
                    <div>
                        <h3>Guardá el comprobante</h3>
                        <p>Conservá el comprobante de la operación para adjuntarlo en el formulario.</p>
                    </div>
                </li>
                <li>
                    <span class="pro-paso-numero">3</span>
                    <div>
                        <h3>Enviá tu solicitud</h3>
                        <p>Adjuntá el comprobante. El administrador revisará el pago y activará tu plan.</p>
                    </div>
                </li>
            </ol>
        </div>

        <?php if ($errorSolicitud != "") { ?>
            <div class="pro-alert pro-alert-error"><?php echo $errorSolicitud; ?></div>
        <?php } ?>

        <div class="pro-form-card">
            <div class="pro-form-header">
                <span class="section-kicker">FORMULARIO</span>
                <h2>Solicitar Plan Pro</h2>
                <p>Adjuntá tu comprobante para que podamos verificar el pago.</p>
            </div>

            <form method="post" action="solicitar_pro.php" enctype="multipart/form-data" class="pro-form">
                <div class="pro-form-group">
                    <label class="pro-form-label">Mensaje <span>(opcional)</span></label>
                    <textarea name="mensaje" class="form-control" rows="3"><?php echo $mensaje_usuario; ?></textarea>
                </div>

                <div class="pro-form-group">
                    <label class="pro-form-label">Comprobante de pago</label>
                    <input type="file" name="comprobante" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    <p class="pro-form-help">Formatos permitidos: JPG, PNG o PDF. Tamaño máximo: 5 MB.</p>
                </div>

                <button type="submit" name="solicitar" class="btn-cursolandia">Enviar solicitud</button>
            </form>
        </div>

        <div class="pro-solicitudes-section">
            <div class="pro-solicitudes-header">
                <div>
                    <span class="section-kicker">HISTORIAL</span>
                    <h2>Mis solicitudes</h2>
                    <p>Consultá el estado de tus solicitudes de Plan Pro.</p>
                </div>
                <span class="pro-solicitudes-count"><?php echo count($pagos); ?></span>
            </div>

            <?php if (count($pagos) == 0) { ?>
                <div class="pro-solicitudes-vacio">
                    <h3>Todavía no enviaste ninguna solicitud</h3>
                    <p>Cuando envíes una solicitud de Plan Pro, vas a poder consultar su estado desde acá.</p>
                </div>
            <?php } else { ?>
                <div class="pro-solicitudes-lista">
                    <?php foreach ($pagos as $pago) { ?>
                        <div class="pro-solicitud-item">
                            <div>
                                <span class="pro-solicitud-label">Fecha de solicitud</span>
                                <p><?php echo $pago->created_at; ?></p>
                            </div>

                            <div>
                                <span class="pro-solicitud-label">Estado</span>
                                <?php if ($pago->estaPendiente()) { ?>
                                    <span class="pro-solicitud-estado pro-solicitud-pendiente">Pendiente</span>
                                <?php } elseif ($pago->estaAprobado()) { ?>
                                    <span class="pro-solicitud-estado pro-solicitud-aprobada">Aprobada</span>
                                <?php } else { ?>
                                    <span class="pro-solicitud-estado pro-solicitud-rechazada">Rechazada</span>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>

    <?php } ?>
</div>

<?php include "includes/footer.php"; ?>