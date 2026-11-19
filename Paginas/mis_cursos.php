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

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Mis cursos</h1>
        <a href="crear_curso.php" class="btn btn-primary">Crear curso</a>
    </div>

    <?php if ($error != "") { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php } elseif (count($items) == 0) { ?>
        <p>Todavía no creaste ningún curso.</p>
    <?php } else { ?>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Estado</th>
                        <th>Acceso</th>
                        <th>Costo</th>
                        <th>Solicitudes</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item) { ?>
                        <?php $curso = $item['curso']; ?>
                        <tr>
                            <td><?php echo $curso->titulo; ?></td>
                            <td>
                                <?php if ($curso->estaActivo() == 'activo' ){ ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php } elseif ($curso->estaActivo()== 'proximamente'){ ?>
                                        <span class="badge bg-info text-dark">Próximamente</span>
                                    <?php } else{ ?>
                                            <span class="badge bg-danger">Finalizado</span>
                                    <?php } ?>
                            </td>
                            <td>
                                <?php if ($curso->esPrivado()) { ?>
                                    Privado
                                <?php } else { ?>
                                    Público
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($curso->esGratuito()) { ?>
                                    Gratuito
                                <?php } else { ?>
                                    $<?php echo $curso->costo; ?>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($item['pendientes'] > 0) { ?>
                                    <span class="badge bg-warning text-dark"><?php echo $item['pendientes']; ?> pendiente(s)</span>
                                <?php } else { ?>
                                    -
                                <?php } ?>
                            </td>
                            <td class="text-end">
                                <a href="curso.php?id=<?php echo $curso->id; ?>" class="btn btn-outline-primary btn-sm">Ver</a>
                                <a href="solicitudes.php?id=<?php echo $curso->id; ?>" class="btn btn-outline-secondary btn-sm">Solicitudes e inscriptos</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    <?php } ?>
</div>

<?php include "includes/footer.php"; ?>