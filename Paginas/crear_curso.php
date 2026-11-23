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

$manejadorUsuario = new ManejadorUsuario($conexion);
$manejadorCurso = new ManejadorCurso($conexion);
$control = new ControlCurso($manejadorCurso, $manejadorUsuario);

$titulo = "";
$descripcion = "";
$costo = "0";
$cupo = "";
$fecha_inicio = "";
$fecha_fin = "";
$tipo_acceso = "publico";
$etiquetas_elegidas = array (); 
$todas_las_etiquetas = array (); 
$error = "";

try {

    $todas_las_etiquetas = $manejadorCurso->listarEtiquetas();

    if (isset($_POST['enviar'])) {
        $titulo = trim($_POST['titulo']);
        $descripcion = trim($_POST['descripcion']);
        $costo = trim($_POST['costo']);
        $cupo = trim($_POST['cupo']);
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];
        $tipo_acceso = $_POST['tipo_acceso'];

        if(isset($_POST['etiquetas'])) {
            $etiquetas_elegidas = $_POST['etiquetas'];
        }
            
        $curso = new Curso(null, $_SESSION['usuario_id'], $titulo, $descripcion, $costo, $cupo, $fecha_inicio, $fecha_fin, $tipo_acceso);
        $id = $control->crear($curso, $etiquetas_elegidas);

        include "../Conexiones/cerrar_conexion.php";
        header("Location: curso.php?id=" . $id);
        exit;
    }      
} catch (Exception $e) {
    $error = $e->getMessage();
}


include "../Conexiones/cerrar_conexion.php";
?>
<?php include "includes/header.php"; ?>

<div class="container my-4">

    <div class="crear-curso-header">
        <div>
            <span class="section-kicker">ENSEÑANZA Y APRENDIZAJE</span>
            <h1 class="crear-curso-title">Crear un curso</h1>
            <p class="section-description">
                Compartí tus conocimientos y creá una nueva experiencia de aprendizaje en Cursolandia.
            </p>
        </div>
    </div>

    <?php if ($error != "") { ?>
        <div class="crear-curso-alert">
            <?php echo $error; ?>
        </div>
    <?php } ?>

    <div class="crear-curso-card">
        <form method="POST" action="crear_curso.php" class="crear-curso-form">
            <div class="crear-curso-seccion">
                <div class="crear-curso-seccion-header">
                    <span class="section-kicker">01 · INFORMACIÓN GENERAL</span>
                    <h2>Presentá tu curso</h2>
                    <p>Contales a los estudiantes qué van a aprender.</p>
                </div>

                <div class="crear-curso-campo">
                    <label class="crear-curso-label">Título del curso</label>
                    <input type="text" name="titulo" class="form-control" value="<?php echo $titulo; ?>">
                </div>

                <div class="crear-curso-campo">
                    <label class="crear-curso-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="4"><?php echo $descripcion; ?></textarea>
                </div>
            </div>

            <div class="crear-curso-seccion">
                <div class="crear-curso-seccion-header">
                    <span class="section-kicker">02 · CONFIGURACIÓN</span>
                    <h2>Detalles del curso</h2>
                    <p>Definí las condiciones de inscripción y duración.</p>
                </div>

                <div class="crear-curso-campos-grid">
                    <div class="crear-curso-campo">
                        <label class="crear-curso-label">Costo <span>(opcional)</span></label>
                        <input type="number" name="costo" class="form-control" min="0" step="0.01" value="<?php echo $costo; ?>">
                    </div>

                    <div class="crear-curso-campo">
                        <label class="crear-curso-label">Cupo <span>(opcional)</span></label>
                        <input type="number" name="cupo" class="form-control" min="1" value="<?php echo $cupo; ?>">
                    </div>

                    <div class="crear-curso-campo">
                        <label class="crear-curso-label">Fecha de inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="<?php echo $fecha_inicio; ?>">
                    </div>

                    <div class="crear-curso-campo">
                        <label class="crear-curso-label">Fecha de fin</label>
                        <input type="date" name="fecha_fin" class="form-control" value="<?php echo $fecha_fin; ?>">
                    </div>
                </div>
            </div>

            <div class="crear-curso-seccion">
                <div class="crear-curso-seccion-header">
                    <span class="section-kicker">03 · ACCESO</span>
                    <h2>Definí cómo accederán los estudiantes</h2>
                    <p>Elegí si el curso estará disponible públicamente o mediante un enlace privado.</p>
                </div>

                <div class="crear-curso-campo">
                    <label class="crear-curso-label">Tipo de acceso</label>
                    <select name="tipo_acceso" class="form-select">
                        <option value="publico" <?php if ($tipo_acceso == "publico") { echo "selected"; } ?>>Público</option>
                        <option value="privado" <?php if ($tipo_acceso == "privado") { echo "selected"; } ?>>Privado (solo con link de acceso)</option>
                    </select>
                </div>
            </div>

            <div class="crear-curso-seccion">
                <div class="crear-curso-seccion-header">
                    <span class="section-kicker">04 · CATEGORIZACIÓN</span>
                    <h2>Etiquetas del curso</h2>
                    <p>Seleccioná las categorías que mejor describan el contenido de tu curso.</p>
                </div>

                <div class="crear-curso-etiquetas">
                    <?php foreach ($todas_las_etiquetas as $etiqueta) { ?>
                        <div class="crear-curso-etiqueta">
                            <input
                                type="checkbox"
                                name="etiquetas[]"
                                id="etiqueta<?php echo $etiqueta['id']; ?>"
                                value="<?php echo $etiqueta['id']; ?>"
                                <?php if (in_array($etiqueta['id'], $etiquetas_elegidas)) { echo "checked"; } ?>
                            >
                            <label for="etiqueta<?php echo $etiqueta['id']; ?>">
                                <?php echo $etiqueta['nombre']; ?>
                            </label>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="crear-curso-acciones">
                <button type="submit" name="enviar" class="btn-cursolandia">
                    Crear curso
                </button>
            </div>
        </form>
    </div>
</div>

<?php include "includes/footer.php"; ?>