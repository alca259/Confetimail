<?php

class BlogController extends BaseController
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
        $this->ViewBag->CurrentMenu = "Blog";
		$this->ViewBag->Title = T_("Blog");
        return new View(__FUNCTION__, $this->controllerName, $this->ViewBag);
	}
    
    /**
     * Carga la pantalla principal
     * @author alca259
     * @version OK
     */
	public function Reading($blogId)
    {
        $item = $this->blogPostModel->Browse(ROOT_USER, array($blogId))[0];
        
        $this->ViewBag->CurrentMenu = "Blog";
		$this->ViewBag->Title = T_("Blog").": ".utf8_encode($item['subject']);
        $this->ViewBag->BlogId = $blogId;
        return new View(__FUNCTION__, $this->controllerName, $this->ViewBag);
	}
    #endregion
    
    #region Ajax actions
    /**
     * Devuelve una lista de entradas registradas en el sistema
     * @author alca259
     * @version OK
     */
	public function Blog_Read()
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
            $domain_search = array(
                array("active", "=", 1),
            );
            
            // Buscamos el conteo total de registros
            $result["data"]["totalResults"] = $this->blogPostModel->Count(ROOT_USER, $domain_search);
            
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
            $sItems = $this->blogPostModel->Search(ROOT_USER, $domain_search, "date_published DESC", $json_data["MaxResults"], $startAtRecord);

            // Obtencion de datos
            if (!empty($sItems))
            {
                $bItems = $this->blogPostModel->Browse(ROOT_USER, $sItems, "date_published DESC");

                // Recorremos todos los campos
                foreach ($bItems as $item)
                {
                    $result["data"]["rows"][] = $this->PrepareData($item);
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
     * Devuelve una lista de entradas registradas en el sistema
     * @author alca259
     * @version OK
     */
	public function Entry_Read()
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
                || !isset($json_data["SelectedBlogId"]))
            {
                throw new Exception(T_("No.Required.Data.Found"));
            }

            // Vars
            $item = $this->blogPostModel->Browse(ROOT_USER, array($json_data["SelectedBlogId"]));
            $result["data"] = $this->PrepareData($item[0]);
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
        $itemData = array(
            "PostId" => $item['id'],
            "PostPublishedDate" => date("d/m/Y", strtotime($item['date_published'])),
            "PostSubject" => stripslashes(html_entity_decode(utf8_encode($item['subject']))),
            "PostBody" => stripslashes(html_entity_decode(utf8_encode($item['post_body']))),
            "PostImage" => $item['image_frontend'],
            "PostIsActive" => ($item['active'] == 1) ? true : false,
            "PostUrl" => StringUtil::UrlAction("Reading", $this->controllerName)."/".$item['id'],
        );

        $x5 = "";
        $long = strlen($itemData["PostBody"]);
        
        for ($i = 0; $i < $long; $i++)
        {
            if ($itemData["PostBody"][$i] == "'")
            {
                $x5 = $x5 . '"';
            }
            else
            {
                $x5 = $x5 . $itemData["PostBody"][$i];
            }
        }
        
        $itemData["PostBody"] = $x5;
        $itemData["PostComments"] = array();
        $itemData["PostCommentsCount"] = 0;
        
        // Realizamos la busqueda
        $sComments = $this->blogPostCommentModel->Search(ROOT_USER, array(array("post_id", "=", $item['id'])), "date_published DESC");

        // Obtencion de datos
        if (!empty($sComments))
        {
            $itemData["PostCommentsCount"] = count($sComments);
            
            $bComments = $this->blogPostCommentModel->Browse(ROOT_USER, $sComments, "date_published DESC");
            
            foreach ($bComments as $itemComment)
            {
                $itemData["PostComments"][] = $this->PrepareComment($itemComment);
            }
        }
        
        return $itemData;
    }
    
    private function PrepareComment($item)
    {
        // Get user
        $bUser = $this->accountModel->Browse(ROOT_USER, array($item['user_id']));
                
        $itemData = array(
            "CommentId" => $item['id'],
            "CommentPublishedDate" => date("d/m/Y", strtotime($item['date_published'])),
            "CommentUserId" => $bUser[0]["name"],
            "CommentMessage" => stripslashes(html_entity_decode(utf8_encode($item['comments']))),
            "CommentPostId" => $item['post_id'],
        );

        $x5 = "";
        $long = strlen($itemData["CommentMessage"]);
        
        for ($i = 0; $i < $long; $i++)
        {
            if ($itemData["CommentMessage"][$i] == "'")
            {
                $x5 = $x5 . '"';
            }
            else
            {
                $x5 = $x5 . $itemData["CommentMessage"][$i];
            }
        }
        
        $itemData["CommentMessage"] = $x5;
        
        return $itemData;
    }
    #endregion
    
    #region Post Comments
    public function Post_Comment()
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
                || !isset($json_data["PostId"]))
            {
                throw new Exception(T_("No.Required.Data.Found"));
            }

            // Guardamos los datos
            $data = array(
				'user_id' => $_SESSION['GUID'],
				'comments' => nl2br($json_data["Comment"]),
				'date_published' => date("Y-m-d H:i:s"),
				'post_id' => $json_data["PostId"]
			);
            
            $this->blogPostCommentModel->Create($_SESSION['GUID'], $data);
            
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