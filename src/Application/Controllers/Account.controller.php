<?php

class AccountController extends BaseController 
{
    #region Variables privadas
    private $controllerName;
    #endregion

    #region Constructor
    public function __construct() 
    {
        // Llamamos al constructor padre
        parent::__construct();
        $this->controllerName = str_replace("Controller", "", __CLASS__);
    }
    #endregion

    #region Controller Actions
    /**
     * Carga la vista de perfil
     * @author alca259
     * @version OK
     */
    public function Index()
    {
        #region Pestaña confetis
        // Buscamos la fecha de registro del usuario
        $currentUser = $this->accountModel->Browse($_SESSION['GUID'], array($_SESSION['GUID']));
        
        // Cargamos las temática accesibles
        $mailIds = $this->mailModel->Search($_SESSION['GUID'], array(
            array("is_confeti", "=", 1),
            array("active", "=", 1),
            array("date_send", ">=", $currentUser[0]["create_date"])
        ), "date_send DESC");
        
        if (!empty($mailIds))
        {
            // DROGAS
            $secondMailId = $mailIds[count($mailIds) -1];
            $secondMail = $this->mailModel->Browse($_SESSION['GUID'], array($secondMailId)); 
            $firstMail = $this->mailModel->Search($_SESSION['GUID'], array(
                array("is_confeti", "=", 1),
                array("active", "=", 1),
                array("date_send", "<", $secondMail[0]["date_send"]),
            ), "date_send DESC", "1");
            
            if (!empty($firstMail))
            {
                array_push($mailIds, $firstMail[0]);
            }
            
            $mails = $this->mailModel->Browse($_SESSION['GUID'], $mailIds, "date_send DESC");
            
            // Por cada uno, vamos a recuperar la url del paquete
            for ($idx = 0; $idx < count($mails); $idx++)
            {
                $mails[$idx]['file_zip_url'] = "#";
                
                // Buscamos todos los ficheros que aparecen en el mail
                $sFiles = $this->mailFileModel->Search($_SESSION['GUID'], array(
                    array("mail_id", "=", $mails[$idx]['id']),
                ));
                
                if (empty($sFiles)) continue;
                
                $bFiles = $this->mailFileModel->Browse($_SESSION['GUID'], $sFiles);
                
                // Por cada fichero encontrado, buscamos el que sea un comprimido
                foreach ($bFiles as $findFile)
                {
                    $bFile = $this->fileModel->Browse($_SESSION['GUID'], array($findFile["file_id"]));
                    
                    // Si no es un comprimido, descartamos
                    if ($bFile[0]['file_type'] != 'zip') continue;
                    
                    // Si lo hemos encontrado, lo añadimos como propiedad
                    $mails[$idx]['file_zip_url'] = substr($bFile[0]['full_url'], 1);
                }
            }
            
            $this->ViewBag->Mails = $mails;
        }
        #endregion
        
        #region Pestaña encuestas
        // Recuperamos todas las encuentas para el usuario
        $surveySearch = $this->surveyAccountModel->Search($_SESSION['GUID'], array(
            array("user_id", "=", $_SESSION['GUID']),
        ));
        
        // Si no tiene, le creamos una linea
        if (empty($surveySearch))
        {
            $surveySearch = $this->surveyAccountModel->Create($_SESSION['GUID'], array(
                "user_id" => $_SESSION['GUID']
            ));
        }

        $surveyFields = $this->surveyAccountModel->GetSurveyFields();
        #endregion

        $this->ViewBag->Surveys = $surveyFields;
        $this->ViewBag->CurrentMenu = "Account";
        $this->ViewBag->Title = T_("My.Account");
        return new View(__FUNCTION__, $this->controllerName, $this->ViewBag);
    }

    /**
     * Desloguea a un usuario y destruye la sesión
     * @author alca259
     * @version OK
     */
    public function Logout()
    {
        if (Security::IsOnline()) 
        {
            Security::LogoutUser();
        }

        return parent::RedirectToAction();
    }

