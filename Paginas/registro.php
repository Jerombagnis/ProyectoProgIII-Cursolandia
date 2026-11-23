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

<div class="auth-container">
    <div class="auth-card auth-card-registro">

        <div class="auth-header">
            <span class="section-kicker">EMPEZÁ TU CAMINO</span>
            <h1 class="auth-title">Creá tu cuenta</h1>
            <p class="section-description">
                Unite a Cursolandia y descubrí nuevas oportunidades de aprendizaje.
            </p>
        </div>

        <!-- muestra de errores -->
        <?php if ($error != "") { ?>
            <div class="auth-alert auth-alert-error">
                <?php echo $error; ?>
            </div>
        <?php } ?>

        <form action="registro.php" method="POST" class="auth-form">
            <div class="auth-field">
                <label class="auth-label">Nombre</label>
                <input type="text" name="nombre" class="form-control" placeholder="Tu nombre" value="<?php echo $nombre; ?>">
            </div>

            <div class="auth-field">
                <label class="auth-label">Apellido</label>
                <input type="text" name="apellido" class="form-control" placeholder="Tu apellido" value="<?php echo $apellido; ?>">
            </div>

            <div class="auth-field">
                <label class="auth-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="ejemplo@correo.com" value="<?php echo $email; ?>">
            </div>

            <div class="auth-field">
                <label class="auth-label">Contraseña</label>
                <input type="password" name="password" class="form-control" placeholder="Creá una contraseña">
            </div>

            <div class="auth-field">
                <label class="auth-label">Repetir contraseña</label>
                <input type="password" name="password2" class="form-control" placeholder="Volvé a ingresar tu contraseña">
            </div>

            <button type="submit" name="enviar" class="btn-cursolandia auth-submit">
                Crear mi cuenta
            </button>

        </form>

        <div class="auth-footer">
            <p>
                ¿Ya tenés una cuenta?
                <a href="login.php">Iniciá sesión</a>
            </p>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>