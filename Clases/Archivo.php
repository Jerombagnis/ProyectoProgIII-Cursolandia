<?php
class Archivo {
    private $id;
    private $curso_id;
    private $nombre_original;
    private $ruta;
    private $tamano_bytes;
    private $created_at;

    public function __construct($id, $curso_id, $nombre_original, $ruta, $tamano_bytes, $created_at) {
        $this->id = $id;
        $this->curso_id = $curso_id;
        $this->nombre_original = $nombre_original;
        $this->ruta = $ruta;
        $this->tamano_bytes = $tamano_bytes;
        $this->created_at = $created_at;
    }

    public function __get($propiedad) {
        return $this->$propiedad;
    }

    // Metodo para devolver el tamaño en texto legible
    public function tamanoEnTexto() {
        if ($this->tamano_bytes >= 1024 * 1024) {
            return round($this->tamano_bytes / (1024 * 1024), 1) . " MB";
        }
        return round($this->tamano_bytes / 1024) . " KB";
    }

    // metodo para validar el archivo subido
    public static function validarArchivo($archivo) {
        if ($archivo['error'] == 4) {
            throw new Exception("Elegí un archivo.");
        }
        if ($archivo['error'] != 0) {
            throw new Exception("No se pudo subir el archivo. Puede ser demasiado pesado.");
        }
        if ($archivo['size'] == 0) {
            throw new Exception("El archivo está vacío.");
        }

        $partes = explode(".", $archivo['name']);
        $extension = strtolower(end($partes));

        $permitidas = array("pdf", "doc", "docx", "ppt", "pptx", "xls", "xlsx", "txt", "zip", "jpg", "jpeg", "png");
        if (!in_array($extension, $permitidas)) {
            throw new Exception("Tipo de archivo no permitido. Podés subir PDF, Word, PowerPoint, Excel, TXT, ZIP o imágenes.");
        }

        return $extension;
    }
}
?>