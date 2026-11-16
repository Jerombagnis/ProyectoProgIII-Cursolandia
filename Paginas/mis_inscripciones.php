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

    <h1 class="mb-4">Mis inscripciones</h1>

    <?php if ($error != "") { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php } elseif (count($items) == 0) { ?>
        <p>Todavía no te inscribiste a ningún curso. <a href="index.php">Mirá los cursos disponibles</a>.</p>
    <?php } else { ?>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Mi inscripción</th>
                        <th>Curso</th>
                        <th>Costo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item) { ?>
                        <?php $curso = $item['curso']; ?>
                        <tr>
                            <td><?php echo $curso->titulo; ?></td>
                            <td>
                                <?php if ($item['estado'] == "confirmada") { ?>
                                    <span class="badge bg-success">Confirmada</span>
                                <?php } elseif ($item['estado'] == "pendiente") { ?>
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                <?php } else { ?>
                                    <span class="badge bg-danger">Rechazada</span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($curso->estaActivo()) { ?>
                                    <span class="badge bg-primary">Activo</span>
                                <?php } elseif ($curso->haTerminado()) { ?>
                                    <span class="badge bg-secondary">Finalizado</span>
                                <?php } else { ?>
                                    <span class="badge bg-info text-dark">Próximamente</span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($curso->esGratuito()) { ?>
                                    Gratuito
                                <?php } else { ?>
                                    $<?php echo $curso->costo; ?>
                                <?php } ?>
                            </td>
                            <td class="text-end">
                                <a href="curso.php?id=<?php echo $curso->id; ?>" class="btn btn-outline-primary btn-sm">Ver curso</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    <?php } ?>
</div>

<?php include "includes/footer.php"; ?>