<?php
session_start();

include "../Conexiones/conexion.php";
include "../Clases/Curso.php";
include "../Manejadores/ManejadorCurso.php";

$curso = null;
$etiquetas = array ();
$error = "";

try {
    
    //id llega por url 
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception("El curso no existe.");
    }

    $manejadorCurso = new ManejadorCurso($conexion);
    $curso = $manejadorCurso->buscarPorId($_GET['id']);

    if ($curso == null) {
        throw new Exception("El curso no existe.");
    }

    $etiquetas = $manejadorCurso->etiquetasDeCurso($curso->id);
    
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

        <h1><?php echo $curso->titulo; ?></h1>

        <p>
            <?php if ($curso->esGratuito()) { ?>
                <span class="badge bg-success">Gratuito</span>
            <?php } else { ?>
                <span class="badge bg-warning text-dark">$<?php echo $curso->costo; ?></span>
            <?php } ?>

            <?php if ($curso->esPrivado()) { ?>
                <span class="badge bg-secondary">Privado</span>
            <?php } else { ?>
                <span class="badge bg-primary">Público</span>
            <?php } ?>

            <?php if ($curso->estaActivo() == "proximamente") { ?>
                <span class="badge bg-info">Próximamente</span>
            <?php } else if ($curso->estaActivo() == "activo") { ?>
                <span class="badge bg-success">Activo</span>
            <?php } else { ?>   
                <span class="badge bg-danger">Finalizado</span>
            <?php } ?>
        </p>

        <p><?php echo $curso->descripcion; ?></p>

        <?php if (count($etiquetas) > 0) { ?>
            <p>
                <?php foreach ($etiquetas as $etiqueta) { ?>
                    <span class="badge rounded-pill text-bg-light border"><?php echo $etiqueta['nombre']; ?></span>
                <?php } ?>
            </p>
        <?php } ?>

        <ul class="list-group mb-3">
            <li class="list-group-item"><strong>Inicio:</strong> <?php echo $curso->fecha_inicio; ?></li>
            <li class="list-group-item"><strong>Fin:</strong> <?php echo $curso->fecha_fin; ?></li>
            <li class="list-group-item">
                <strong>Cupo:</strong>
                <?php
                if ($curso->cupo == "") {
                    echo "Sin límite";
                } else {
                    echo $curso->cupo;
                }
                ?>
            </li>
        </ul>

        <?php if (isset($_SESSION['usuario_id']) && $curso->esCreador($_SESSION['usuario_id'])) { ?>
            <div class="alert alert-info">Este curso es tuyo.</div>
        <?php } ?>

    <?php } ?>
</div>

<?php include "includes/footer.php"; ?>