<?php 

include '../Conexiones/conexion.php'; 

$sql = "SELECT * FROM usuarios";
$resultado = mysqli_query($conexion, $sql);

if($resultado) {
    while($fila = mysqli_fetch_assoc($resultado)) {
        echo "ID: " . $fila['id'] . " - Nombre: " . $fila['nombre'] . " - Email: " . $fila['email'] . "<br>";
    }
} else {
    echo "Error en la consulta: " . mysqli_error($conexion);
}

include '../Conexiones/cerrar_conexion.php';

?>