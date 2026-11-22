<?php
session_start();

include "../Conexiones/conexion.php";
include "../Clases/Usuario.php";
include "../Manejadores/ManejadorUsuario.php";
include "../Clases/Curso.php";
include "../Manejadores/ManejadorCurso.php";

// si "q" no está seteada, la inicializo en vacío para que no tire error
// si "q" está seteada, recibio el valor que se pasó por GET (texto de búsqueda)
$q = "";
if (isset($_GET['q'])) {
    $q = trim($_GET['q']);
}

$cursos = array();
$recomendados = array();
$destacados = array();
$resultados = array();
$tiene_intereses = false;
$error = "";

try {
    $manejadorCurso = new ManejadorCurso($conexion);
    $manejadorUsuario = new ManejadorUsuario($conexion);

    if ($q != "") {
        // si q esta seteada, se realiza la búsqueda y se muestran los resultados
        $cursos = $manejadorCurso->buscarPublicos($q);
    } else {
        // si q no esta seteada, entonces se listan recomendados (si hay sesion), destacados y todos los cursos públicos
        $cursos = $manejadorCurso->listarPublicos();

        if (isset($_SESSION['usuario_id'])) {
            $intereses = $manejadorUsuario->interesesDeUsuario($_SESSION['usuario_id']);

            if (count($intereses) > 0) {
                $tiene_intereses = true;

                $cursos_recomendados = $manejadorCurso->listarRecomendados($_SESSION['usuario_id']);
                foreach ($cursos_recomendados as $curso) {
                    // array que en cada posicion pose otro arrayasociativo con curso y sus etiquetas
                    $recomendados[] = array('curso' => $curso,'etiquetas' => $manejadorCurso->etiquetasDeCurso($curso->id)); 
                }
            }
        }

        // obtengo los cursos destacados (3 maximo) de forma aleatoria
        $cursos_destacados = $manejadorCurso->listarDestacados();
        foreach ($cursos_destacados as $curso) {
            $destacados[] = array('curso' => $curso,'etiquetas' => $manejadorCurso->etiquetasDeCurso($curso->id));
        }
    }

    // por cada curso de la lista (ya sea recomendados, destacados o todos los cursos públicos) se le buscan sus etiquetas
    foreach ($cursos as $curso) {
        $resultados[] = array('curso' => $curso,'etiquetas' => $manejadorCurso->etiquetasDeCurso($curso->id)
        );
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

include "../Conexiones/cerrar_conexion.php";
?>
<?php include "includes/header.php"; ?>

<div class="container my-4">


    <section class="hero-home">

        <div class="hero-content">

            <span class="hero-label">
                APRENDIZAJE SIN LÍMITES
            </span>

            <h1>
                Aprendé algo nuevo.<br>
                <span>Impulsá tu futuro.</span>
            </h1>

            <p class="hero-description">
                Descubrí cursos, desarrollá nuevas habilidades
                y aprendé a tu propio ritmo.
            </p>

            <form method="get" action="index.php" class="search-home">

                <div class="search-input-wrapper">
                    <span class="search-icon">⌕</span>

                    <input
                        type="text"
                        name="q"
                        class="search-input"
                        placeholder="¿Qué te gustaría aprender?"
                        value="<?php echo $q; ?>"
                    >
                </div>

                <button type="submit" class="search-button">
                    Buscar cursos
                    <span>→</span>
                </button>

            </form>

            <div class="hero-caption">
                Explorá nuestra colección de cursos y encontrá tu próximo desafío.
            </div>

        </div>

    </section>


    <?php if ($error != "") { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php } else { ?>

        <!-- si la variable "q" está vacía y hay sesión iniciada, se muestran primero los recomendados para el usuario -->
        <?php if ($q == "" && isset($_SESSION['usuario_id'])) { ?>
            <div class="section-heading">
                <span class="section-kicker">PERSONALIZADO</span>
                <h2 class="section-title">Recomendados para vos</h2>
                <p class="section-description">
                    Cursos seleccionados según tus intereses.
                </p>
            </div>

            <?php if (!$tiene_intereses) { ?>
                <div class="alert alert-info">
                    Elegí tus intereses en <a href="perfil.php" class="alert-link">tu perfil</a> y te recomendamos cursos.
                </div>
            <?php } elseif (count($recomendados) == 0) { ?>
                <p>Por ahora no hay cursos que coincidan con tus intereses.</p>
            <?php } else { ?>
                <div class="row g-4">
                    <?php foreach ($recomendados as $item) { ?>
                        <?php include "includes/tarjeta_curso.php"; ?>
                    <?php } ?>
                </div>
            <?php } ?>
        <?php } ?>    

        <!-- si la variable "q" está vacía y hay cursos destacados, se muestran primero los destacados -->
        <?php if ($q == "" && count($destacados) > 0) { ?>
            <div class="section-heading">
                <span class="section-kicker">SELECCIÓN EXCLUSIVA</span>
                <h2 class="section-title">Cursos destacados</h2>
                <p class="section-description">
                    Descubrí algunos de los cursos que tenemos para ofrecerte.
                </p>
            </div>
            <div class="row g-4">

                <!-- recorro el array de destacados y por cada uno incluyo la tarjeta de curso -->
                <?php foreach ($destacados as $item) { ?>
                    <?php include "includes/tarjeta_curso.php"; ?>
                <?php } ?>
            </div>
        <?php } ?>

        <div class="section-heading">
            <span class="section-kicker">EXPLORÁ</span>

            <?php if ($q != "") { ?>
                <h2 class="section-title">
                    Resultados para "<?php echo $q; ?>"
                </h2>
                <p class="section-description">
                    Estos cursos coinciden con tu búsqueda.
                </p>
            <?php } else { ?>
                <h2 class="section-title">Todos los cursos</h2>
                <p class="section-description">
                    Explorá nuestro catálogo y encontrá algo nuevo para aprender.
                </p>
            <?php } ?>
        </div>

        <?php if (count($resultados) == 0) { ?>
            <p>No se encontraron cursos.</p>
        <?php } else { ?>
            <div class="row g-4">
                <?php foreach ($resultados as $item) { ?>
                    <?php include "includes/tarjeta_curso.php"; ?>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if ($q != "") { ?>
            <a href="index.php" class="btn btn-secondary">Ver todos los cursos</a>
        <?php } ?>

    <?php } ?>
</div>

<?php include 'includes/footer.php'; ?>