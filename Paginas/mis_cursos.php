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
include "../Manejadores/ManejadorCurso.php";
include "../Manejadores/ManejadorInscripcion.php";

$items = array();
$error = "";

try {
    $manejadorCurso = new ManejadorCurso($conexion);
    $manejadorInscripcion = new ManejadorInscripcion($conexion);

    $cursos = $manejadorCurso->listarPorCreador($_SESSION['usuario_id']);

    // cuenta la cantidad de solicitudes pendientes de cada curso 
    foreach ($cursos as $curso) {
        $pendientes = $manejadorInscripcion->listarPorCurso($curso->id, "pendiente");
        $items[] = array('curso' => $curso,'pendientes' => count($pendientes));
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

include "../Conexiones/cerrar_conexion.php";
?>
<?php include "includes/header.php"; ?>

<div class="container my-4">

    <div class="mis-cursos-header">

        <div>
            <span class="section-kicker">PANEL DE GESTIÓN</span>
            <h1 class="mis-cursos-title">Mis cursos</h1>
            <p class="section-description">
                Administrá tus cursos, revisá las inscripciones y seguí su actividad.
            </p>
        </div>

        <a href="crear_curso.php" class="btn-cursolandia">
            Crear curso
        </a>

    </div>

    <?php if ($error != "") { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php } elseif (count($items) == 0) { ?>
        <p>Todavía no creaste ningún curso.</p>
    <?php } else { ?>

        <div class="mis-cursos-grid">

            <?php foreach ($items as $item) { ?>
                <?php $curso = $item['curso']; ?>

                <article class="mis-curso-card">

                    <div class="mis-curso-card-header">
                        <h2 class="mis-curso-card-title">
                            <?php echo $curso->titulo; ?>
                        </h2>

                        <?php if ($curso->estaActivo() == 'activo') { ?>
                            <span class="curso-badge badge-activo">Activo</span>
                        <?php } elseif ($curso->estaActivo() == 'proximamente') { ?>
                            <span class="curso-badge badge-proximamente">Próximamente</span>
                        <?php } else { ?>
                            <span class="curso-badge badge-finalizado">Finalizado</span>
                        <?php } ?>
                    </div>

                    <div class="mis-curso-details">

                        <div class="mis-curso-detail">
                            <span class="mis-curso-label">Acceso</span>
                            <strong>
                                <?php if ($curso->esPrivado()) { ?>
                                    Privado
                                <?php } else { ?>
                                    Público
                                <?php } ?>
                            </strong>
                        </div>

                        <div class="mis-curso-detail">
                            <span class="mis-curso-label">Costo</span>
                            <strong>
                                <?php if ($curso->esGratuito()) { ?>
                                    Gratuito
                                <?php } else { ?>
                                    $<?php echo $curso->costo; ?>
                                <?php } ?>
                            </strong>
                        </div>

                    </div>

                    <div class="mis-curso-pendientes">
                        <?php if ($item['pendientes'] > 0) { ?>
                            <span class="solicitudes-pendientes">
                                <?php echo $item['pendientes']; ?> solicitud(es) pendiente(s)
                            </span>
                        <?php } else { ?>
                            <span class="mis-curso-sin-pendientes">
                                No hay solicitudes pendientes
                            </span>
                        <?php } ?>
                    </div>

                    <div class="mis-curso-actions">
                        <a href="curso.php?id=<?php echo $curso->id; ?>" class="btn-cursolandia">
                            Ver curso
                        </a>

                        <a href="solicitudes.php?id=<?php echo $curso->id; ?>" class="btn-curso-secondary">
                            Solicitudes e inscriptos
                        </a>
                    </div>

                </article>

            <?php } ?>

        </div>

    <?php } ?>
</div>

<?php include "includes/footer.php"; ?>