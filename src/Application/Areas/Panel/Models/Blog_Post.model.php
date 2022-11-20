<?php
class Blog_PostModel extends ModelBase
{
    public function __construct($db, $iniciar = false)
    {
		$this->name = "blog_post";
		$this->table = "blog_posts";
		$this->columns = array (
			"subject" => new Char("Título", 512, true),
			"post_body" => new Text("Entrada", true),
			"image_frontend" => new Char("Imagen portada", 512, true),
			"active" => new Boolean("Público"),
            "date_published" => new Date("Fecha publicación"),
		);
		parent::__construct($db, $iniciar);
	}
}
?>