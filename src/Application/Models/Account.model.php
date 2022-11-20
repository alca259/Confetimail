<?php

class AccountModel extends ModelBase {

	public function __construct($db, $iniciar = false) {
		$this->name = "account";
		$this->table = "res_users";
		$this->columns = array (
			"name" => new Char("Nombre", 64, true),
			"username" => new Char("Nombre de usuario", 64, true),
			"password" => new Char("Contraseña", 256, true),
			"password_salt" => new Char("Salt", 256, false),
			"email" => new Char("Email", 256, true),
			"subscribed" => new Boolean("Suscrito"),
			"active" => new Boolean("Activo"),
			"admin" => new Char("Admin", 1),
            "last_login" => new Fechahora("Last login"),
            "web_url" => new Char("URL", 256),
            "web_name" => new Char("URL Nombre", 256),
            "image_profile" => new Char("Url imagen", 512),
		);
		parent::__construct($db, $iniciar);
	}
}

?>