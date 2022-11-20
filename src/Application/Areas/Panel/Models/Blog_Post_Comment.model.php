<?php
class Blog_Post_CommentModel extends ModelBase
{
    public function __construct($db, $iniciar = false)
    {
		$this->name = "blog_post_comment";
		$this->table = "blog_post_comments";
		$this->columns = array (
            "post_id" => new Many2one("Post", "blog_post", Constants::$PanelAreaName),
			"user_id" => new Many2one("Usuario", "account", Constants::$PanelAreaName),
			"comments" => new Text("Comentario", true),
            "date_published" => new Fechahora("Fecha publicación", true),
		);
		parent::__construct($db, $iniciar);
	}
}
?>