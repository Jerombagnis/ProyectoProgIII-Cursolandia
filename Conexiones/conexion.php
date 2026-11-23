
<?php

$rutaEnv = dirname(__DIR__) . '/.env';

if (!file_exists($rutaEnv)) {
    die("No se encontró el archivo .env");
}

$config = [];

$lineas = file($rutaEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lineas as $linea) {

    $linea = trim($linea);

    if (str_starts_with($linea, '#')) {
        continue;
    }

    if (strpos($linea, '=') === false) {
        continue;
    }

    [$clave, $valor] = explode('=', $linea, 2);

    $valor = trim($valor);

    if (strlen($valor) >= 2 &&
        (($valor[0] === '"' && substr($valor, -1) === '"') ||
         ($valor[0] === "'" && substr($valor, -1) === "'"))) {
        $valor = substr($valor, 1, -1);
    }

    $config[trim($clave)] = $valor;
}

$conexion = mysqli_connect(
    $config['DB_HOST'],
    $config['DB_USER'],
    $config['DB_PASSWORD'],
    $config['DB_NAME']
);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8mb4");

?>