    /**
     * Autentica a un usuario y reencripta su contraseña cada vez
     * @author alca259
     * @version OK
     */
    public function Login() 
    {
        if (Security::IsOnline())
        {
            return parent::RedirectToAction();
        }
        
        $ajaxCall = Security::VerifyAjax($_SERVER['REQUEST_METHOD']);

        if ($ajaxCall != 1)
        {
            // Cargamos la vista
            $this->ViewBag->CurrentMenu = "Login";
            $this->ViewBag->Title = T_("Start.Session");
            return new View(__FUNCTION__, $this->controllerName, $this->ViewBag);
        }
        
        // Aqui hay datos enviados con POST, comprobamos que hayamos recibido todo
        if (!isset($_POST['email']) || strlen($_POST['email']) <= 0 
            || !isset($_POST['subPassword']) || strlen($_POST['subPassword']) <= 0)
        {
            // Error, faltan datos
            $this->ViewBag->ErrorMessage = T_("No.Data.Found");
            $this->ViewBag->CurrentMenu = "Login";
            $this->ViewBag->Title = T_("Start.Session");
            return new View(__FUNCTION__, $this->controllerName, $this->ViewBag);
        }
        
        $result = $this->DoLogin($_POST['email'], $_POST['subPassword']);
        if (!$result["success"])
        {
            // Error, el error que sea
            $this->ViewBag->ErrorMessage = $result['message'];
            $this->ViewBag->CurrentMenu = "Login";
            $this->ViewBag->Title = T_("Start.Session");
            return new View(__FUNCTION__, $this->controllerName, $this->ViewBag);
        }
        
        // Correcto
        return parent::RedirectToAction();
    }

    /**
     * Registra a un usuario en el sistema y le envia el ultimo confeti publicado
     * @author alca259
     * @version OK
     */
    public function Subscribe() 
    {
        $this->ViewBag->CurrentMenu = "Subscribe";
        $this->ViewBag->Title = T_("Subscribe");
        return new View(__FUNCTION__, $this->controllerName, $this->ViewBag);
    }
    #endregion

    #region Ajax Actions
    public function Read_Profile()
    {
        $result = array("success" => false, "data" => array(), "message" => "");
        
        try
        {
            $ajaxCall = Security::VerifyAjax($_SERVER['REQUEST_METHOD']);

            if ($ajaxCall != 1)
            {
                throw new Exception(T_("Unathorized.Access"));
            }

            // Cargamos los datos de usuario
            $currentUser = $this->accountModel->Browse($_SESSION['GUID'], array($_SESSION['GUID']));
            
            // Recuperamos todas las encuentas para el usuario
            $surveySearch = $this->surveyAccountModel->Search($_SESSION['GUID'], array(
                array("user_id", "=", $_SESSION['GUID']),
            ));
            
            // Si no tiene, lanzamos excepcion
            if (empty($surveySearch) || empty($currentUser))
            {
                throw new Exception(T_("No.Data.Found"));
            }
            
            $fechaSuscripcion = new DateTime($currentUser[0]["create_date"]);
            
            // Cargamos los datos de usuario en la variable de datos
            $data = array(
                "Name" => $currentUser[0]["name"],
                "Username" => $currentUser[0]["username"],
                "Email" => $currentUser[0]["email"],
                "WebUrl" => $currentUser[0]["web_url"],
                "WebName" => $currentUser[0]["web_name"],
                "CreateDate" => $fechaSuscripcion->format('d/m/Y H:i'),
                "Subscribed" => $currentUser[0]["subscribed"],
                "ProfileUrl" => strlen($currentUser[0]["image_profile"]) > 0 ? $currentUser[0]["image_profile"] : "/Public/img/layout/placeholder-empty.png",
            );

            $surveyFields = $this->surveyAccountModel->GetSurveyFields();
            $surveyBrowse = $this->surveyAccountModel->Browse($_SESSION['GUID'], $surveySearch);

            // Cargamos los valores que ya tenia el usuario guardados en DB
            foreach ($surveyFields as $key1 => $field)
            {
                foreach ($surveyBrowse[0] as $key2 => $valueDb)
                {
                    if ($field['field_name'] != $key2) continue;
                    $data[$field['field_name']] = intval($valueDb);
                }
            }
            
            $result["data"] = $data;
            $result["success"] = true;
        }
        catch (ORMException $ex)
        {
            // Errors found, return json error
            $result["message"] = parent::ErrorJson($ex);
        }
        catch (Exception $ex)
        {
            // Errors found, return json error
            $result["message"] = parent::ErrorJson($ex);
        }
        
        echo JsonHandler::Encode($result);
    }
    
