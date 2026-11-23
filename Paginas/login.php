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

<div class="auth-container">
    <div class="auth-card">

        <div class="auth-header">
            <span class="section-kicker">TU ESPACIO DE APRENDIZAJE</span>
            <h1 class="auth-title">¡Bienvenido de nuevo!</h1>
            <p class="section-description">
                Ingresá a tu cuenta y continuá aprendiendo en Cursolandia.
            </p>
        </div>

        <?php if (isset($_GET['registrado'])) { ?>
            <div class="auth-alert auth-alert-success">
                Registro exitoso. Ya podés iniciar sesión.
            </div>
        <?php } ?>

        <?php if ($error != "") { ?>
            <div class="auth-alert auth-alert-error">
                <?php echo $error; ?>
            </div>
        <?php } ?>

        <form action="login.php" method="POST" class="auth-form">
            <div class="auth-field">
                <label class="auth-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="ejemplo@correo.com" value="<?php echo $email; ?>">
            </div>

            <div class="auth-field">
                <label class="auth-label">Contraseña</label>
                <input type="password" name="password" class="form-control" placeholder="Ingresá tu contraseña">
            </div>

            <button type="submit" name="enviar" class="btn-cursolandia auth-submit">
                Ingresar
            </button>

        </form>

        <div class="auth-footer">
            <p>
                ¿Todavía no tenés una cuenta?
                <a href="registro.php">Registrate</a>
            </p>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>