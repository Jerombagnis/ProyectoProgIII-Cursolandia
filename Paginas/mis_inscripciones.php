<?php
session_start();

// verifica que el usuario esté logueado, sino lo redirige al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include "../Conexiones/conexion.php";
include "../Clases/Curso.php";
include "../Clases/Inscripcion.php";
include "../Manejadores/ManejadorInscripcion.php";

$items = array();
$error = "";

try {
    $manejadorInscripcion = new ManejadorInscripcion($conexion);
    $items = $manejadorInscripcion->listarPorUsuario($_SESSION['usuario_id']);
} catch (Exception $e) {
    $error = $e->getMessage();
}

include "../Conexiones/cerrar_conexion.php";
?>
<?php include "includes/header.php"; ?>

<div class="container my-4">

    <div class="mis-inscripciones-card-header">

    <div>
        <span class="section-kicker">MI ACTIVIDAD</span>
        <h1 class="mis-inscripciones-title">Mis inscripciones</h1>
        <p class="section-description">
            Consultá el estado de tus inscripciones y accedé a tus cursos.
        </p>
    </div>

    <a href="index.php" class="btn-curso-secondary">
        Explorar cursos
    </a>

</div>

    <?php if ($error != "") { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php } elseif (count($items) == 0) { ?>
        <p>Todavía no te inscribiste a ningún curso. <a href="index.php">Mirá los cursos disponibles</a>.</p>
    <?php } else { ?>

    <div class="mis-inscripciones-grid">

        <?php foreach ($items as $item) { ?>
            <?php $curso = $item['curso']; ?>

            <article class="mis-inscripcion-card">

                <div class="mis-inscripcion-card-header">
                    <h2 class="mis-inscripcion-title">
                        <?php echo $curso->titulo; ?>
                    </h2>

                    <?php if ($item['estado'] == "confirmada") { ?>
                        <span class="curso-badge badge-activo">Confirmada</span>
                    <?php } elseif ($item['estado'] == "pendiente") { ?>
                        <span class="curso-badge badge-pendiente">Pendiente</span>
                    <?php } else { ?>
                        <span class="curso-badge badge-rechazada">Rechazada</span>
                    <?php } ?>
                </div>

                <div class="mis-inscripcion-details">

                    <div class="mis-inscripcion-detail">
                        <span class="mis-inscripcion-label">Estado del curso</span>

                        <?php if ($curso->estaActivo() == 'activo') { ?>
                            <span class="curso-badge badge-activo">Activo</span>
                        <?php } elseif ($curso->estaActivo() == 'proximamente') { ?>
                            <span class="curso-badge badge-proximamente">Próximamente</span>
                        <?php } else { ?>
                            <span class="curso-badge badge-finalizado">Finalizado</span>
                        <?php } ?>
                    </div>

                    <div class="mis-inscripcion-detail">
                        <span class="mis-inscripcion-label">Costo</span>
                        <strong>
                            <?php if ($curso->esGratuito()) { ?>
                                Gratuito
                            <?php } else { ?>
                                $<?php echo $curso->costo; ?>
                            <?php } ?>
                        </strong>
                    </div>

                </div>

                <div class="mis-inscripcion-actions">
                    <a href="curso.php?id=<?php echo $curso->id; ?>" class="btn-cursolandia">
                        Ver curso
                    </a>
                </div>

            </article>

        <?php } ?>

    </div>

    <?php } ?>
</div>

<?php include "includes/footer.php"; ?>