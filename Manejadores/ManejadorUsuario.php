<?php 

class ManejadorUsuario {
    
    private $conexion;
 
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
 
    public function guardar($usuario) {
        $sql = "INSERT INTO usuarios (nombre, apellido, email, password)
                VALUES ('$usuario->nombre', '$usuario->apellido', '$usuario->email', '$usuario->password')
                ";
        if (!mysqli_query($this->conexion, $sql)) {
            throw new Exception("No se pudo registrar el usuario.");
        }
    }
 
    public function buscarPorEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = '$email'";
        return $this->armarUsuario(mysqli_query($this->conexion, $sql));
    }
 
    public function buscarPorId($id) {
        $sql = "SELECT * FROM usuarios WHERE id = $id";
        return $this->armarUsuario(mysqli_query($this->conexion, $sql));
    }
 
    private function armarUsuario($resultado) {
        if (!$resultado) {
            throw new Exception("Error al consultar los usuarios.");
        }
        $fila = mysqli_fetch_assoc($resultado);
        if (!$fila) {
            return null;
        }
        return new Usuario($fila['id'], $fila['nombre'], $fila['apellido'], $fila['email'], $fila['password'], $fila['rol'], $fila['vencimiento_pro'],$fila['antecedentes'], $fila['imagen_perfil']);
    }
}

?>