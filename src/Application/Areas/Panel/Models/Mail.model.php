<?php
class MailModel extends ModelBase
{
	public function __construct($db, $iniciar = false)
    {
		$this->name = "mail";
		$this->table = "mail_message";
		$this->columns = array (
			"subject" => new Char("Título del mensaje", 512, true),
			"header_for_new" => new Text("Cabecera para nuevos registros"),
			"header_for_old" => new Text("Cabecera para viejos registros"),
			"body" => new Text("Cuerpo del mensaje"),
			"date_send" => new Fechahora("Fecha de envio"),
			"active" => new Boolean("Activo"),
            "tematica" => new Char("Temática", 64),
            "is_confeti" => new Boolean("Es un confeti"),
            "image_carousel" => new Char("Imagen portada", 512),
            "image_frontend" => new Char("Imagen miniatura", 512),
            "tematica_desc" => new Char("Descripción temática", 256),
		);
		parent::__construct($db, $iniciar);
	}
}
?>