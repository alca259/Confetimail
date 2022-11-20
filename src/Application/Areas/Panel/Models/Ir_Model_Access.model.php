<?php
class Ir_Model_AccessModel extends ModelBase
{
    public function __construct($db, $iniciar = false)
    {
        $this->name = "ir_model_access";
        $this->table = "ir_model_access";
        $this->columns = array (
            'active' => new Boolean('Active'),
            'model_id' => new Many2one('Object', 'ir_model', Constants::$PanelAreaName),
            'user_id' => new Many2one('User', 'account', Constants::$PanelAreaName),
            'perm_read' => new Boolean('Read Access'),
            'perm_write' => new Boolean('Write Access'),
            'perm_create' => new Boolean('Create Access'),
            'perm_unlink' => new Boolean('Delete Access'),
            'perm_read_anon' => new Boolean('Anonymous Read Access'),
            'perm_write_anon' => new Boolean('Anonymous Write Access'),
            'perm_create_anon' => new Boolean('Anonymous Create Access'),
            'perm_unlink_anon' => new Boolean('Anonymous Delete Access'),
        );
        parent::__construct($db, $iniciar);
    }
}
?>