    public function Change_Password()
    {
        $result = array("success" => false, "data" => array(), "message" => "");
        
        try
        {
            $ajaxCall = Security::VerifyAjax($_SERVER['REQUEST_METHOD']);
            $request = file_get_contents('php://input');

            if ($request)
            {
                $json_data = JsonHandler::NormalDecode($request, true);
            }

            if ($ajaxCall != 1)
            {
                throw new Exception(T_("Unathorized.Access"));
            }
            
            if (!isset($json_data)
                || !isset($json_data["OldPassword"])
                || !isset($json_data["NewPassword"])
                || !isset($json_data["RetypePassword"]))
            {
                throw new ORMException(T_("No.Data.Found"));
            }
            
            // Cargamos los datos de usuario
            $currentUser = $this->accountModel->Browse($_SESSION['GUID'], array($_SESSION['GUID']));

            // Comprobamos que la contraseña antigua sea correcta
            $valuePassword = parent::CryptPassword($json_data["OldPassword"], $currentUser[0]['password_salt']);

            // Comparamos las contraseñas
            if ($currentUser[0]['password'] != $valuePassword)
            {
                throw new Exception(T_("User.Invalid.Credentials"));
            }
            
            // Comprobamos que sean iguales
            if ($json_data["NewPassword"] != $json_data["RetypePassword"])
            {
                throw new Exception(T_("Passwords.Not.Match"));
            }
            
            // Rellenamos los datos faltantes
            $data = array();
            $data['password_salt'] = StringUtil::RandString(200);
            $data['password'] = parent::CryptPassword($json_data["NewPassword"], $data['password_salt']);

            // Modificamos en DB los datos del usuario
            $this->accountModel->Write($_SESSION['GUID'], array($_SESSION['GUID']), $data);

            $result["data"] = $json_data;
            $result["success"] = true;
        }
        catch (ORMException $ex)
        {
            // Errors found, return json error
            $result["message"] = parent::ErrorJson($ex);
        }
        catch (Exception $ex)
        {
            // Errors found, return json error
            $result["message"] = parent::ErrorJson($ex);
        }
        
        echo JsonHandler::Encode($result);
    }
    
