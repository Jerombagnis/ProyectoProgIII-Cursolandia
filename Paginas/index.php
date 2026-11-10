<?php
session_start();
$titulo = "Cursolandia";
include 'includes/header.php';
?>

<h1>Bienvenido a Cursolandia</h1>

<?php if (isset($_SESSION['usuario_id'])) { ?>
    <p>Hola, <?php echo $_SESSION['usuario_nombre']; ?>. Acá va a ir la lista de cursos.</p>
<?php } else { ?>
    <p>Registrate o iniciá sesión para empezar.</p>
<?php } ?>

<?php include 'includes/footer.php'; ?>