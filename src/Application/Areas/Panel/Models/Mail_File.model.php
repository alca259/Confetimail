<?php
class Mail_FileModel extends ModelBase
{
    public function __construct($db, $iniciar = false)
    {
        $this->name = "mail_file";
        $this->table = "rel_mail_file";
        $this->columns = array (
            "file_id" => new Many2one("File", "file", Constants::$PanelAreaName),
            "mail_id" => new Many2One("Mail", "mail", Constants::$PanelAreaName),
        );
        parent::__construct($db, $iniciar);
    }
}
?>