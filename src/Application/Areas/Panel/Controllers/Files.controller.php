<?php
ini_set('max_input_time', "300");
ini_set('post_max_size', '128M');
set_time_limit(300);

class FilesController extends BaseController
{
	#region Variables privadas
    private $controllerName;
    private $uploadFilesUrl = '';
    private $uploadFilesRelativeUrl = '';
    private $uploadImagesUrl = '';
    private $uploadImagesRelativeUrl = '';
    #endregion

    #region Constructor
	public function __construct()
    {
		// Llamamos al constructor padre
		parent::__construct();
        $this->controllerName = str_replace("Controller", "", __CLASS__);
        
        $this->uploadFilesUrl = Constants::$UploadFilesUrl;
        $this->uploadFilesRelativeUrl = Constants::$UploadFilesRelativeUrl;
        $this->uploadImagesUrl = Constants::$UploadImagesUrl;
        $this->uploadImagesRelativeUrl = Constants::$UploadImagesRelativeUrl;
	}
    #endregion

	#region Action controllers
	public function Index()
    {
        if (Security::IsAuthorizedAdmin())
        {
            $this->ViewBag->CurrentMenu = "Files";
            $this->ViewBag->Title = "Ficheros";
            return new View(__FUNCTION__, $this->controllerName, $this->ViewBag, Constants::$PanelAreaName, true);
		}
        else
        {
            return parent::RedirectToAction("401");
		}
	}
    
    public function NewFile()
    {
        if (!Security::IsAuthorizedAdmin())
        {
            return parent::RedirectToAction("401");
        }
        
        $ajaxCall = Security::VerifyAjax($_SERVER['REQUEST_METHOD']);

        if ($ajaxCall != 1)
        {
            // Cargamos la vista
            $this->ViewBag->CurrentMenu = "Files";
            $this->ViewBag->Title = "Nuevo fichero";
            return new View(__FUNCTION__, $this->controllerName, $this->ViewBag, Constants::$PanelAreaName, true);
        }
        
        if (empty($_POST) 
                || !isset($_FILES['FileInput']) 
                || !isset($_POST['FileName']) 
                || !isset($_POST['FileType'])
                || !isset($_POST['FileCategory']) 
                || !isset($_POST['FileActive']))
        {
            $this->ViewBag->Error = "Not all data is found";
            $this->ViewBag->CurrentMenu = "Files";
            $this->ViewBag->Title = "Nuevo fichero";
            return new View(__FUNCTION__, $this->controllerName, $this->ViewBag, Constants::$PanelAreaName, true);
        }
        
        // Aqui ya tenemos todos los datos, lanzamos la subida
        $itemData = array(
            "Files" => $_FILES["FileInput"],
            "FileName" => $_POST["FileName"],
            "FileType" => $_POST["FileType"],
            "FileCategory" => $_POST["FileCategory"],
            "FileActive" => $_POST["FileActive"],
        );
        
        $result = $this->UploadFile($itemData);
        
        // Devolvemos el error por el que sea que no se ha podido subir
        if (!$result["success"])
        {
            $this->ViewBag->Error = $result["message"];
            $this->ViewBag->CurrentMenu = "Files";
            $this->ViewBag->Title = "Nuevo fichero";
            return new View(__FUNCTION__, $this->controllerName, $this->ViewBag, Constants::$PanelAreaName, true);
        }
        
        // Correcto, devolvemos la vista normal
        $this->ViewBag->CurrentMenu = "Files";
        $this->ViewBag->Title = "Nuevo fichero";
        return new View(__FUNCTION__, $this->controllerName, $this->ViewBag, Constants::$PanelAreaName, true);
    }
    #endregion

	#region Action Ajax
    /**
     * Función que devuelve los ficheros cargados en el servidor
     */
	public function Files_Read()
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
			$valid_files = array();
            
            $result["data"] = array(
				"rows" => array(),
				"totalCount" => 0
			);

			// Realizamos la busqueda
			$sFiles = $this->fileModel->Search($_SESSION['GUID'], array());

			// Obtencion de datos
			if (!empty($sFiles))
            {
				$valid_files = $this->fileModel->BrowseRecord($_SESSION['GUID'], $sFiles, "name ASC");
			}

