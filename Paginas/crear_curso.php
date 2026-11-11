<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include "../Conexiones/conexion.php";
include "../Clases/Usuario.php";
include "../Clases/Curso.php";
include "../Clases/ControlCurso.php";
include "../Manejadores/ManejadorUsuario.php";
include "../Manejadores/ManejadorCurso.php";

$titulo = "";
$descripcion = "";
$costo = "0";
$cupo = "";
$fecha_inicio = "";
$fecha_fin = "";
$tipo_acceso = "publico";
$error = "";

if (isset($_POST['enviar'])) {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $costo = trim($_POST['costo']);
    $cupo = trim($_POST['cupo']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $tipo_acceso = $_POST['tipo_acceso'];

    $manejadorUsuario = new ManejadorUsuario($conexion);
    $manejadorCurso = new ManejadorCurso($conexion);
    $control = new ControlCurso($manejadorCurso, $manejadorUsuario);

    try {
        $curso = new Curso(null, $_SESSION['usuario_id'], $titulo, $descripcion, $costo, $cupo, $fecha_inicio, $fecha_fin, $tipo_acceso);
        $id = $control->crear($curso);

        include "../Conexiones/cerrar_conexion.php";
        header("Location: curso.php?id=" . $id);
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

include "../Conexiones/cerrar_conexion.php";
?>
<?php include "includes/header.php"; ?>

<div class="container my-4">
    <h1 class="mb-4">Crear curso</h1>

    <?php if ($error != "") { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php } ?>

    <form method="POST" action="crear_curso.php">
        <div class="mb-3">
            <label class="form-label">Título</label>
            <input type="text" name="titulo" class="form-control" value="<?php echo $titulo; ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="4"><?php echo $descripcion; ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Costo (0 = gratuito)</label>
                <input type="number" name="costo" class="form-control" min="0" step="0.01" value="<?php echo $costo; ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Cupo (vacío = sin límite)</label>
                <input type="number" name="cupo" class="form-control" min="1" value="<?php echo $cupo; ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Fecha de inicio</label>
                <input type="date" name="fecha_inicio" class="form-control" value="<?php echo $fecha_inicio; ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Fecha de fin</label>
                <input type="date" name="fecha_fin" class="form-control" value="<?php echo $fecha_fin; ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Tipo de acceso</label>
            <select name="tipo_acceso" class="form-select">
                <option value="publico" <?php if ($tipo_acceso == "publico") { echo "selected"; } ?>>Público</option>
                <option value="privado" <?php if ($tipo_acceso == "privado") { echo "selected"; } ?>>Privado (solo con link de acceso)</option>
            </select>
        </div>

        <button type="submit" name="enviar" class="btn btn-primary">Crear curso</button>
    </form>
</div>

<?php include "includes/footer.php"; ?>