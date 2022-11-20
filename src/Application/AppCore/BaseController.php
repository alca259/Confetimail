<?php

/**
 * This is the "base controller class".
 * All other "real" controllers extend this class.
 */
class BaseController {
	/**
	 *
	 * @var null Database Connection
	 */
	public $db = null;
    public $ViewBag = null;
    
	protected $accountModel;
	protected $fileModel;
    protected $irModelModel;
	protected $irModelAccessModel;
	protected $mailModel;
	protected $mailAccountModel;
	protected $mailFileModel;
    protected $blogPostModel;
    protected $blogPostCommentModel;
    protected $reviewPostModel;
    protected $surveyAccountModel;

	/**
	 * Whenever a controller is created, open a database connection too.
	 * The idea behind is to have ONE connection
	 * that can be used by multiple models (there are frameworks that open one connection per model).
	 */
	function __construct()
    {
		$this->openDatabaseConnection();
        // Dynamic dictionary, it be resolved in execution time
        $this->ViewBag = new Dictionary();
        $this->ViewBag->Title = "Default";
        
        // Inicializamos todos los modelos para tenerlos utilizables desde las clases hijas
		$this->accountModel = $this->LoadModel('Account');
		$this->fileModel = $this->LoadModel('File', Constants::$PanelAreaName);
        $this->irModelModel = $this->LoadModel('Ir_Model', Constants::$PanelAreaName);
		$this->irModelAccessModel = $this->LoadModel('Ir_Model_Access', Constants::$PanelAreaName);
		$this->mailModel = $this->LoadModel('Mail', Constants::$PanelAreaName);
		$this->mailAccountModel = $this->LoadModel('Mail_Account', Constants::$PanelAreaName);
		$this->mailFileModel = $this->LoadModel('Mail_File', Constants::$PanelAreaName);
        $this->blogPostModel = $this->LoadModel('Blog_Post', Constants::$PanelAreaName);
        $this->blogPostCommentModel = $this->LoadModel('Blog_Post_Comment', Constants::$PanelAreaName);
        $this->reviewPostModel = $this->LoadModel('Review_Post', Constants::$PanelAreaName);
        $this->surveyAccountModel = $this->LoadModel('Survey_Account', Constants::$PanelAreaName);
	}

	/**
	 * Open the database connection with the credentials from application/config/config.php
	 */
	private function openDatabaseConnection()
    {
		// set the (optional) options of the PDO connection. in this case, we set the fetch mode to
		// "objects", which means all results will be objects, like this: $result->user_name !
		// For example, fetch mode FETCH_ASSOC would return results like this: $result["user_name] !
		// @see http://www.php.net/manual/en/pdostatement.fetch.php
		$options = array (
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING
		);

		// generate a database connection, using the PDO connector
		// @see http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
		$this->db = new PDO(DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, $options);
	}

	/**
	 * Load the model with the given name.
	 * @param string $model_name (The name of the model)
     * @param string $area_name
     * @param bool $initialize
	 * @return object model
	 */
	public function LoadModel($model_name, $area_name = "", $initialize = false)
    {
		// Agregamos la extension Model para cargar la clase
		$model_name .= "Model";

		// return new model (and pass the database connection to the model)
		return new $model_name($this->db, $initialize);
	}

	public function DropModel($model_name, $area_name = "", $initialize = false)
    {
		// Agregamos la extension Model para cargar la clase
		$model_name .= "Model";

		// Cargamos el modelo
		$model = new $model_name($this->db);

        // Borramos la tabla
		$model->DropTable();
	}
    
    public function RedirectToAction($action_name = "Index", $controller_name = "Home", $area_name = "")
    {
        if ($action_name == "401" || $action_name == "404" || $action_name == "500")
        {
            header(sprintf("Location: /%s.html", $action_name));
            return true;
        }
        
        // If controller is Home, return to main web
        if ($controller_name == "Home")
        {
            header(sprintf("Location: /"));
            return true;
        }
        
        $url = "/";
        if ($area_name != "")
        {
            // If area is setted, use it
            $url = sprintf("%s/%s", $url, $area_name);
        }

        // Add controller name
        $url = sprintf("%s/%s", $url, $controller_name);
        
        if ($action_name == "Index")
        {
            // If action is index, we can return to action
            header(sprintf("Location: %s", $url));
            return true;
        }
        
        // Add action name
        $url = sprintf("%s/%s", $url, $action_name);
        
        // Redirect
        header(sprintf("Location: %s", $url));
        return true;
    }

    public function ErrorJson($exception)
    {
        if (!APPLICATION_DEBUG)
        {
            return $exception->getMessage();
        }
        
        $error = $exception->getMessage();
        $error = sprintf("%s<br/><b>Line:</b> %s", $error, $exception->getLine());
        $error = sprintf("%s<br/><b>File:</b> %s", $error, $exception->getFile());
        $error = sprintf("%s<br/><b>Stacktrace:</b><br/><pre>%s</pre>", $error, $exception->getTraceAsString());
        
        return $error;
    }
    
