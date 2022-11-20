<?php
class Blog_PostModel extends ModelBase
{
    public function __construct($db, $iniciar = false)
    {
        $this->name = "blog_post";
        $this->table = "blog_posts";
        $this->columns = array (
            "subject" => new Char("T�tulo", 512, true),
            "post_body" => new Text("Entrada", true),
            "image_frontend" => new Char("Imagen portada", 512, true),
            "active" => new Boolean("P�blico"),
            "date_published" => new Date("Fecha publicaci�n"),
        );
        parent::__construct($db, $iniciar);
    }
}
?>