<?php
class Mail_AccountModel extends ModelBase
{
    public function __construct($db, $iniciar = false)
    {
        $this->name = "mail_account";
        $this->table = "rel_mail_account";
        $this->columns = array (
            "user_id" => new Many2one("User", "account", Constants::$PanelAreaName),
            "mail_id" => new Many2One("Mail", "mail", Constants::$PanelAreaName),
            "date_sent" => new Fechahora("Fecha de envio"),
            "status" => new Integer("Estado", 1), // 0 - No enviado; 1 - Errores al enviar; 2 - Enviado
        );
        parent::__construct($db, $iniciar);
    }
}

?>