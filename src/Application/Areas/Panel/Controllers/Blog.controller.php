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
            $this->ViewBag->CurrentMenu = "Blog";
            $this->ViewBag->Title = "Blog";
            return new View(__FUNCTION__, $this->controllerName, $this->ViewBag, Constants::$PanelAreaName, true);
		}
        else
        {
			return parent::RedirectToAction("401");
		}
	}

	/**
     * Carga la información de la entrada actual
	 * @author alca259
	 * @version OK
	 * @param int $Id
	 */
	public function Manage($Id = 0)
    {
		if (Security::IsAuthorizedAdmin())
        {
            try
            {
                if ($Id < 0)
                {
                    throw new Exception("Post not found");
                }

                if ($Id > 0)
                {
                    $dataReturned = $this->GetPostWithId($Id, true);

                    if (!JsonHandler::IsJSON($dataReturned))
                    {
                        throw new Exception("Post data corrupted, is not a valid JSON file");
                    }

                    $this->ViewBag->Data = $dataReturned;
                }
            }
            catch (Exception $ex)
            {
            	$this->ViewBag->Error = parent::ErrorJson($ex);
            }
            
            $this->ViewBag->CurrentMenu = "Blog";
            $this->ViewBag->Title = "Entrada de blog";
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
     * Devuelve una lista de entradas registradas en el sistema
     * @author alca259
     * @version OK
     */
	public function Posts_Read()
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
            $sItems = $this->blogPostModel->Search($_SESSION['GUID'], array());

            // Obtencion de datos
            if (!empty($sItems))
            {
                $bItems = $this->blogPostModel->Browse($_SESSION['GUID'], $sItems, "date_published DESC");
            }

            // Recorremos todos los campos
            foreach ($bItems as $item)
            {
                $itemData = array(
                    "PostId" => $item['id'],
                    "PostPublishedDate" => date("d/m/Y", strtotime($item['date_published'])),
                    "PostSubject" => utf8_encode($item['subject']),
                    "PostIsActive" => ($item['active'] == 1) ? true : false,
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
     * Devuelve una lista de comentarios de un post registrados en el sistema
     * @author alca259
     * @version OK
     */
	public function Comments_Read()
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
                || !isset($json_data["PostId"]))
            {
                throw new Exception("Required data not found");
            }
            
            // Vars
            $bItems = array();
            $result["data"] = array(
                "rows" => array(),
                "totalCount" => 0
            );

            // Realizamos la busqueda
            $sItems = $this->blogPostCommentModel->Search($_SESSION['GUID'], array(array("post_id", "=", $json_data["PostId"])));

            // Obtencion de datos
            if (!empty($sItems))
            {
                $bItems = $this->blogPostCommentModel->Browse($_SESSION['GUID'], $sItems, "date_published DESC");
            }

            // Recorremos todos los campos
            foreach ($bItems as $item)
            {
                $itemData = array(
                    "CommentId" => $item['id'],
                    "CommentPublishedDate" => date("d/m/Y H:i:s", strtotime($item['date_published'])),
                    "CommentComment" => utf8_encode($item['comments']),
                    "CommentUser" => "Unknown",
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
     * Crea o modifica una entrada
	 * @author alca259
	 * @version OK
	 */
	public function SavePost()
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
                || !isset($json_data["Action"])
                || !isset($json_data["Subject"])
                || !isset($json_data["PostBody"])
                || !isset($json_data["ImageFrontend"]))
            {
                throw new Exception("Required data not found");
            }

			// Preparamos los datos
            $postbody = $json_data["PostBody"];
            $subject = $json_data["Subject"];
            $Action = $json_data['Action'];
            $imagefrontend = $json_data['ImageFrontend'];
            $date_published = "";
            
            $active = isset($json_data["Active"]) && $json_data["Active"] == "true" ? true : false;
            if (isset($json_data["DatePublished"]) && strlen($json_data["DatePublished"]) > 0)
            {
                $date_published = date("Y-m-d", strtotime(str_replace('/', '-', $json_data['DatePublished'])));
            }
            
			$Id = (isset($json_data['Id']) && strlen($json_data['Id']) > 0) ? $json_data['Id'] : "0";
            
			$data = array(
				'subject' => $subject,
				'post_body' => $postbody,
				'date_published' => $date_published,
				'active' => $active,
                'image_frontend' => $imagefrontend
			);

            switch ($Action)
            {
            	case "Draft":
                    // Creamos el modelo
                    $Id = $this->blogPostModel->Create($_SESSION['GUID'], $data);
                    break;
                case "Edit":
                    // Creamos la busqueda de validacion
                    $domain_model = array(array("id", "=", $Id));

                    // Buscamos aquellos post que coincidan
                    $sModel = $this->blogPostModel->Search($_SESSION['GUID'], $domain_model);

                    // Si lo encontramos, lo actualizamos
                    if (!empty($sModel))
                    {
                        $this->blogPostModel->Write($_SESSION['GUID'], $sModel, $data);
                    }
                    break;
                default:
                    throw new Exception(sprintf("Action %s not allowed", $Action));
            }

			$json_data["Id"] = $Id;

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
        
        echo JsonHandler::NormalEncode($result);
	}

	/**
     * Elimina una entrada
	 * @author alca259
	 * @version OK
	 */
	public function DeletePost()
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
			$sModel = $this->blogPostModel->Search($_SESSION['GUID'], $domain_model);

			// Si lo encontramos, lo borramos
			if (!empty($sModel))
            {
				$this->blogPostModel->Unlink($_SESSION['GUID'], $sModel);
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
     * Elimina un comentario
     * @author alca259
     * @version OK
     */
	public function Comment_Delete()
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
			$sModel = $this->blogPostCommentModel->Search($_SESSION['GUID'], $domain_model);

			// Si lo encontramos, lo borramos
			if (!empty($sModel))
            {
				$this->blogPostCommentModel->Unlink($_SESSION['GUID'], $sModel);
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
    
	#region File Functions
	/**
	 * Devuelve una lista de imagenes que son de portada
	 * @author alca259
	 * @version OK
	 */
	public function Images_Read()
    {
        $result = array("success" => false, "data" => array(), "message" => "");
        
        try
        {
		    $ajaxCall = Security::VerifyAjax($_SERVER['REQUEST_METHOD']);

            if (!Security::IsAuthorizedAdmin() || $ajaxCall != 1)
            {
                throw new Exception("Unathorized user");
            }
            
		    $result["data"] = array(
			    "rows" => array(),
			    "totalCount" => 0
		    );

			// Vars
			$valid_files = array();

			// Buscamos todos los ficheros permitidos
			$domain_files = array(
				array("active", "=", true),
				array("file_category", "=", "frontend"),
				array("file_type", "=", "image"),
			);

			// Realizamos la busqueda
			$sFiles = $this->fileModel->Search($_SESSION['GUID'], $domain_files);

			// Obtencion de datos
			if (!empty($sFiles))
            {
				$valid_files = $this->fileModel->Browse($_SESSION['GUID'], $sFiles, "name ASC");
			}

			// Recorremos todos los ficheros validos
			foreach ($valid_files as $file)
            {
				$itemData = array(
					"id" => $file['id'],
					"name" => utf8_encode($file['file_url']),
                    "type" => "f",
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
    #endregion

    #endregion
    
	#region Private methods
	/**
	 * Devuelve los datos de una entrada, codificado en json y utf-8
	 * @author alca259
	 * @version OK
     * @param $Id
	 * @param bool $EncodeToJson
	 */
	private function GetPostWithId($Id, $EncodeToJson = false)
    {
        try
        {
		    // preparamos las variables a devolver
		    $itemData = array();

		    // Creamos la busqueda de validacion
		    $domain_model = array(array("id", "=", $Id));

		    // Buscamos aquel email que coincida
		    $sModel = $this->blogPostModel->Search($_SESSION['GUID'], $domain_model);

		    // Si lo encontramos, lo obtenemos
		    if (empty($sModel) && $Id > 0)
            {
			    throw new Exception("Error al obtener la entrada");
            }
            elseif ($Id == 0)
            {
                return $itemData;
            }
            
			$items = $this->blogPostModel->Browse($_SESSION['GUID'], $sModel);

			foreach ($items as $item)
            {
				$itemData = array(
					"Id" => $item['id'],
					"Subject" => utf8_encode($item['subject']),
					"PostBody" => html_entity_decode(stripslashes(utf8_encode($item['post_body']))),
					"DatePublished" => str_replace('-', '/', date("d-m-Y", strtotime($item['date_published']))),
                    "ImageFrontend" => utf8_encode($item['image_frontend']),
					"Active" => $item['active'] == 1 ? "true" : "false",
				);
			}

			// Devolvemos un objeto
            return $EncodeToJson ? JsonHandler::Encode($itemData) : $itemData;
        }
        catch (ORMException $ex)
        {
            // Errors found, return json error
            return parent::ErrorJson($ex);
        }
        catch (Exception $ex)
        {
            // Errors found, return json error
            return parent::ErrorJson($ex);
        }
	}
    #endregion
}
