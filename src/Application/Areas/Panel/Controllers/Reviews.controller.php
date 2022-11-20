<?php
class ReviewsController extends BaseController
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
     * Carga la vista del indice
	 * @author alca259
	 * @version OK
	 */
	public function Index()
    {
        if (Security::IsAuthorizedAdmin())
        {
            $this->ViewBag->CurrentMenu = "Reviews";
            $this->ViewBag->Title = "Comentarios";
            return new View(__FUNCTION__, $this->controllerName, $this->ViewBag, Constants::$PanelAreaName, true);
		}
        else
        {
			return parent::RedirectToAction("401");
		}
	}
    #endregion

	#region Action Ajax
    
    #region Read actions
    /**
     * Devuelve una lista de comentarios registradas en el sistema
     * @author alca259
     * @version OK
     */
	public function Reviews_Read()
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
            $bItems = array();
            $result["data"] = array(
                "rows" => array(),
                "totalCount" => 0
            );

            // Realizamos la busqueda
            $sItems = $this->reviewPostModel->Search($_SESSION['GUID'], array());

            // Obtencion de datos
            if (!empty($sItems))
            {
                $bItems = $this->reviewPostModel->Browse($_SESSION['GUID'], $sItems, "date_published DESC");
            }

            // Recorremos todos los campos
            foreach ($bItems as $item)
            {
                $itemData = array(
                    "ReviewId" => $item['id'],
                    "ReviewPublishedDate" => date("d/m/Y H:i:s", strtotime($item['date_published'])),
                    "ReviewComment" => utf8_encode($item['comments']),
                    "ReviewScore" => $item['score'],
                    "ReviewUser" => "Unknown",
                );
                
                // Search user name
                $sUser = $this->accountModel->Search($_SESSION['GUID'], array(
                    array("id", "=", $item['user_id']),
                ));
                
                if (!empty($sUser))
                {
                    $bUser = $this->accountModel->Browse($_SESSION['GUID'], $sUser);
                    $itemData["ReviewUser"] = $bUser[0]["name"];
                }

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
    #endregion
    
    #region Create/Update/Delete
	/**
     * Elimina un comentario
	 * @author alca259
	 * @version OK
	 */
	public function DeleteReview()
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
                throw new Exception("Unathorized user");
            }
            
            if (!isset($json_data)
                || !isset($json_data["Id"]))
            {
                throw new Exception("No data found");
            }

			// Vars
			$Id = $json_data["Id"];

			// Creamos la busqueda de validacion
			$domain_model = array(array("id", "=", $Id));

			// Buscamos aquellos emails que coincidan
			$sModel = $this->reviewPostModel->Search($_SESSION['GUID'], $domain_model);

			// Si lo encontramos, lo borramos
			if (!empty($sModel))
            {
				$this->reviewPostModel->Unlink($_SESSION['GUID'], $sModel);
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
    #endregion
    
    #endregion
}