<?php

/**
 * Class Account
 * Please note:
 * Don't use the same name for class and method, as this might trigger an (unintended) __construct of the class.
 * This is really weird behaviour, but documented here: http://php.net/manual/en/language.oop5.decon.php
 */
class SecurityController extends BaseController
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

	#region Action controllers
    /**
     * Carga la vista principal de modelos
     * @author alca259
     * @version OK
     */
	public function Index()
    {
		if (Security::IsAuthorizedAdmin())
        {
            $this->ViewBag->CurrentMenu = "Security";
            $this->ViewBag->Title = "Seguridad";
            return new View(__FUNCTION__, $this->controllerName, $this->ViewBag, Constants::$PanelAreaName, true);
		}
        else
        {
			return parent::RedirectToAction("401");
		}
	}
    #endregion

	#region Action Ajax
    
    #region Permisos de usuario
    /**
     * Lee los permisos de un usuario
     * @author alca259
     * @version OK
     */
    public function Permissions_Read()
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

            if (!Security::IsAuthorizedAdmin() || $ajaxCall != 1)
            {
                throw new Exception("Unathorized access");
            }
            
            $result["data"] = array(array(
                'id' =>'root',
                'text' => 'Modelos',
                'expanded' => true,
                'spriteCssClass' => "rootfolder",
                'items' => array()
            ));

            // Obtenemos todos los modelos
            $sModel = $this->irModelModel->Search($_SESSION['GUID'], array(
                array("active", "=", 1),
            ));
            $bModel = $this->irModelModel->Browse($_SESSION['GUID'], $sModel);

            if (isset($json_data) && isset($json_data["UserId"]))
            {
                $userId = $json_data["UserId"];
            
                // Para cada uno de los modelos
                foreach ($bModel as $model)
                {
                    // Buscamos los permisos para el usuario y modelo actual
                    $sAccess = $this->irModelAccessModel->Search($_SESSION['GUID'], array(
                        array('user_id', '=', $userId),
                        array('model_id', '=', $model['id'])
                    ));

                    if (empty($sAccess))
                    {
                        // Si no existe, lo metemos en la lista como vacio
                        $permisos = array(
                            array('id' => 'perm_read', 'text' => 'Lectura', 'spriteCssClass' => 'perm_read'),
                            array('id' => 'perm_write', 'text' => 'Escritura', 'spriteCssClass' => 'perm_write'),
                            array('id' => 'perm_create', 'text' => 'Creación', 'spriteCssClass' => 'perm_create'),
                            array('id' => 'perm_unlink', 'text' => 'Borrado', 'spriteCssClass' => 'perm_unlink')
                        );
                        array_push($result["data"][0]['items'], array(
                            'id' => $model['id'],
                            'text' => $model['name'],
                            'spriteCssClass' => 'folder',
                            'items' => $permisos,
                            'model_id' => $model['id'],
                            'expanded' => false
                        ));
                    }
                    else
                    {
                        // Si existe, lo metemos en la lista con los parametros de la base de datos
                        $bAccess = $this->irModelAccessModel->Browse($_SESSION['GUID'], $sAccess);

                        $cr = $bAccess[0]['perm_read'] == "1";
                        $cw = $bAccess[0]['perm_write'] == "1";
                        $cc = $bAccess[0]['perm_create'] == "1";
                        $cu = $bAccess[0]['perm_unlink'] == "1";
                        $all = false;
                        $any = false;

                        if ($cr && $cw && $cc && $cu)
                        {
                            $all = true;
                        }
                        
                        if ($cr || $cw || $cc || $cu)
                        {
                            $any = true;
                        }

                        $permisos = array(
                            array('id' => 'perm_read', 'text' => 'Lectura', 'spriteCssClass' => 'perm_read', 'checked' => $cr),
                            array('id' => 'perm_write', 'text' => 'Escritura', 'spriteCssClass' => 'perm_write', 'checked' => $cw),
                            array('id' => 'perm_create', 'text' => 'Creación', 'spriteCssClass' => 'perm_create', 'checked' => $cc),
                            array('id' => 'perm_unlink', 'text' => 'Borrado', 'spriteCssClass' => 'perm_unlink', 'checked' => $cu)
                        );

                        array_push($result["data"][0]['items'], array(
                            'id' => $model['id'],
                            'text' => $model['name'],
                            'spriteCssClass' => 'folder',
                            'items' => $permisos,
                            'model_id' => $model['id'],
                            'expanded' => $any && !$all,
                            'checked' => $all
                        ));
                    }
                }
            }

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

    /**
     * Guarda los permisos de un usuario
     * @author alca259
     * @version OK
     */
    function Permissions_Save()
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

            if (!Security::IsAuthorizedAdmin() || $ajaxCall != 1)
            {
                throw new Exception("Unathorized access");
            }
            
            if (!isset($json_data) || !isset($json_data["userId"]))
            {
                throw new ORMException("No data required found");
            }

            $userId = $json_data["userId"];
            
            $accessData = array(
                "perm_read" => false,
                "perm_write" => false,
                "perm_create" => false,
                "perm_unlink" => false,
                "user_id" => $userId,
                "model_id" => false,
                "active" => true,
            );

            // Buscamos todos los accesos a modelos del usuario
            $sAccess = $this->irModelAccessModel->Search($_SESSION['GUID'], array(
                array ("user_id", "=", $userId),
            ));
            
            // Si existen, los establecemos todos a falso
            if (count($sAccess) > 0)
            {
                $this->irModelAccessModel->Write($_SESSION['GUID'], $sAccess, array(
                    'perm_read_anon' => false,
                    'perm_write_anon' => false,
                    'perm_create_anon' => false,
                    'perm_unlink_anon' => false,
                ));
            }
            
            if (isset($json_data["permIds"]) && !empty($json_data["permIds"]))
            {
                // Se han modificado los permisos
                foreach ($json_data["permIds"] as $key_model => $access)
                {
                    // Copiamos el array en cada vuelta
                	$data = $accessData;
                    
                    if (!isset($access["model_id"]) || strlen($access["model_id"]) <= 0)
                    {
                        // Root mode
                        continue;
                    }
                    
                    $data["model_id"] = $access["model_id"];
                    
                    // Buscamos si ya existe uno
                    $sAccess = $this->irModelAccessModel->Search($_SESSION['GUID'], array(
                        array ('model_id', '=', $data['model_id']),
                        array ('user_id', '=', $userId),
                    ));
                    
                    if (isset($access["checked"]) && $access["checked"] == true)
                    {
                        // Full Access
                        $data['perm_read'] = true;
                        $data['perm_write'] = true;
                        $data['perm_create'] = true;
                        $data['perm_unlink'] = true;
                    }
                    else
                    {
                        // Partial access
                        foreach ($access["items"] as $key_item => $item)
                        {
                            switch ($item["index"])
                            {
                            	case 0:
                                    # perm_read
                                    $data['perm_read'] = isset($item['checked']) && $item['checked'];
                                    break;
                                case 1:
                                    # perm_write
                                    $data['perm_write'] = isset($item['checked']) && $item['checked'];
                                    break;
                                case 2:
                                    # perm_create
                                    $data['perm_create'] = isset($item['checked']) && $item['checked'];
                                    break;
                                case 3:
                                    # perm_unlink
                                    $data['perm_unlink'] = isset($item['checked']) && $item['checked'];
                                    break;
                            }
                        }
                    }
                    
                    if (!empty($sAccess))
                    {
                        // Existe, lo modificamos
                        $this->irModelAccessModel->Write($_SESSION['GUID'], $sAccess, array(
                            'perm_read' => $data['perm_read'],
                            'perm_write' => $data['perm_write'],
                            'perm_create' => $data['perm_create'],
                            'perm_unlink' => $data['perm_unlink'],
                        ));
                    }
                    else
                    {
                        // No existe, lo creamos
                        $this->irModelAccessModel->Create($_SESSION['GUID'], $data);
                    }
                }
            }
            
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
    
    #region Permisos generales
    /**
     * Lee los permisos generales
     * @author alca259
     * @version OK
     */
    public function PermissionsGeneral_Read()
    {
        $result = array("success" => false, "data" => array(), "message" => "");
        
        try
        {
            $ajaxCall = Security::VerifyAjax($_SERVER['REQUEST_METHOD']);

            if (!Security::IsAuthorizedAdmin() || $ajaxCall != 1)
            {
                throw new Exception("Unathorized access");
            }
            
            $result["data"] = array(array(
                'id' =>'root',
                'text' => 'Modelos',
                'expanded' => true,
                'spriteCssClass' => "rootfolder",
                'items' => array()
            ));

            // Obtenemos todos los modelos
            $sModel = $this->irModelModel->Search($_SESSION['GUID'], array(
                array("active", "=", 1),
            ));
            $bModel = $this->irModelModel->Browse($_SESSION['GUID'], $sModel);

            $userId = ROOT_USER;

            // Para cada uno de los modelos
            foreach ($bModel as $model)
            {
                // Buscamos los permisos para el usuario y modelo actual
                $sAccess = $this->irModelAccessModel->Search($_SESSION['GUID'], array(
                    array('user_id', '=', $userId),
                    array('model_id', '=', $model['id'])
                ));

                if (empty($sAccess))
                {
                    // Si no existe, lo metemos en la lista como vacio
                    $permisos = array(
                        array('id' => 'perm_read_anon', 'text' => 'Lectura', 'spriteCssClass' => 'perm_read'),
                        array('id' => 'perm_write_anon', 'text' => 'Escritura', 'spriteCssClass' => 'perm_write'),
                        array('id' => 'perm_create_anon', 'text' => 'Creación', 'spriteCssClass' => 'perm_create'),
                        array('id' => 'perm_unlink_anon', 'text' => 'Borrado', 'spriteCssClass' => 'perm_unlink')
                    );
                    array_push($result["data"][0]['items'], array(
                        'id' => $model['id'],
                        'text' => $model['name'],
                        'spriteCssClass' => 'folder',
                        'items' => $permisos,
                        'model_id' => $model['id'],
                        'expanded' => false
                    ));
                }
                else
                {
                    // Si existe, lo metemos en la lista con los parametros de la base de datos
                    $bAccess = $this->irModelAccessModel->Browse($_SESSION['GUID'], $sAccess);

                    $cr = $bAccess[0]['perm_read_anon'] == "1";
                    $cw = $bAccess[0]['perm_write_anon'] == "1";
                    $cc = $bAccess[0]['perm_create_anon'] == "1";
                    $cu = $bAccess[0]['perm_unlink_anon'] == "1";
                    $all = false;
                    $any = false;

                    if ($cr && $cw && $cc && $cu)
                    {
                        $all = true;
                    }
                        
                    if ($cr || $cw || $cc || $cu)
                    {
                        $any = true;
                    }

                    $permisos = array(
                        array('id' => 'perm_read_anon', 'text' => 'Lectura', 'spriteCssClass' => 'perm_read', 'checked' => $cr),
                        array('id' => 'perm_write_anon', 'text' => 'Escritura', 'spriteCssClass' => 'perm_write', 'checked' => $cw),
                        array('id' => 'perm_create_anon', 'text' => 'Creación', 'spriteCssClass' => 'perm_create', 'checked' => $cc),
                        array('id' => 'perm_unlink_anon', 'text' => 'Borrado', 'spriteCssClass' => 'perm_unlink', 'checked' => $cu)
                    );

                    array_push($result["data"][0]['items'], array(
                        'id' => $model['id'],
                        'text' => $model['name'],
                        'spriteCssClass' => 'folder',
                        'items' => $permisos,
                        'model_id' => $model['id'],
                        'expanded' => $any && !$all,
                        'checked' => $all
                    ));
                }
            }

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

    /**
     * Guarda los permisos generales
     * @author alca259
     * @version OK
     */
    function PermissionsGeneral_Save()
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

            if (!Security::IsAuthorizedAdmin() || $ajaxCall != 1)
            {
                throw new Exception("Unathorized access");
            }
            
            if (!isset($json_data))
            {
                throw new ORMException("No data required found");
            }

            $accessData = array(
                "perm_read_anon" => false,
                "perm_write_anon" => false,
                "perm_create_anon" => false,
                "perm_unlink_anon" => false,
                "user_id" => ROOT_USER,
                "model_id" => false,
                "active" => true,
            );

            // Buscamos todos los accesos a modelos del usuario
            $sAccess = $this->irModelAccessModel->Search($_SESSION['GUID'], array(
                array ("user_id", "=", ROOT_USER),
            ));
            
            // Si existen, los establecemos todos a falso
            if (count($sAccess) > 0)
            {
                $this->irModelAccessModel->Write($_SESSION['GUID'], $sAccess, array(
                    'perm_read_anon' => false,
                    'perm_write_anon' => false,
                    'perm_create_anon' => false,
                    'perm_unlink_anon' => false,
                ));
            }
            
            if (isset($json_data["permIds"]) && !empty($json_data["permIds"]))
            {               
                // Se han modificado los permisos
                foreach ($json_data["permIds"] as $key_model => $access)
                {
                    // Copiamos el array en cada vuelta
                	$data = $accessData;
                    
                    if (!isset($access["model_id"]) || strlen($access["model_id"]) <= 0)
                    {
                        // Root mode
                        continue;
                    }
                    
                    $data["model_id"] = $access["model_id"];
                   
                    if (isset($access["checked"]) && $access["checked"] == true)
                    {
                        // Full Access
                        $data['perm_read_anon'] = true;
                        $data['perm_write_anon'] = true;
                        $data['perm_create_anon'] = true;
                        $data['perm_unlink_anon'] = true;
                    }
                    else
                    {
                        // Partial access
                        foreach ($access["items"] as $key_item => $item)
                        {
                            switch ($item["index"])
                            {
                            	case 0:
                                    # perm_read
                                    $data['perm_read_anon'] = isset($item['checked']) && $item['checked'];
                                    break;
                                case 1:
                                    # perm_write
                                    $data['perm_write_anon'] = isset($item['checked']) && $item['checked'];
                                    break;
                                case 2:
                                    # perm_create
                                    $data['perm_create_anon'] = isset($item['checked']) && $item['checked'];
                                    break;
                                case 3:
                                    # perm_unlink
                                    $data['perm_unlink_anon'] = isset($item['checked']) && $item['checked'];
                                    break;
                            }
                        }
                    }
                        
                    // Buscamos si ya existe uno
                    $sAccess = $this->irModelAccessModel->Search($_SESSION['GUID'], array(
                        array ('model_id', '=', $data['model_id']),
                        array ('user_id', '=', ROOT_USER),
                    ));
                    
                    if (!empty($sAccess))
                    {
                        // Existe, lo modificamos
                        $this->irModelAccessModel->Write($_SESSION['GUID'], $sAccess, array(
                            'perm_read_anon' => $data['perm_read_anon'],
                            'perm_write_anon' => $data['perm_write_anon'],
                            'perm_create_anon' => $data['perm_create_anon'],
                            'perm_unlink_anon' => $data['perm_unlink_anon'],
                        ));
                    }
                    else
                    {
                        // No existe, lo creamos
                        $this->irModelAccessModel->Create($_SESSION['GUID'], $data);
                    }
                }
            }
            
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
    
    #endregion
}
