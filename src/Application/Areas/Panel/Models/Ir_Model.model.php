<?php
class Ir_ModelModel extends ModelBase
{
    public function __construct($db, $iniciar = false)
    {
        $this->name = "ir_model";
        $this->table = "ir_model";
        $this->columns = array (
            "name" => new Char("Nombre del modelo", 64, false),
            "description" => new Char("Descripción", 128, false),
            "active" => new Boolean("Activo"),
            "area" => new Char("Área", 32, false)
        );
        parent::__construct($db, $iniciar);
    }
}
?>