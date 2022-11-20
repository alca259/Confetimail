<?php

/**
 * Class Account
 * Please note:
 * Don't use the same name for class and method, as this might trigger an (unintended) __construct of the class.
 * This is really weird behaviour, but documented here: http://php.net/manual/en/language.oop5.decon.php
 */
class StructureController extends BaseController
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
     * Carga la vista principal de modelos
     * @author alca259
     * @version OK
     */
	public function Index()
    {
		if (Security::isAuthorizedAdmin())
        {
            $this->ViewBag->CurrentMenu = "Structure";
            $this->ViewBag->Title = "Estructura";
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
     * Función que carga los modelos en un grid
     * @author alca259
     * @version OK
     */
    public function Models_Read()
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
            $sItems = $this->irModelModel->Search($_SESSION['GUID'], array());

            // Obtencion de datos
            if (!empty($sItems))
            {
                $bItems = $this->irModelModel->Browse($_SESSION['GUID'], $sItems, "name ASC");
            }

            // Recorremos todos los campos
            foreach ($bItems as $item)
            {

                $itemData = array(
                    "ModelId" => $item['id'],
                    "ModelName" => utf8_encode($item['name']),
                    "ModelDesc" => utf8_encode($item['description']),
                    "ModelIsActive" => ($item['active'] == 1) ? true : false
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
     * Función que busca ficheros de modelos en las carpetas de modelos del sistema
     * y los registra para poder instalarlos en la aplicación
     * @author alca259
     * @version OK
     */
	public function SearchModels()
    {
        $result = array("success" => false, "data" => array(), "message" => "");
        
		try
        {
            $ajaxCall = Security::VerifyAjax($_SERVER['REQUEST_METHOD']);

            if (!Security::IsAuthorizedAdmin() || $ajaxCall != 1)
            {
                throw new Exception("Unathorized access");
            }

			// Recorremos las carpetas de modelos
            $paths = GlobalConfig::$registeredPathAreas;
            $paths["Base"] = GlobalConfig::$appBaseUrl;

            foreach ($paths as $areaName => $pathArea)
            {
        	    $path = $pathArea."/Models/";
			    $dir = opendir($path);

			    // Leo todos los ficheros de la carpeta
			    while ($elemento = readdir($dir))
                {
				    // Tratamos los elementos . y .. que tienen todas las carpetas
				    if( $elemento != "." && $elemento != "..")
                    {
					    // Si es una carpeta
					    if(!is_dir($path.$elemento) && strpos($elemento, ".model.php"))
                        {
						    // Obtenemos el nombre de cada fichero, formateado de acuerdo a la clase que contiene
						    // Position
						    $pos = strpos($elemento, ".model.php");
						    $model = substr($elemento, 0, $pos);
						    $model_array = explode("_", $model);
						    foreach ($model_array as $key => $value)
                            {
							    $model_array[$key] = ucwords($value);
						    }
						    $model_name = implode("_", $model_array);

						    // Creamos la busqueda de validacion
						    $domain_model = array(array("name", "=", $model_name));

						    // Buscamos aquellos modelos que coincidan
						    $sModel = $this->irModelModel->Search($_SESSION['GUID'], $domain_model);

						    // Si está vacio, insertamos
						    if (empty($sModel))
                            {
                                $this->irModelModel->Create($_SESSION['GUID'], array(
                                    'name' => $model_name,
                                    'description' => $model_name,
                                    'active' => false,
                                    "area" => $areaName == "Base" ? "" : $areaName)
                                );
						    }
					    }
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
     * Esta función realiza varias tareas:
     *  - Instalar un modelo, genera toda la estructura de base de datos referente al modelo
     *  - Actualiza un modelo, comprueba la estructura de datos, la actualiza y migra la información al nuevo campo
     *  - Elimina un modelo, destruye la tabla y las relaciones que tuviera
     * @author alca259
     * @version OK
     */
	public function ReloadModel()
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

            if (empty($json_data)
                || !isset($json_data["IdModel"])
                || !isset($json_data["Action"]))
            {
                throw new Exception("No data found");
            }
            
			$IdModel = $json_data['IdModel'];
			$Action = $json_data['Action'];

			// Creamos la busqueda de validacion
			$domain_model = array(array("id", "=", $IdModel));

			// Buscamos aquellos modelos que coincidan
			$sModel = $this->irModelModel->Search($_SESSION['GUID'], $domain_model);

			// Si no está vacio, recreamos las tablas y actualizamos
			if (empty($sModel))
            {
                throw new Exception("No data found");
            }
            
			// Obtenemos el nombre del modelo
			$bModel = $this->irModelModel->Browse($_SESSION['GUID'], $sModel);

            switch ($Action)
            {
            	case "Install":
                    // Forzamos la recreación de las tablas
                    $this->LoadModel($bModel[0]['name'], $bModel[0]['area'], true);
                    // Lo marcamos como activo y guardamos
                    $data = array('active' => true);
                    $this->irModelModel->Write($_SESSION['GUID'], $sModel, $data);
                    break;
                case "Upgrade":
                    // Forzamos la recreación de las tablas
                    $this->LoadModel($bModel[0]['name'], $bModel[0]['area'], true);
                    break;
                case "Drop":
                    // Lo borramos del modelo
                    $this->irModelModel->Unlink($_SESSION['GUID'], $sModel);

                    // Forzamos la eliminación de la tabla
                    $this->DropModel($bModel[0]['name'], $bModel[0]['area']);
                    break;
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
}
