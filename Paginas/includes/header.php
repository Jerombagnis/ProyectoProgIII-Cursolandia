<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($titulo) ? $titulo : "Cursolandia"; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-cursolandia">
    <div class="container">
        <a class="navbar-brand" href="index.php"><img src="../Archivos/Icos/Cursolandia_logo.jpeg" width="200px" heigth="auto"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-controls="menu" aria-expanded="false" aria-label="Abrir menú">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-auto align-items-md-center gap-lg-2">

                <?php if (isset($_SESSION['usuario_id'])) { ?>

                    <li class="nav-item">
                        <span class="navbar-text user-greeting">Hola, <?php echo $_SESSION['usuario_nombre']; ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-cursolandia" href="perfil.php">Mi perfil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-cursolandia" href="mis_cursos.php">Mis cursos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-cursolandia" href="mis_inscripciones.php">Mis inscripciones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-cursolandia" href="solicitar_pro.php">Plan Pro</a>
                    </li>
                    <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == "admin") { ?>
                        <li class="nav-item">
                            <a class="nav-link nav-cursolandia" href="admin_pagos.php">Administración</a>
                        </li>
                    <?php } ?>
                    <li class="nav-item">
                        <a class="nav-link nav-cursolandia" href="logout.php">Cerrar sesión</a>
                    </li>
                <?php } else { ?>
                    <li class="nav-item">
                        <a class="nav-link nav-cursolandia" href="login.php">Ingresar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-cursolandia" href="registro.php">Registrarse</a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">