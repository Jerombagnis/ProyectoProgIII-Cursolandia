<?php
session_start();

// control para que no se pueda acceder a esta página si el usuario no está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include "../Conexiones/conexion.php";
include "../Clases/Usuario.php";
include "../Clases/Curso.php";
include "../Manejadores/ManejadorUsuario.php";
include "../Manejadores/ManejadorCurso.php";

$usuario = null;
$nombre = "";
$apellido = "";
$antecedentes = "";
$mensaje = "";
$error = "";
$errorDatos = "";
$errorClave = "";
$errorIntereses = "";
$todas_las_etiquetas = array();
$intereses_elegidos = array();

// si hay un mensaje en la sesión, se guarda en $mensaje y se borra de la sesión
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}

try {
    $manejadorUsuario = new ManejadorUsuario($conexion);
    $manejadorCurso = new ManejadorCurso($conexion);

    
    $usuario = $manejadorUsuario->buscarPorId($_SESSION['usuario_id']);
    if ($usuario == null) {
        throw new Exception("El usuario no existe.");
    }

    // asigno los datos del usuario a variables para poder mostrarlos en el formulario
    $nombre = $usuario->nombre;
    $apellido = $usuario->apellido;
    $antecedentes = $usuario->antecedentes;

    // obtengo todas las etiquetas de la base de datos y los intereses del usuario para mostrarlos en el formulario
    $todas_las_etiquetas = $manejadorCurso->listarEtiquetas();
    $intereses_elegidos = $manejadorUsuario->interesesDeUsuario($usuario->id);

    // formulario 1: guardar datos generales del usuario (nombre, apellido, antecedentes)
    if (isset($_POST['guardar_datos'])) {
        $nombre = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $antecedentes = trim($_POST['antecedentes']);

        try {
            // se arma un usuario con los datos nuevos. email, contraseña, rol, etc. quedan como estaban
            $nuevo = new Usuario($usuario->id, $nombre, $apellido, $usuario->email, $usuario->password, $usuario->rol, $usuario->vencimientoPro, $antecedentes, $usuario->imagenPerfil);
            $nuevo->validar();
            $manejadorUsuario->actualizarDatos($nuevo);

            $_SESSION['usuario_nombre'] = $nuevo->nombre; // actualizo el nombre del usuario en la sesión para que se vea reflejado en el menu y no quede el nombre anterior
            $_SESSION['mensaje'] = "Datos actualizados.";

            include "../Conexiones/cerrar_conexion.php";
            header("Location: perfil.php");
            exit;
        } catch (Exception $e) {
            $errorDatos = $e->getMessage();
        }
    }

    // Formulario 2: guardar intereses
    if (isset($_POST['guardar_intereses'])) {
        // si no marcó ninguna casilla, $_POST['intereses'] no existe
        $intereses_elegidos = array();
        if (isset($_POST['intereses'])) {
            $intereses_elegidos = $_POST['intereses'];
        }

        try {
            $manejadorUsuario->guardarIntereses($usuario->id, $intereses_elegidos);

            $_SESSION['mensaje'] = "Intereses actualizados.";

            include "../Conexiones/cerrar_conexion.php";
            header("Location: perfil.php");
            exit;
        } catch (Exception $e) {
            $errorIntereses = $e->getMessage();
        }
    }

    // Formulario 3: cambiar contraseña
    if (isset($_POST['cambiar_clave'])) {
        try {
            $usuario->validarCambioClave($_POST['clave_actual'], $_POST['clave_nueva'], $_POST['clave_repetida']);
            $manejadorUsuario->cambiarPassword($usuario->id, Usuario::cifrar($_POST['clave_nueva']));

            $_SESSION['mensaje'] = "Contraseña actualizada.";

            include "../Conexiones/cerrar_conexion.php";
            header("Location: perfil.php");
            exit;
        } catch (Exception $e) {
            $errorClave = $e->getMessage();
        }
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

        <h1 class="mb-4">Mi perfil</h1>

        <?php if ($mensaje != "") { ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php } ?>

        <p>
            <?php if ($usuario->esPro()) { ?>
                <span class="badge bg-warning text-dark">Plan Pro hasta <?php echo $usuario->vencimientoPro; ?></span>
            <?php } else { ?>
                <span class="badge bg-secondary">Plan gratuito</span>
            <?php } ?>
        </p>

        <h2 class="h4 mt-4">Mis datos</h2>

        <?php if ($errorDatos != "") { ?>
            <div class="alert alert-danger"><?php echo $errorDatos; ?></div>
        <?php } ?>

        <form method="post" action="perfil.php" class="mb-5">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="text" class="form-control" value="<?php echo $usuario->email; ?>" disabled>
                <div class="form-text">El email no se puede modificar.</div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="<?php echo $nombre; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Apellido</label>
                    <input type="text" name="apellido" class="form-control" value="<?php echo $apellido; ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Antecedentes (opcional)</label>
                <textarea name="antecedentes" class="form-control" rows="4"><?php echo $antecedentes; ?></textarea>
            </div>

            <button type="submit" name="guardar_datos" class="btn btn-primary">Guardar datos</button>
        </form>

        <h2 class="h4">Mis intereses</h2>
        <p class="text-muted">Los usamos para recomendarte cursos.</p>

        <?php if ($errorIntereses != "") { ?>
            <div class="alert alert-danger"><?php echo $errorIntereses; ?></div>
        <?php } ?>

        <form method="post" action="perfil.php" class="mb-5">
            <div class="mb-3">
                <?php foreach ($todas_las_etiquetas as $etiqueta) { ?>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="intereses[]"
                               id="interes<?php echo $etiqueta['id']; ?>"
                               value="<?php echo $etiqueta['id']; ?>"
                               <?php if (in_array($etiqueta['id'], $intereses_elegidos)) { echo "checked"; } ?>>
                        <label class="form-check-label" for="interes<?php echo $etiqueta['id']; ?>">
                            <?php echo $etiqueta['nombre']; ?>
                        </label>
                    </div>
                <?php } ?>
            </div>

            <button type="submit" name="guardar_intereses" class="btn btn-primary">Guardar intereses</button>
        </form>

        <h2 class="h4">Cambiar contraseña</h2>

        <?php if ($errorClave != "") { ?>
            <div class="alert alert-danger"><?php echo $errorClave; ?></div>
        <?php } ?>

        <form method="post" action="perfil.php">
            <div class="mb-3">
                <label class="form-label">Contraseña actual</label>
                <input type="password" name="clave_actual" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Contraseña nueva</label>
                <input type="password" name="clave_nueva" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Repetir contraseña nueva</label>
                <input type="password" name="clave_repetida" class="form-control">
            </div>

            <button type="submit" name="cambiar_clave" class="btn btn-primary">Cambiar contraseña</button>
        </form>

    <?php } ?>
</div>

<?php include "includes/footer.php"; ?>