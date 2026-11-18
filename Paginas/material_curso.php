<?php
session_start();

// control usuarios logueados
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include "../Conexiones/conexion.php";
include "../Clases/Usuario.php";
include "../Clases/Curso.php";
include "../Clases/Inscripcion.php";
include "../Clases/Archivo.php";
include "../Clases/ControlArchivo.php";
include "../Manejadores/ManejadorUsuario.php";
include "../Manejadores/ManejadorCurso.php";
include "../Manejadores/ManejadorInscripcion.php";
include "../Manejadores/ManejadorArchivo.php";

$curso = null;
$usuario = null;
$archivos = array();
$total_bytes = 0;
$mensaje = "";
$error = "";
$errorAccion = "";

// mensaje de sesion exitosa
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}

try {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception("El curso no existe.");
    }

    $manejadorUsuario = new ManejadorUsuario($conexion);
    $manejadorCurso = new ManejadorCurso($conexion);
    $manejadorInscripcion = new ManejadorInscripcion($conexion);
    $manejadorArchivo = new ManejadorArchivo($conexion);
    $control = new ControlArchivo($manejadorArchivo, $manejadorCurso, $manejadorUsuario, $manejadorInscripcion);

    $curso = $manejadorCurso->buscarPorId($_GET['id']);
    if ($curso == null) {
        throw new Exception("El curso no existe.");
    }

    // control solo creador administra el material 
    if (!$curso->esCreador($_SESSION['usuario_id'])) {
        throw new Exception("No tenés permiso para administrar el material de este curso.");
    }

    $usuario = $manejadorUsuario->buscarPorId($_SESSION['usuario_id']);

    // si apreto subir archivo 
    if (isset($_POST['subir'])) {
        try {
            //valida el archivo 
            $extension = Archivo::validarArchivo($_FILES['archivo']);

            //obtiene nombre original y nombre unico para guardarlo
            $nombreOriginal = str_replace("'", "", $_FILES['archivo']['name']);
            $ruta = md5($curso->id . time() . rand()) . "." . $extension;
            $destino = __DIR__ . "/../Archivos/Cursos/" . $ruta;
            if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $destino)) {
                throw new Exception("No se pudo guardar el archivo en el servidor.");
            }

            // si todo sale bien se guarda el archivo, si no elimina el recien subido
            try {
                $archivo = new Archivo(null, $curso->id, $nombreOriginal, $ruta, $_FILES['archivo']['size'], null);
                $control->subir($archivo, $_SESSION['usuario_id']);
            } catch (Exception $e) {
                unlink($destino);
                throw $e;
            }

            $_SESSION['mensaje'] = "Archivo subido.";

            include "../Conexiones/cerrar_conexion.php";
            header("Location: material_curso.php?id=" . $curso->id);
            exit;
        } catch (Exception $e) {
            $errorAccion = $e->getMessage();
        }
    }

    // eliminacion de material
    if (isset($_POST['eliminar']) && isset($_POST['archivo_id']) && is_numeric($_POST['archivo_id'])) {
        try {
            $ruta = $control->eliminar($_POST['archivo_id'], $_SESSION['usuario_id']);

            $fisico = "../Archivos/Cursos/" . $ruta;
            if (file_exists($fisico)) {
                unlink($fisico);
            }

            $_SESSION['mensaje'] = "Archivo eliminado.";

            include "../Conexiones/cerrar_conexion.php";
            header("Location: material_curso.php?id=" . $curso->id);
            exit;
        } catch (Exception $e) {
            $errorAccion = $e->getMessage();
        }
    }

    $archivos = $manejadorArchivo->listarPorCurso($curso->id);
    foreach ($archivos as $a) {
        $total_bytes = $total_bytes + $a->tamano_bytes;
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
        <a href="index.php" class="btn btn-secondary">Volver al inicio</a>
    <?php } else { ?>

        <h1 class="mb-1">Material del curso</h1>
        <p class="text-muted"><?php echo $curso->titulo; ?></p>

        <?php if ($mensaje != "") { ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php } ?>

        <?php if ($errorAccion != "") { ?>
            <div class="alert alert-danger"><?php echo $errorAccion; ?></div>
        <?php } ?>

        <div class="card mb-4">
            <div class="card-body">
                <?php if ($usuario->esPro()) { ?>
                    <p class="mb-0">
                        <span class="badge bg-warning text-dark">Plan Pro</span>
                        Sin límites. Usás <?php echo round($total_bytes / (1024 * 1024), 1); ?> MB en <?php echo count($archivos); ?> archivo(s).
                    </p>
                <?php } else { ?>
                    <p class="mb-0">
                        <span class="badge bg-secondary">Plan gratuito</span>
                        <?php echo count($archivos); ?> de 5 archivos &middot;
                        <?php echo round($total_bytes / (1024 * 1024), 1); ?> MB de 10 MB
                    </p>
                <?php } ?>
            </div>
        </div>

        <h2 class="h4">Subir un archivo</h2>

        <form method="post" action="material_curso.php?id=<?php echo $curso->id; ?>" enctype="multipart/form-data" class="mb-5">
            <div class="mb-3">
                <input type="file" name="archivo" class="form-control">
                <div class="form-text">PDF, Word, PowerPoint, Excel, TXT, ZIP o imágenes.</div>
            </div>
            <button type="submit" name="subir" class="btn btn-primary">Subir archivo</button>
        </form>

        <h2 class="h4">Archivos del curso</h2>

        <?php if (count($archivos) == 0) { ?>
            <p>Todavía no subiste material.</p>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Tamaño</th>
                            <th>Subido</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($archivos as $archivo) { ?>
                            <tr>
                                <td>
                                    <a href="../Archivos/Cursos/<?php echo $archivo->ruta; ?>" download="<?php echo $archivo->nombre_original; ?>">
                                        <?php echo $archivo->nombre_original; ?>
                                    </a>
                                </td>
                                <td><?php echo $archivo->tamanoEnTexto(); ?></td>
                                <td><?php echo $archivo->created_at; ?></td>
                                <td class="text-end">
                                    <form method="post" action="material_curso.php?id=<?php echo $curso->id; ?>" class="d-inline">
                                        <input type="hidden" name="archivo_id" value="<?php echo $archivo->id; ?>">
                                        <button type="submit" name="eliminar" class="btn btn-outline-danger btn-sm"
                                                onclick="return confirm('¿Eliminar este archivo?');">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>

        <a href="curso.php?id=<?php echo $curso->id; ?>" class="btn btn-secondary">Volver al curso</a>

    <?php } ?>
</div>

<?php include "includes/footer.php"; ?>