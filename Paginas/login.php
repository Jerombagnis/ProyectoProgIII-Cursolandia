<?php
session_start();
include '../Conexiones/conexion.php';
include '../Clases/Usuario.php';
include '../Manejadores/ManejadorUsuario.php';

$error = "";
$email = "";

if (isset($_POST['enviar'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($email == "" || $password == "") {
        $error = "Ingresá tu email y tu contraseña.";
    } else {
        try {
            $manejador = new ManejadorUsuario($conexion);
            $usuario = $manejador->buscarPorEmail($email);

            // control de usuario y contraseña
            if ($usuario == null || $usuario->password != Usuario::cifrar($password)) {
                $error = "Email o contraseña incorrectos.";
            } else {
                $_SESSION['usuario_id'] = $usuario->id;
                $_SESSION['usuario_nombre'] = $usuario->nombre;
                $_SESSION['usuario_rol'] = $usuario->rol;
                include '../Conexiones/cerrar_conexion.php';
                header("Location: index.php");
                exit;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

include '../Conexiones/cerrar_conexion.php';
$titulo = "Ingresar - Cursolandia";
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <h1 class="mb-4">Ingresar</h1>

        <?php if (isset($_GET['registrado'])) { ?>
            <div class="alert alert-success">Registro exitoso. Ya podés iniciar sesión.</div>
        <?php } ?>

        <?php if ($error != "") { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="text" name="email" class="form-control" value="<?php echo $email; ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control">
            </div>
            <input type="submit" name="enviar" value="Ingresar" class="btn btn-primary">
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>