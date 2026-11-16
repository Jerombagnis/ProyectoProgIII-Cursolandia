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
$todas_las_etiquetas = array();
$intereses_elegidos = array();
$mensaje = "";
$error = "";
$errorFoto= "";
$errorDatos = "";
$errorClave = "";
$errorIntereses = "";

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


    //Formulario 1: guardar foto de perfil
    if (isset($_POST['guardar_foto'])) {
        try {
            // valido la imagen y obtengo la extensión
            $extension = Usuario::validarImagen($_FILES['imagen']);

            // creo un nombre de archivo único con time y el id del usuario para evitar que se sobreescriba la imagen de otro usuario
            $nombreArchivo = $usuario->id . "_" . time() . "." . $extension; // time da la hora actual en segundos 
            $destino = "../Archivos/Perfiles/" . $nombreArchivo;

            if (!copy($_FILES['imagen']['tmp_name'], $destino)) {
                throw new Exception("No se pudo guardar la imagen en el servidor.");
            }

            $manejadorUsuario->cambiarImagen($usuario->id, $nombreArchivo);

            // se elimina la imagen anterior del usuario si no es la default.png
            if ($usuario->imagenPerfil != "default.png") {
                $anterior = "../Archivos/Perfiles/" . $usuario->imagenPerfil;
                if (file_exists($anterior)) {
                    unlink($anterior); // elimino el archivo del servidor 
                }
            }

            $_SESSION['mensaje'] = "Foto de perfil actualizada.";

            include "../Conexiones/cerrar_conexion.php";
            header("Location: perfil.php");
            exit;
        } catch (Exception $e) {
            $errorFoto = $e->getMessage();
        }
    }

    // formulario 2: guardar datos generales del usuario (nombre, apellido, antecedentes)
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

    // Formulario 3: guardar intereses
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

    // Formulario 4: cambiar contraseña
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

        <div class="d-flex align-items-center mb-4">
            <img src="../Archivos/Perfiles/<?php echo $usuario->imagenPerfil; ?>" alt="Foto de perfil"
                 class="rounded-circle border me-3" style="width: 100px; height: 100px; object-fit: cover;">
            <div>
                <p class="mb-1 fw-bold"><?php echo $usuario->nombre . " " . $usuario->apellido; ?></p>
                <?php if ($usuario->esPro()) { ?>
                    <span class="badge bg-warning text-dark">Plan Pro hasta <?php echo $usuario->vencimientoPro; ?></span>
                <?php } else { ?>
                    <span class="badge bg-secondary">Plan gratuito</span>
                <?php } ?>
            </div>
        </div>

        <h2 class="h4">Foto de perfil</h2>

        <?php if ($errorFoto != "") { ?>
            <div class="alert alert-danger"><?php echo $errorFoto; ?></div>
        <?php } ?>

        <form method="post" action="perfil.php" enctype="multipart/form-data" class="mb-5">
            <div class="mb-3">
                <input type="file" name="imagen" class="form-control" accept=".jpg,.jpeg,.png,.gif">
                <div class="form-text">JPG, PNG o GIF. Máximo 2 MB.</div>
            </div>
            <button type="submit" name="guardar_foto" class="btn btn-primary">Cambiar foto</button>
        </form>        

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