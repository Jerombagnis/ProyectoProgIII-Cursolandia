<?php

//  las variables $curso y $etiquetas reciben $item que se pasan desde el archivo (index) donde se incluye esta tarjeta
$curso = $item['curso'];
$etiquetas = $item['etiquetas'];
?>
<div class="col-md-4 mb-4">
    <div class="card h-100">
        <div class="card-body">
            <h5 class="card-title"><?php echo $curso->titulo; ?></h5>

            <p>
                <?php if ($curso->esGratuito()) { ?>
                    <span class="badge bg-success">Gratuito</span>
                <?php } else { ?>
                    <span class="badge bg-warning text-dark">$<?php echo $curso->costo; ?></span>
                <?php } ?>

                <?php if ($curso->estaActivo()) { ?>
                    <span class="badge bg-primary">Activo</span>
                <?php } else { ?>
                    <span class="badge bg-info text-dark">Próximamente</span>
                <?php } ?>
            </p>

            <p class="card-text">
                <?php

                // controlo que solo muestre los primeros 100 caracteres de la descripción
                if (strlen($curso->descripcion) > 100) {
                    echo substr($curso->descripcion, 0, 100) . "..."; 
                } else {
                    echo $curso->descripcion;
                }
                ?>
            </p>

            <?php foreach ($etiquetas as $etiqueta) { ?>
                <span class="badge rounded-pill text-bg-light border"><?php echo $etiqueta['nombre']; ?></span>
            <?php } ?>
        </div>
        <div class="card-footer bg-transparent">
            <a href="curso.php?id=<?php echo $curso->id; ?>" class="btn btn-primary btn-sm">Ver curso</a>
        </div>
    </div>
</div>