    /**
     * Envia un mensaje a un usuario y devuelve una cadena de texto si hubiera ocurrido un error
     * @param int $IdMail
     * @param int $user_id
     * @param array $mail_data
     * @param mixed $accountModel
     * @param mixed $mailAccountModel
     * @param bool $registerDbError
     * @return string Mensaje de error
     * @author alca259
     * @version OK
     */
	public function SendConfetiMailToUser($IdMail, $user_id, $mail_data, $registerDbError = false)
    {
		$status = MailStatus::NoEnviado;
		$error_msg = "";

        // Obtenemos la informacion del usuario al que queremos enviar
        $domain_account = array(
            array("id", "=", $user_id),
        );

        $sAccount = $this->accountModel->search($_SESSION['GUID'], $domain_account);

        if (!empty($sAccount))
        {
        	$user_data = $this->accountModel->browse($_SESSION['GUID'], $sAccount);

        	$header_new = str_replace("{DisplayUser}", $user_data[0]["name"], $mail_data[0]["header_for_new"]);
        	$header_old = str_replace("{DisplayUser}", $user_data[0]["name"], $mail_data[0]["header_for_old"]);
        	$mensaje = str_replace("{DisplayUser}", $user_data[0]["name"], $mail_data[0]["body"]);

        	// Logica para saber que cabecera enviar
        	// Comprobamos si al usuario actual, se le ha enviado alguna vez un correo
        	$domain_new_user = array(
        			array("user_id", "=", $user_id),
        	);

        	$sCheckAM = $this->mailAccountModel->search($_SESSION['GUID'], $domain_new_user);

        	if (empty($sCheckAM))
            {
        		// Es un usuario nuevo
        		$fullMessage = $header_new . "<br />" . $mensaje;
        	}
            else
            {
        		$fullMessage = $header_old . "<br />" . $mensaje;
        	}

        	// Intentamos realizar el envio del correo
        	$email = new Mailer($user_data[0]["email"], $mail_data[0]["subject"], $fullMessage, "text/html");

        	// Recojemos el resultado del envio
        	$statusMail = $email->Send();

        	if (!$statusMail)
            {
        		$error_msg .= $email->getMessageMail() . "<br />";
        		$status = MailStatus::ErroresAlEnviar;
        	}
            else
            {
        		$status = MailStatus::EnviadoConExito;
        	}
        } else {
        	// User not found
        	return $error_msg;
        }

        // Si no requiere un registro en DB, devolvemos el mensaje de error, si lo hay
        if (!$registerDbError) 
        {
            return $error_msg;
        }
        
        // Data to save
        $data = array(
        		"user_id" => $user_id,
        		"mail_id" => $IdMail,
        		"date_sent" => date("Y-m-d H:i:s"),
        		"status" => $status,
        );

        // Buscamos si ya existe el registro
        $domain_account_mail = array(
        		array("mail_id", "=", $IdMail),
        		array("user_id", "=", $user_id),
        );

        $sAM = $this->mailAccountModel->search($_SESSION['GUID'], $domain_account_mail);

        if (empty($sAM))
        {
        	// Si no existe, lo creamos
        	$this->mailAccountModel->create($_SESSION['GUID'], $data);
        }
        else
        {
        	// Si existe, lo modificamos
        	$this->mailAccountModel->write($_SESSION['GUID'], $sAM, $data);
        }

		return $error_msg;
	}
    
    /**
     * Genera una contraseña aleatoria
     * @param int $minLength 
     * @param int $maxLength 
     * @param bool $includeNumbers 
     * @param bool $includeAscii 
     * @param bool $includeSpecialChars 
     * @return string
     */
    public function GetRandomPassword($minLength = 8, $maxLength = 16, $includeNumbers = true, $includeAscii = true, $includeSpecialChars = true)
    {
        $numbers = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
        $ascii = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q",
                       "r", "s", "t", "u", "v", "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G", "H",
                       "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
        $specialChars = array("*", "-", "/", "+", ".", "_", ":", ";", "<", ">", "=", ")", "(", "&", "%", "$", "!", "?");
        
        $result = "";
        $passwordLength = rand($minLength, $maxLength);
        $maxCharsType = $passwordLength; // Numero de caracteres maximo por tipo
        
        if ($includeNumbers && $includeAscii && $includeSpecialChars) { $maxCharsType = $passwordLength / 3; }
        else if ($includeNumbers && $includeAscii && !$includeSpecialChars) { $maxCharsType = $passwordLength / 2; }
        else if (!$includeNumbers && $includeAscii && $includeSpecialChars) { $maxCharsType = $passwordLength / 2; }
        else if ($includeNumbers && !$includeAscii && $includeSpecialChars) { $maxCharsType = $passwordLength / 2; }
        
        // Si incluye numeros
        if ($includeNumbers)
        {
            $size = count($numbers);
            for( $i = 0; $i < $maxCharsType; $i++ )
            {
                $result .= $numbers[ rand( 0, $size - 1 ) ];
            }
        }
        
        // Si incluye ascii
        if ($includeAscii)
        {
            $size = count($ascii);
            for( $i = 0; $i < $maxCharsType; $i++ )
            {
                $result .= $ascii[ rand( 0, $size - 1 ) ];
            }
        }
        
        // Si incluye caracteres especiales
        if ($includeSpecialChars)
        {
            $size = count($specialChars);
            for( $i = 0; $i < $maxCharsType; $i++ )
            {
                $result .= $specialChars[ rand( 0, $size - 1 ) ];
            }
        }

        return str_shuffle($result);
    }
    
    /**
     * Encripta una contraseña con un salt
     * @author alca259
     * @version OK
     */
	public function CryptPassword($pass, $salt)
    {
		return sha1(md5(sha1(md5(sha1($pass.$salt)))));
	}
}
