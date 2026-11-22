<?php

//  las variables $curso y $etiquetas reciben $item que se pasan desde el archivo (index) donde se incluye esta tarjeta
$curso = $item['curso'];
$etiquetas = $item['etiquetas'];
?>
<div class="col-md-4 mb-4">
    <div class="card h-100">
        <div class="curso-card-body">
            <h5 class="curso-card-title"><?php echo $curso->titulo; ?></h5>


            <div class="curso-meta">
                <?php if ($curso->esGratuito()) { ?>
                    <span class="curso-badge badge-gratuito">Gratuito</span>
                <?php } else { ?>
                    <span class="curso-badge badge-precio">$<?php echo $curso->costo; ?></span>
                <?php } ?>

                <?php if ($curso->estaActivo()=='activo') { ?>
                    <span class="curso-badge badge-activo">Activo</span>
                <?php } else if ($curso->estaActivo() == 'proximamente'){ ?>
                    <span class="curso-badge badge-proximamente">Próximamente</span>
                <?php } ?>
            </div>

            <p class="curso-card-description">
                <?php

                // controlo que solo muestre los primeros 100 caracteres de la descripción
                if (strlen($curso->descripcion) > 100) {
                    echo substr($curso->descripcion, 0, 100) . "..."; 
                } else {
                    echo $curso->descripcion;
                }
                ?>
            </p>

            <div class="curso-tags">
                <?php foreach ($etiquetas as $etiqueta) { ?>
                    <span class="curso-tag">
                        <?php echo $etiqueta['nombre']; ?>
                    </span>
                <?php } ?>
            </div>
        </div>
        <div class="curso-card-footer">
            <a href="curso.php?id=<?php echo $curso->id; ?>" class="btn-cursolandia">Ver curso</a>
        </div>
    </div>
</div>