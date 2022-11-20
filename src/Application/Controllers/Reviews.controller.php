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

    #region Controller actions
    /**
     * Carga la pantalla principal
     * @author alca259
     * @version OK
     */
    public function Index()
    {
        $this->ViewBag->CurrentMenu = "Reviews";
        $this->ViewBag->Title = T_("Reviews");
        return new View(__FUNCTION__, $this->controllerName, $this->ViewBag);
    }
    #endregion
    
    #region Ajax actions
    /**
     * Devuelve una lista de entradas registradas en el sistema
     * @author alca259
     * @version OK
     */
    public function Reviews_Read()
    {
        $result = array("success" => false, "data" => array(), "message" => "");
        
        try
        {
            $ajaxCall = Security::VerifyAjax($_SERVER['REQUEST_METHOD']);
            
            if ($ajaxCall != 1)
            {
                throw new Exception(T_("Unathorized.Access"));
            }
            
            $request = file_get_contents('php://input');

            if ($request)
            {
                $json_data = JsonHandler::NormalDecode($request, true);
            }
            
            if (!isset($json_data)
                || !isset($json_data["SelectedPage"])
                || !isset($json_data["MaxResults"]))
            {
                throw new Exception(T_("No.Required.Data.Found"));
            }

            // Vars
            $bItems = array();
            $result["data"] = array(
                "rows" => array(),
                "totalResults" => 0,
                "totalPages" => 1,
                "currentPage" => $json_data["SelectedPage"]
            );
            
            // Dominio de búsqueda
            $domain_search = array();
            
            // Buscamos el conteo total de registros
            $result["data"]["totalResults"] = $this->reviewPostModel->Count(ROOT_USER, $domain_search);
            
            if ($result["data"]["totalResults"] <= 0)
            {
                throw new Exception(T_("No.Data.Found"));
            }
            
            // Calculamos el numero total de paginas dividiendo el total de resultados entre los resultados a mostrar
            $result["data"]["totalPages"] = ceil($result["data"]["totalResults"] / $json_data["MaxResults"]);
            
            // Calculamos a partir de que registro comenzaremos a paginar datos
            // Restamos uno a la pagina actual y lo multiplicamos por el número de registros que queremos visualizar
            // Por último, le sumamos uno a todo porque los registros empiezan en el número siguientes al que queremos ver
            // Esto quedaría así con unos 5 registros máximos:
            // Primera Página: ((1 - 1) * 5) = 0 -- Se visualizarian los registros desde el 0 al 4
            // Segunda Página: ((2 - 1) * 5) = 5 -- Se visualizarian los registros desde el 5 al 9
            
            $startAtRecord = ($result["data"]["currentPage"] - 1) * $json_data["MaxResults"];

            // Realizamos la busqueda
            $sItems = $this->reviewPostModel->Search(ROOT_USER, $domain_search, "date_published DESC", $json_data["MaxResults"], $startAtRecord);

            // Obtencion de datos
            if (!empty($sItems))
            {
                $bItems = $this->reviewPostModel->Browse(ROOT_USER, $sItems, "date_published DESC");
            }

            // Recorremos todos los campos
            foreach ($bItems as $item)
            {
                $result["data"]["rows"][] = $this->PrepareData($item);
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
     * Crea una nueva opinion en el sistema
     * @author alca259
     * @version OK
     */
    public function Review_Create()
    {
        $result = array("success" => false, "data" => array(), "message" => "");
        
        try
        {
            $ajaxCall = Security::VerifyAjax($_SERVER['REQUEST_METHOD']);
            
            if ($ajaxCall != 1)
            {
                throw new Exception(T_("Unathorized.Access"));
            }
            
            $request = file_get_contents('php://input');

            if ($request)
            {
                $json_data = JsonHandler::NormalDecode($request, true);
            }
            
            if (!isset($json_data)
                || !isset($json_data["Comment"])
                || !isset($json_data["Score"]))
            {
                throw new Exception(T_("No.Required.Data.Found"));
            }

            // Guardamos los datos
            $data = array(
                'user_id' => $_SESSION['GUID'],
                'comments' => nl2br($json_data["Comment"]),
                'date_published' => date("Y-m-d H:i:s"),
                'score' => $json_data["Score"]
            );
            
            $this->reviewPostModel->Create($_SESSION['GUID'], $data);
            
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
     * Convierte un registro en un objeto JSON preparado
     * @author alca259
     * @version OK
     */
    private function PrepareData($item)
    {
        // Get user
        $bUser = $this->accountModel->Browse(ROOT_USER, array($item['user_id']));
        
        $ratings_text = array(
            0 => T_("Bad.Or.Forgot.To.Rate"),
            1 => T_("Not.For.Me"),
            2 => T_("Not.Bad"),
            3 => T_("Good"),
            4 => T_("Brilliant"),
            5 => T_("Excellent"),
        );
        
        $itemData = array(
            "ReviewId" => $item['id'],
            "ReviewPublishedDate" => date("d/m/Y", strtotime($item['date_published'])),
            "ReviewUserId" => $bUser[0]["name"],
            "ReviewMessage" => stripslashes(html_entity_decode(utf8_encode($item['comments']))),
            "ReviewScore" => $item['score'],
            "ReviewScoreText" => $ratings_text[intval($item['score'])],
        );

        $x5 = "";
        $long = strlen($itemData["ReviewMessage"]);
        
        for ($i = 0; $i < $long; $i++)
        {
            if ($itemData["ReviewMessage"][$i] == "'")
            {
                $x5 = $x5 . '"';
            }
            else
            {
                $x5 = $x5 . $itemData["ReviewMessage"][$i];
            }
        }
        
        $itemData["ReviewMessage"] = $x5;
        
        return $itemData;
    }
    #endregion
}