			// Recorremos todos los ficheros validos
			foreach ($valid_files as $file)
            {
				$fecha_modif = $file->data['create_date'];

				if (strtotime($file->data['write_date']) > 0)
                {
					$fecha_modif = $file->data['write_date'];
				}

				$itemData = array(
					"file_id" => $file->data['id'],
					"file_name" => utf8_encode($file->data['name']),
					"file_url" => utf8_encode($file->data['file_url']),
					"full_url" => utf8_encode("/".substr($file->data['full_url'], 2)),
					"file_type" => utf8_encode($file->columns['file_type']->GetObjectSelected($file->data['file_type'])),
					"file_date" => str_replace('-', '/', date("d-m-Y H:i", strtotime($fecha_modif))),
					"file_category" => utf8_encode($file->columns['file_category']->GetObjectSelected($file->data['file_category'])),
					"file_active" => $file->data['active'] == 1 ? true : false
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
     * Función que modifica la información del fichero subido
     */
	public function UpdateFile()
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
                || !isset($json_data["file_id"]))
            {
                throw new Exception("No data found");
            }

			$ItemData = array(
				'active' => $json_data["file_active"],
			);

            // Buscamos el original
            $sFile = $this->fileModel->Search($_SESSION['GUID'], array(array("id", "=", $json_data["file_id"])));

            // Si no existe, no seguimos
            if (empty($sFile))
            {
                throw new Exception("No data found");
            }

            // Recover name of file
            if (strlen($json_data["file_name"]) > 0)
            {
                $ItemData['name'] = $json_data["file_name"];
            }

            if (strlen($json_data["file_type"]) > 0)
            {
                $fileTypes = $this->fileModel->columns['file_type']->GetObjects();

                // Marcamos la variable de modificacion a falsa
                $keyTypeFile = false;
                $valueTypeFile = false;

                // Buscamos la clave del valor o clave que nos hayan enviado
                foreach ($fileTypes as $key => $value)
                {
                    // Primero comprobamos si lo que nos han enviado es un valor
                    if ($json_data["file_type"] == $value)
                    {
                        $keyTypeFile = $key;
                        $valueTypeFile = $value;
                        break;
                    }

                    // Si no, comprobamos si la clave coincide
                    if ($json_data["file_type"] == $key)
                    {
                        $keyTypeFile = $key;
                        $valueTypeFile = $value;
                        break;
                    }
                }

                if ($keyTypeFile)
                {
                    $ItemData['file_type'] = $keyTypeFile;
                    $json_data["file_type"] = $valueTypeFile;
                }
            }

            if (strlen($json_data["file_category"]) > 0)
            {
                $fileTypes = $this->fileModel->columns['file_category']->GetObjects();

                // Marcamos la variable de modificacion a falsa
                $keyCategoryFile = false;
                $valueCategoryFile = false;

                // Buscamos la clave del valor o clave que nos hayan enviado
                foreach ($fileTypes as $key => $value)
                {
                    // Primero comprobamos si lo que nos han enviado es un valor
                    if ($json_data["file_category"] == $value)
                    {
                        $keyCategoryFile = $key;
                        $valueCategoryFile = $value;
                        break;
                    }

                    // Si no, comprobamos si la clave coincide
                    if ($json_data["file_category"] == $key)
                    {
                        $keyCategoryFile = $key;
                        $valueCategoryFile = $value;
                        break;
                    }
                }

                if ($keyCategoryFile)
                {
                    $ItemData['file_category'] = $keyCategoryFile;
                    $json_data["file_category"] = $valueCategoryFile;
                }
            }

            switch ($json_data["Action"])
            {
                case "update":
                    $this->fileModel->Write($_SESSION['GUID'], $sFile, $ItemData);
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
     * Función que elimina los ficheros fisicos y de base de datos
     */
	public function RemoveFile()
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
            
            if (empty($json_data) 
                || !isset($json_data["FilesId"]))
            {
                throw new Exception("No data found");
            }

			// Obtenemos los datos
			$FilesId = $json_data['FilesId'];

			foreach ($FilesId as $FileId)
            {
				// Obtenemos la linea
				$bData = $this->fileModel->Browse($_SESSION['GUID'], array($FileId));

				if (count($bData) == 1)
                {
					// Borramos el fichero
					if (file_exists($bData[0]['full_url']))
                    {
                        unlink($bData[0]['full_url']);
                    }
					// Borramos los datos
					$this->fileModel->Unlink($_SESSION['GUID'], array($FileId));
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
     * Busca ficheros en el servidor que no esten registrados ya
     * y los registra en el sistema en base a la extension
     * @author alca259
     * @version OK
     */
    public function SearchFiles()
    {
        $result = array("success" => false, "data" => array(), "message" => "");
        
        try
        {
            $ajaxCall = Security::VerifyAjax($_SERVER['REQUEST_METHOD']);
            
            if (!Security::IsAuthorizedAdmin() || $ajaxCall != 1)
            {
                throw new Exception("Unathorized user");
            }

            $counter_files = 0;
            $counter_image = 0;

            // Recuperamos la lista de todos los ficheros subidos
            $sFiles = $this->fileModel->Search($_SESSION['GUID'], array());
            $bFiles = $this->fileModel->Browse($_SESSION['GUID'], $sFiles);

            $sFiles = array();
            foreach ($bFiles as $bFile)
            {
                $sFiles[] = strtolower($bFile['file_url']);
            }

            #region Registro de ficheros
            // Recorremos la carpeta de ficheros
            $dir = opendir($this->uploadFilesUrl);

            // Leo todos los ficheros de la carpeta
            while ($elemento = readdir($dir))
            {
                // Tratamos los elementos . y .. que tienen todas las carpetas y
                // continuamos si el elemento actual es una carpeta
                if ($elemento == "." || $elemento == ".." || is_dir($this->uploadFilesUrl.$elemento))
                {
                    continue;
                }

                // Comprobamos si el fichero ya esta registrado
                if (in_array(strtolower($this->uploadFilesRelativeUrl.$elemento), $sFiles))
                {
                    continue;
                }

                // Como no existe, lo vamos a registrar
                $this->fileModel->create($_SESSION['GUID'], array(
                    'name' => $elemento,
                    'file_url' => $this->uploadFilesRelativeUrl.$elemento,
                    'full_url' => $this->uploadFilesUrl.$elemento,
                    'file_type' => "other",
                    'file_category' => "other",
                    'active' => true,
                ));

                $counter_files++;
            }
            #endregion

            #region Registro de imagenes
            // Recorremos la carpeta de imagenes
            $dir = opendir($this->uploadImagesUrl);

            // Leo todos los ficheros de la carpeta
            while ($elemento = readdir($dir))
            {
                // Tratamos los elementos . y .. que tienen todas las carpetas y
                // continuamos si el elemento actual es una carpeta
                if ($elemento == "." || $elemento == ".." || is_dir($this->uploadImagesUrl.$elemento))
                {
                    continue;
                }

                // Comprobamos si el fichero ya esta registrado
                if (in_array(strtolower($this->uploadImagesRelativeUrl.$elemento), $sFiles))
                {
                    continue;
                }

                // Como no existe, lo vamos a registrar
                $this->fileModel->create($_SESSION['GUID'], array(
                    'name' => $elemento,
                    'file_url' => $this->uploadImagesRelativeUrl.$elemento,
                    'full_url' => $this->uploadImagesUrl.$elemento,
                    'file_type' => "image",
                    'file_category' => "other",
                    'active' => true,
                ));

                $counter_image++;
            }
            #endregion

            $result['message'] = sprintf("(%s) ficheros nuevos y (%s) imagenes nuevas", $counter_files, $counter_image);
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
     * Función que devuelve las categorias de los archivos
     */
	public function GetComboboxCategoryFiles()
	{
        $result = array("success" => false, "data" => array(), "message" => "");
        
        try
        {
		    $ajaxCall = Security::VerifyAjax($_SERVER['REQUEST_METHOD']);

            if (!Security::IsAuthorizedAdmin() || $ajaxCall != 1)
            {
                throw new Exception("Unathorized user");
            }

			$fileTypes = $this->fileModel->columns['file_category']->GetObjects();

			foreach ($fileTypes as $key => $value)
            {
				$itemData = array(
					"category_id" => utf8_encode($key),
					"category_name" => utf8_encode($value),
				);

				$result["data"][] = $itemData;
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
     * Función que devuelve los tipos de archivos
     */
	public function GetComboboxFileTypes()
	{
        $result = array("success" => false, "data" => array(), "message" => "");
        
        try 
        {
            $ajaxCall = Security::VerifyAjax($_SERVER['REQUEST_METHOD']);

            if (!Security::IsAuthorizedAdmin() || $ajaxCall != 1)
            {
                throw new Exception("Unathorized user");
            }
            
			$fileTypes = $this->fileModel->columns['file_type']->GetObjects();

			foreach ($fileTypes as $key => $value)
            {
				$itemData = array(
					"type_id" => utf8_encode($key),
					"type_name" => utf8_encode($value),
				);

				$result["data"][] = $itemData;
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
    
    #region Private methods
    /**
     * Función que sube un fichero al servidor y lo registra en la base de datos
     * Además realiza una validación de tipo y tamaño
     */
	private function UploadFile($itemInfo)
    {
        $UploadDirectory = "";
        $NewFileName = "";
        
        $result = array("success" => false, "message" => "");
        
        try 
        {           
		    #region ########### Getting vars ###########
		    $FileName = $itemInfo['FileName'];
		    $FileType = $itemInfo['FileType'];
		    $FileCategory = $itemInfo['FileCategory'];
		    $FileActive = $itemInfo['FileActive'] == "on" || $itemInfo['FileActive'] == "true" ? true : false;
		    $UploadDirectory = "";
		    $RelativeURL = "";
            $files = $itemInfo["Files"];
            $counterFiles = is_array($files["name"]) ? count($files["name"]) : 1;
		    #endregion

		    #region ########### Directory settings ###########
		    switch($FileType)
            {
			    case "image":
				    $UploadDirectory = $this->uploadImagesUrl; //specify upload directory ends with / (slash)
				    $RelativeURL = $this->uploadImagesRelativeUrl;
				    break;
			    case "zip":
			    case "pdf":
			    case "other":
				    $UploadDirectory = $this->uploadFilesUrl; //specify upload directory ends with / (slash)
				    $RelativeURL = $this->uploadFilesRelativeUrl;
				    break;
			    default:
                    throw new Exception("Not allowed to upload");
		    }

		    if (!file_exists($UploadDirectory))
            {
			    mkdir($UploadDirectory, 0755, true);
		    }
		    #endregion

            for ($i = 0; $i < $counterFiles; $i++)
            {
		        #region ########### Server Validation ###########
		        // Is file size is less than allowed size.
		        if ($files["size"][$i] > 134217728) // 128MB
                {
                    throw new Exception("File size is too big (".($files["size"][$i]/1048576)."MB)");
		        }

		        $fType = $files['type'][$i];
		        if ($fType == "" || $fType == "application/octet-stream")
                {
			        $fName = $files['name'][$i];
			        $fType = strlen($fName) > 4 ? "ext/".substr($fName, strlen($fName) - 4) : "";
		        }

		        // Allowed file type Server side check
		        switch(strtolower($fType))
		        {
			        //allowed file types
			        case 'image/png':
			        case 'image/gif':
			        case 'image/jpeg':
			        case 'image/pjpeg':
			        case 'text/plain':
			        case 'text/html': //html file
			        case 'application/x-zip-compressed':
			        case 'application/zip':
			        case 'ext/.zip':
                    case 'ext/zip':
			        case 'application/pdf':
			        case 'application/msword':
			        case 'application/vnd.ms-excel':
			        case 'video/mp4':
				        break;
			        default:
				        // Unsupported File!
				        throw new Exception("Extension not allowed (".$files["type"][$i].")");
		        }
		        #endregion

                #region ########### Upload file ###########
                // No hay errores
                $File_Name          = strtolower($files['name'][$i]);
                $File_Ext           = substr($File_Name, strrpos($File_Name, '.')); //get file extention
                $Random_Number      = rand(0, 9999999999); //Random number to be added to name.
                $NewFileName        = $Random_Number.$File_Ext; //new file name

                // Subimos el fichero
                if(!move_uploaded_file($files['tmp_name'][$i], $UploadDirectory.$NewFileName))
                {
                    throw new Exception("No se puede subir el fichero a la ruta ".$UploadDirectory.". Error desconocido.");
                }
                #endregion
                
                #region ########### Register in DB ###########
                $ItemData = array(
                    'name' => $FileName." - ".$File_Name,
                    'file_url' => $RelativeURL.$NewFileName,
                    'full_url' => $UploadDirectory.$NewFileName,
                    'file_type' => $FileType,
                    'file_category' => $FileCategory,
                    'active' => $FileActive,
                );

                // Upload to database
                $this->fileModel->Create($_SESSION['GUID'], $ItemData);
                #endregion  
            }
            
            $result["success"] = true;
        }
        catch (ORMException $ex)
        {
            // Si tenemos errores, borramos el fichero subido
            if ($UploadDirectory != "" && $NewFileName != "")
            {
                unlink($UploadDirectory.$NewFileName);
            }
            
            // Errors found, return message
            $result["message"] = parent::ErrorJson($ex);
        }
        catch (Exception $ex)
        {
            // Errors found, return message
            $result["message"] = parent::ErrorJson($ex);
        }

        return $result;
	}
    #endregion
}