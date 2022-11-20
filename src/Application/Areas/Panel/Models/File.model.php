<?php
class FileModel extends ModelBase
{
    public function __construct($db, $iniciar = false)
    {
        $this->name = "file";
        $this->table = "res_files";
        $this->columns = array (
            "name" => new Char("Nombre del fichero", 256, true),
            "file_url" => new Char("Ruta de guardado", 512, true),
            "full_url" => new Char("Ruta de guardado absoluta", 512, true),
            "file_type" => new Selection("Tipo de fichero", array(
                "image" => "Imagen",
                "zip" => "Comprimido",
                "pdf" => "PDF",
                "other" => "Varios",
            ), 32, true),
            "file_category" => new Selection("Categoria de fichero", array(
                "frontend" => "Blog",
                "mail" => "Correo",
                "other" => "Varios",
            ), 32, true),
            "active" => new Boolean("Activo"),
        );
        parent::__construct($db, $iniciar);
    }
}
?>