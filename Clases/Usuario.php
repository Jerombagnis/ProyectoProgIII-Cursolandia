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
 
    // metodo estatico para cifrar la contraseña del usuario. 
    public static function cifrar($clave) {
        return md5($clave);
    }
}

?>