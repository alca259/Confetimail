<?php

class UsersController extends BaseController
{
    #region Variables privadas
    private $controllerName;
    #endregion

    #region Constructors
    public function __construct()
    {
        // Llamamos al constructor padre
        parent::__construct();
        $this->controllerName = str_replace("Controller", "", __CLASS__);
    }
    #endregion

    #region Action controllers
    /**
     * Carga la pantalla principal de usuarios
     * @author alca259
     * @version OK
     */
    public function Index()
    {
        if (Security::IsAuthorizedAdmin())
        {
            $this->ViewBag->CurrentMenu = "Users";
            $this->ViewBag->Title = "Usuarios";
            return new View(__FUNCTION__, $this->controllerName, $this->ViewBag, Constants::$PanelAreaName, true);
        }
        else
        {
            return parent::RedirectToAction("401");
        }
    }
    #endregion

    #region Action Ajax
    /**
     * Funcion que carga los usuarios en un grid
     * @author alca259
     * @version OK
     */
    public function Users_Read()
    {
        $result = array("success" => false, "data" => array(), "message" => "");
        
        try
        {
            $ajaxCall = Security::VerifyAjax($_SERVER['REQUEST_METHOD']);

            if (!Security::IsAuthorizedAdmin() || $ajaxCall != 1)
            {
                throw new Exception("Unathorized access");
            }

            // Vars
            $bUsers = array();
            $result["data"] = array(
                "rows" => array(),
                "totalCount" => 0
            );

            // Realizamos la busqueda
            $sUsers = $this->accountModel->Search($_SESSION['GUID'], array());

            // Obtencion de datos
            if (!empty($sUsers))
            {
                $bUsers = $this->accountModel->Browse($_SESSION['GUID'], $sUsers, "create_date DESC, name ASC");
            }

            // Recorremos todos los campos
            foreach ($bUsers as $user)
            {
                $itemData = array(
                    "UserId" => $user['id'],
                    "CreateDate" => date("d/m/Y", strtotime($user['create_date'])),
                    "UserDisplayName" => utf8_encode($user['name']),
                    "UserName" => utf8_encode($user['username']),
                    "UserEmail" => utf8_encode($user['email']),
                    "UserIsSubscribed" => ($user['subscribed'] == 1) ? true : false,
                    "UserIsActive" => ($user['active'] == 1) ? true : false,
                    "UserIsAdmin" => ($user['admin'] == 1) ? true : false
                );

                $result["data"]["rows"][] = $itemData;
                $result["data"]["totalCount"]++;
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
     * Función que registra, actualiza y elimina a un usuario manualmente
     * @author alca259
     * @version OK
     */
    public function CUD_Users()
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
            
            if (!isset($json_data) || !isset($json_data["Action"]))
            {
                throw new ORMException("No data required found");
            }
            
            $itemData = array(
                "name" => $json_data["UserDisplayName"],
                "username" => $json_data["UserName"],
                "email" => $json_data["UserEmail"],
                "subscribed" => $json_data["UserIsSubscribed"],
                "active" => $json_data["UserIsActive"],
                "admin" => $json_data["UserIsAdmin"],
            );

            switch ($json_data["Action"])
            {
                case "create":
                    $json_data["UserId"] = $this->accountModel->Create($_SESSION['GUID'], $itemData);
                    break;
                case "update":
                    $sUser = $this->accountModel->Search($_SESSION['GUID'], array(array("id", "=", $json_data["UserId"])));
                    if (!empty($sUser))
                    {
                        $this->accountModel->Write($_SESSION['GUID'], $sUser, $itemData);
                    }
                    break;
                case "delete":
                    $sUser = $this->accountModel->Search($_SESSION['GUID'], array(array("id", "=", $json_data["UserId"])));
                    if (!empty($sUser))
                    {
                        $this->accountModel->Unlink($_SESSION['GUID'], $sUser);
                    }
                    break;
            }

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
    
    /**
     * Función que devuelve los datos en modo combobox
     * @author alca259
     * @version OK
     */
    public function GetComboboxUsers()
    {
        $result = array("success" => false, "data" => array(), "message" => "");
        
        try
        {
            $ajaxCall = Security::VerifyAjax($_SERVER['REQUEST_METHOD']);

            if (!Security::IsAuthorizedAdmin() || $ajaxCall != 1)
            {
                throw new Exception("Unathorized access");
            }
                       
            // Realizamos la busqueda
            $sUsers = $this->accountModel->Search($_SESSION['GUID'], array());

            // Obtencion de datos
            if (!empty($sUsers))
            {
                $bUsers = $this->accountModel->Browse($_SESSION['GUID'], $sUsers, "name ASC");

                // Recorremos todos los campos
                foreach ($bUsers as $user)
                {
                    $itemData = array(
                        "UserId" => $user['id'],
                        "UserName" => utf8_encode($user['name']." <".$user['email'].">"),
                    );

                    $result["data"][] = $itemData;
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
     * Resetea la contraseña de un usuario y la devuelve al administrador
     * @author alca259
     * @version OK
     */
    public function ResetPassword_User()
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
                throw new Exception(T_("Unathorized.Access"));
            }
            
            if (!isset($json_data) 
                || !isset($json_data["UserId"]))
            {
                throw new Exception(T_("No.Data.Found"));
            }

            $newPassword = parent::GetRandomPassword();
            
            $user_id = $json_data["UserId"];
            
            // Cargamos los datos de usuario
            $bUser = $this->accountModel->Browse($_SESSION['GUID'], array($user_id));
            $userName = "Undefined";

            if (empty($bUser))
            {
                throw new ORMException(T_("No.Data.Found"));
            }
            else
            {
                $userName = $bUser[0]["name"];
            }
            
            // Rellenamos los datos faltantes
            $data = array();
            $data['password_salt'] = StringUtil::RandString(200);
            $data['password'] = $this->CryptPassword($newPassword, $data['password_salt']);

            // Modificamos en DB los datos del usuario
            $this->accountModel->Write($_SESSION['GUID'], array($user_id), $data);

            $result['message'] = sprintf(T_("The new password for user %s is [<b>%s</b>]"), $userName, $newPassword);
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
}