    public function Save_Profile()
    {
        $result = array("success" => false, "data" => array(), "message" => "");
        
        try
        {
            $ajaxCall = Security::VerifyAjax($_SERVER['REQUEST_METHOD']);
            $request = file_get_contents('php://input');

            if ($request)
            {
                $json_data = JsonHandler::NormalDecode($request, true);
            }

            if ($ajaxCall != 1)
            {
                throw new Exception(T_("Unathorized.Access"));
            }
            
            if (!isset($json_data))
            {
                throw new ORMException(T_("No.Data.Found"));
            }
            
            // Recuperamos todas las encuentas para el usuario
            $surveySearch = $this->surveyAccountModel->Search($_SESSION['GUID'], array(
                array("user_id", "=", $_SESSION['GUID']),
            ));
            
            // Si no tiene, lanzamos excepcion
            if (empty($surveySearch))
            {
                throw new Exception(T_("No.Data.Found"));
            }
            
            // Rellenamos los datos faltantes
            $data = array();
            
            if (isset($json_data["Name"]) && strlen($json_data['Name']) > 0) { $data['name'] = $json_data["Name"]; }
            if (isset($json_data["WebUrl"]) && strlen($json_data['WebUrl']) > 0) { $data['web_url'] = $json_data["WebUrl"]; }
            if (isset($json_data["WebName"]) && strlen($json_data['WebName']) > 0) { $data['web_name'] = $json_data["WebName"]; }
            $data['subscribed'] = isset($json_data["Subscribed"]) && $json_data["Subscribed"] == "true" ? true : false;

            // Modificamos en DB los datos del usuario
            $this->accountModel->Write($_SESSION['GUID'], array($_SESSION['GUID']), $data);
            
            // Recorremos las suscripciones
            $surveyFields = $this->surveyAccountModel->GetSurveyFields();

            $data_surveys = array();
            // Recorremos todos los campos de la encuesta
            foreach ($surveyFields as $key1 => $field)
            {
                 $newValue = isset($json_data[$field['field_name']]) && $json_data[$field['field_name']];
                 $data_surveys[$field['field_name']] = $newValue;
            }
            
            // Guardamos las encuestas
            $this->surveyAccountModel->Write($_SESSION['GUID'], $surveySearch, $data_surveys);

            $result["data"] = $json_data;
            $result["success"] = true;
        }
        catch (ORMException $ex)
        {
            // Errors found, return json error
            $result["message"] = parent::ErrorJson($ex);
        }
        catch (Exception $ex)
        {
            // Errors found, return json error
            $result["message"] = parent::ErrorJson($ex);
        }
        
        echo JsonHandler::Encode($result);
    }
    
    public function Upload_File()
    {
        $result = array("success" => false, "message" => "");
        
        try
        {
            $ajaxCall = Security::VerifyAjax($_SERVER['REQUEST_METHOD']);

            if ($ajaxCall != 1)
            {
                throw new Exception(T_("Unathorized.Access"));
            }
            
            if (!isset($_FILES['image_avatar']))
            {
                throw new ORMException(T_("No.Data.Found"));
            }

            $UploadDirectory = Constants::$UploadUserImagesUrl;
            
            // Si no existe el directorio lo creamos
            if (!file_exists($UploadDirectory))
            {
                mkdir($UploadDirectory, 0755, true);
            }
            
            #region ########### Server Validation ###########
            // Is file size is less than allowed size.
            if ($_FILES['image_avatar']["size"] > 2097152) // 2MB
            {
                throw new Exception("File size is too big (".($_FILES['image_avatar']["size"]/1048576)."MB)");
            }

            $fType = $_FILES['image_avatar']['type'];
            if ($fType == "" || $fType == "application/octet-stream")
            {
                $fName = $_FILES['image_avatar']['name'];
                $fType = strlen($fName) > 4 ? "ext/".substr($fName, strlen($fName) - 4) : "";
            }

            // Allowed file type Server side check
            switch(strtolower($fType))
            {
                //allowed file types
                case 'image/png':
                case 'ext/png':
                case 'image/jpeg':
                case 'ext/jpg':
                case 'ext/jpeg':
                    break;
                default:
                    // Unsupported File!
                    throw new Exception("Extension not allowed (".$_FILES['image_avatar']["type"].")");
            }
            #endregion

            #region ########### Upload file ###########
            // No hay errores
            $File_Name          = strtolower($_FILES['image_avatar']['name']);
            $File_Ext           = substr($File_Name, strrpos($File_Name, '.')); //get file extention
            $NewFileName        = "user_image_".$_SESSION['GUID'].$File_Ext; //new file name

            // Subimos el fichero
            if(!move_uploaded_file($_FILES['image_avatar']['tmp_name'], $UploadDirectory.$NewFileName))
            {
                throw new Exception("No se puede subir el fichero a la ruta ".$UploadDirectory.". Error desconocido.");
            }
            #endregion
            
            // Rellenamos los datos
            $data = array(
                "image_profile" => sprintf("%s%s", substr($UploadDirectory, 1), $NewFileName),
            );

            // Modificamos en DB los datos del usuario
            $this->accountModel->Write($_SESSION['GUID'], array($_SESSION['GUID']), $data);

            $result["success"] = true;
        }
        catch (ORMException $ex)
        {
            // Errors found, return json error
            $result["message"] = parent::ErrorJson($ex);
        }
        catch (Exception $ex)
        {
            // Errors found, return json error
            $result["message"] = parent::ErrorJson($ex);
        }
        
        echo JsonHandler::Encode($result);
    }
    
