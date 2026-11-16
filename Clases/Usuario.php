<?php 

class Usuario {
    private $id;
    private $nombre;
    private $apellido;
    private $email;
    private $password;          
    private $rol;               
    private $vencimientoPro;    
    private $antecedentes;
    private $imagenPerfil;
 
    public function __construct($id, $nombre, $apellido, $email, $password, $rol, $vencimientoPro, $antecedentes, $imagenPerfil) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->email = $email;
        $this->password = $password;
        $this->rol = $rol;
        $this->vencimientoPro = $vencimientoPro;
        $this->antecedentes = $antecedentes;
        $this->imagenPerfil = $imagenPerfil;
    }
 
    public function __get($nombre) {
        return $this->$nombre;
    }
 
    public function esPro() {
        return $this->vencimientoPro != null && $this->vencimientoPro >= date("Y-m-d");
    }
 
    public function esAdmin() {
        return $this->rol == "admin";
    }

    // metodo para validar los datos del usuario
    public function validar() {
        if (trim($this->nombre) == "") {
            throw new Exception("El nombre es obligatorio.");
        }
        if (trim($this->apellido) == "") {
            throw new Exception("El apellido es obligatorio.");
        }
    }

    // metodo para validar el cambio de contraseña del usuario. Se le pasa la contraseña actual, la nueva y la repetida. Si alguna no cumple con las condiciones, lanza una excepción.
    public function validarCambioClave($actual, $nueva, $repetida) {
        if (self::cifrar($actual) != $this->password) { //self para llamar a un metodo estatico de la misma clase
            throw new Exception("La contraseña actual no es correcta.");
        }
        if ($nueva == "") {
            throw new Exception("La contraseña nueva no puede estar vacía.");
        }
        if ($nueva != $repetida) {
            throw new Exception("Las contraseñas no coinciden.");
        }
    }
 
    // metodo estatico para cifrar la contraseña del usuario. static porque no necesita acceder a ningun atributo de la clase para hacer su funcion. 
    public static function cifrar($clave) {
        return md5($clave);
    }
}

?>