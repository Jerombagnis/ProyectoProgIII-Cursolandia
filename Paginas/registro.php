<?php
session_start();
include '../Conexiones/conexion.php';
include '../Clases/Usuario.php';
include '../Manejadores/ManejadorUsuario.php';

$error = "";
$nombre = "";
$apellido = "";
$email = "";

if (isset($_POST['enviar'])) {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    if ($nombre == "" || $apellido == "" || $email == "" || $password == "") {
        $error = "Completá todos los campos.";
    } else if (strpos($email, "@") === false) {
        $error = "El email no es válido.";
    } else if ($password != $password2) {
        $error = "Las contraseñas no coinciden.";
    } else {
        try {
            $manejador = new ManejadorUsuario($conexion);
            if ($manejador->buscarPorEmail($email) != null) {
                $error = "Ya existe un usuario registrado con ese email.";
            } else {
                $usuario = new Usuario(null, $nombre, $apellido, $email, Usuario::cifrar($password), null, null, null, null);
                $manejador->guardar($usuario);
                include '../Conexiones/cerrar_conexion.php';
                header("Location: login.php?registrado=1");
                exit;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

include '../Conexiones/cerrar_conexion.php';   
$titulo = "Registrarse - Cursolandia";
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h1 class="mb-4">Crear cuenta</h1>

        <!-- muestra de errores -->
        <?php if ($error != "") { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>

        <form action="registro.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control" value="<?php echo $nombre; ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Apellido</label>
                <input type="text" name="apellido" class="form-control" value="<?php echo $apellido; ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="text" name="email" class="form-control" value="<?php echo $email; ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Repetir contraseña</label>
                <input type="password" name="password2" class="form-control">
            </div>
            <input type="submit" name="enviar" value="Registrarme" class="btn btn-primary">
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>