    public function Subscribe_Save()
    {
        $result = array("success" => false, "data" => array(), "message" => "");
        
        try
        {
            $ajaxCall = Security::VerifyAjax($_SERVER['REQUEST_METHOD']);
            $request = file_get_contents('php://input');

            if ($request)
            {
                $json_data = JsonHandler::NormalDecode($request, true);
            }

            if ($ajaxCall != 1)
            {
                throw new Exception(T_("Unathorized.Access"));
            }
            
            if (!isset($json_data))
            {
                throw new ORMException(T_("No.Data.Found"));
            }
                       
            // Fill vars form
            $valuePassword = $json_data["Password"];
            
            $itemData = array(
                "name" => $json_data["Name"],
                "email" => $json_data["Email"],
                "password" => $json_data["Password"]
            );

            // Comprobamos que sean iguales
            if ($json_data["Password"] != $json_data["VerifyPassword"])
            {
                throw new Exception(T_("Passwords.Not.Match"));
            }

            // Action vars
            $data = array();

            // Comprobamos si hay algun campo obligatorio
            foreach ($itemData as $key => $value)
            {
                if (array_key_exists($key, $this->accountModel->columns))
                {
                    if ($this->accountModel->columns[$key]->getRequired() && empty($value))
                    {
                        throw new Exception(sprintf(T_("Field.X.Required"), $this->accountModel->columns[$key]->getTitle()));
                    }
                    else
                    {
                        $data[$key] = $value;
                    }
                }
            }

            // Comprobamos si el correo ya esta suscrito
            $domain = array ( 
                array("email", "=", $data['email']),
                // No tiene sentido filtrar por si está suscrito o no,
                // el usuario puede cambiar esta opción en su panel
                // array("subscribed", "=", true)
            );

            $results_search = $this->accountModel->Search(ROOT_USER, $domain);

            if (!empty($results_search))
            {
                throw new Exception(sprintf(T_("Mail.Exists"), $data['email']));
            }
            
            // Rellenamos los datos faltantes
            $data['username'] = strtolower($data['email']);
            $data['password_salt'] = StringUtil::RandString(200);
            $data['password'] = parent::CryptPassword($valuePassword, $data['password_salt']);
            $data['subscribed'] = true;
            $data['active'] = true;

            // Creamos el usuario y obtenemos su ID
            $user_id = $this->accountModel->Create(ROOT_USER, $data);
            $_SESSION['GUID'] = ROOT_USER;

            // Mandamos el correo de confirmacion
            $mensaje = sprintf(T_("Mail.New.Register.Body"), $data['name'], $data['email']);
            $email = new Mailer('info@confetimail.net', T_("Mail.New.Register.Subject"), $mensaje);
            // Recojemos el resultado del envio
            $statusMail = $email->Send();

            $result["message"] = $statusMail 
                ? T_("Mail.Subscribe.Success")
                : T_("Mail.Subscribe.Success.Warning");

            // No se filtra por fechas, porque en el caso de que no se publique un confeti en dos meses
            // el usuario no recibe nada
            // $original = new DateTime('NOW');
            // $previous = DateTime::createFromFormat('U',
            //      strtotime('first day of last month', ($original->format('U'))),
            //      new DateTimeZone('UTC'));

            // Intentamos enviarle el último correo marcado como enviable
            // Creamos la busqueda de validacion
            $domain_model = array(
                array("active", "=", 1),
                array("is_confeti", "=", 1),
            //    array("date_send", ">=", $previous->format('Y-m-d'))
            );

            // Buscamos aquellos emails que coincidan
            $sModel = $this->mailModel->Search($_SESSION['GUID'], $domain_model, "date_send DESC");

            // Si lo encontramos, lo obtenemos
            if (!empty($sModel))
            {
                $items = $this->mailModel->Browse($_SESSION['GUID'], array($sModel[0]));
                parent::SendConfetiMailToUser($sModel[0], $user_id, $items);
            }

            $_SESSION['GUID'] = null;

            $result["data"] = $json_data;
            $result["success"] = true;
        }
        catch (ORMException $ex)
        {
            // Errors found, return json error
            $result["message"] = parent::ErrorJson($ex);
        }
        catch (Exception $ex)
        {
            // Errors found, return json error
            $result["message"] = parent::ErrorJson($ex);
        }
        
        echo JsonHandler::Encode($result);
    }
    #endregion
    
