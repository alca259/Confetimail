<?php
class Review_PostModel extends ModelBase
{
    public function __construct($db, $iniciar = false)
    {
		$this->name = "review_post";
		$this->table = "review_posts";
		$this->columns = array (
			"user_id" => new Many2one("Usuario", "account", Constants::$PanelAreaName),
			"comments" => new Text("Comentario", true),
            "date_published" => new Fechahora("Fecha publicaci�n", true),
            "score" => new Float("Valoraci�n", 11, true)
		);
		parent::__construct($db, $iniciar);
	}
}
?>