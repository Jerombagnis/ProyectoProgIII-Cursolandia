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

    // metodo para actualizar datos generales de un usuario (perfil)
    public function actualizarDatos($usuario) {
        $sql = "UPDATE usuarios
                SET nombre = '$usuario->nombre',
                    apellido = '$usuario->apellido',
                    antecedentes = '$usuario->antecedentes'
                WHERE id = $usuario->id";
        if (!mysqli_query($this->conexion, $sql)) {
            throw new Exception("No se pudieron actualizar los datos.");
        }
    }

    // metodo para modificar la constraseña de un usuario. 
    public function cambiarPassword($id, $passwordCifrada) {
        $sql = "UPDATE usuarios 
                SET password = '$passwordCifrada' 
                WHERE id = $id";
        if (!mysqli_query($this->conexion, $sql)) {
            throw new Exception("No se pudo cambiar la contraseña.");
        }
    }

    // metodo para obtener los intereses de un usuario. Devuelve un array con los ids de las etiquetas
    public function interesesDeUsuario($usuario_id) {
        $sql = "SELECT etiqueta_id FROM usuario_intereses WHERE usuario_id = $usuario_id";
        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception("No se pudieron cargar los intereses.");
        }

        $ids = array();
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $ids[] = $fila['etiqueta_id'];
        }
        return $ids;
    }

    // metodo para guardar los intereses de un usuario 
    public function guardarIntereses($usuario_id, $etiquetas_ids) {
        $sql = "DELETE FROM usuario_intereses WHERE usuario_id = $usuario_id"; // borro los intereses anteriores del usuario 
        if (!mysqli_query($this->conexion, $sql)) {
            throw new Exception("No se pudieron actualizar los intereses.");
        }

        foreach ($etiquetas_ids as $etiqueta_id) {
            if (is_numeric($etiqueta_id)) {         
                $sql = "INSERT INTO usuario_intereses (usuario_id, etiqueta_id) 
                        VALUES ($usuario_id, $etiqueta_id)";                // guardo los intereses nuevos del usuario
                if (!mysqli_query($this->conexion, $sql)) {
                    throw new Exception("No se pudieron guardar los intereses.");
                }
            }
        }
    }

    // metodo para cambiar la imagen de perfil de un usuario. Se le pasa el id del usuario y el nombre del archivo de la imagen
    public function cambiarImagen($id, $nombreArchivo) {
        $sql = "UPDATE usuarios SET imagen_perfil = '$nombreArchivo' 
                WHERE id = $id";
                
        if (!mysqli_query($this->conexion, $sql)) {
            throw new Exception("No se pudo guardar la foto de perfil.");
        }
    }
}

?>