    #region Private methods
    /**
     * Realiza un login con el email y contraseña proporcionados
     * @param string $email Email del usuario
     * @param string $password Contraseña del usuario
     * @throws Exception 
     * @return array (success(bool) y message(string))
     */
    private function DoLogin($email, $password)
    {
        $result = array("success" => false, "message" => "");

        try
        {
            // Action vars
            $itemData = array(
                "email" => $email,
                "password" => $password
            );
            
            $valuePassword = $itemData["password"];

            $data = array();
            
            // Comprobamos si hay algun campo obligatorio
            foreach ($itemData as $key => $value)
            {
                if (array_key_exists($key, $this->accountModel->columns))
                {
                    if ($this->accountModel->columns[$key]->getRequired() && empty($value))
                    {
                        throw new Exception(sprintf(T_("Field.X.Required"), $this->accountModel->columns[$key]->getTitle()));
                    }
                    else
                    {
                        $data[$key] = $value;
                    }
                }
            }

            // Creamos el filtro para buscar al usuario
            $domain_user = array (
                array("username", "=", $data['email']),
                array("active", "=", true)
            );

            $sUser = $this->accountModel->Search(ROOT_USER, $domain_user);
            
            if (empty($sUser))
            {
                throw new Exception(sprintf(T_("User.Not.Exists.Or.Not.Active"), $data['email']));
            }

            // El usuario existe, buscamos sus datos
            $bUser = $this->accountModel->Browse(ROOT_USER, $sUser);

            // Obtenemos el primero
            $bUser = $bUser[0];

            // Preparamos la contraseña enviada para la validacion
            $valuePassword = parent::CryptPassword($valuePassword, $bUser['password_salt']);

            // Comparamos las contraseñas
            if ($bUser['password'] != $valuePassword)
            {
                throw new Exception(T_("User.Invalid.Credentials"));
            }
            
            // Login correcto, generamos un nuevo salt y volvemos a encriptar la contraseña
            $data['password_salt'] = StringUtil::RandString(200);
            $data['password'] = parent::CryptPassword($data['password'], $data['password_salt']);
            
            // Actualizamos el ultimo login y capturamos el ultimo en una variable de sesion
            $_SESSION['LAST_LOGIN'] = $bUser["last_login"] != null ? $bUser["last_login"] : date("Y-m-d");
            $data['last_login'] = date("Y-m-d H:i:s");

            // Actualizamos los datos
            $this->accountModel->Write(ROOT_USER, $sUser, $data);

            // Comprobamos si se han producido errores
            // Generamos la sesion
            Security::LoginUser($bUser);
            
            $result["success"] = true;
        }
        catch (ORMException $ex)
        {
            // Errors found, return json error
            $result["message"] = parent::ErrorJson($ex);
        }
        catch (Exception $ex)
        {
            // Errors found, return json error
            $result["message"] = parent::ErrorJson($ex);
        }
        
        return $result;
    }
    #endregion
}