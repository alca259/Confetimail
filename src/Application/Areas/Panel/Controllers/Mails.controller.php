<?php
/**
 * Class Mails
 * Please note:
 * Don't use the same name for class and method, as this might trigger an (unintended) __construct of the class.
 * This is really weird behaviour, but documented here: http://php.net/manual/en/language.oop5.decon.php
 */
class MailsController extends BaseController
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
            $this->ViewBag->CurrentMenu = "Mails";
            $this->ViewBag->Title = "Mails";
            return new View(__FUNCTION__, $this->controllerName, $this->ViewBag, Constants::$PanelAreaName, true);
		}
        else
        {
			return parent::RedirectToAction("401");
		}
	}

	/**
     * Carga la información del correo actual
	 * @author alca259
	 * @version OK
	 * @param int $IdMail
	 */
	public function Manage($IdMail = 0)
    {
		if (Security::IsAuthorizedAdmin())
        {
            try
            {
                if ($IdMail < 0)
                {
                    throw new Exception("Mail not found");
                }

                if ($IdMail > 0)
                {
                    $dataReturned = $this->GetMailWithId($IdMail, true);

                    if (!JsonHandler::IsJSON($dataReturned))
                    {
                        throw new Exception("Mail data corrupted, is not a valid JSON file");
                    }

                    $this->ViewBag->Data = $dataReturned;
                }
            }
            catch (Exception $ex)
            {
            	$this->ViewBag->Error = parent::ErrorJson($ex);
            }
            
            $this->ViewBag->CurrentMenu = "Mails";
            $this->ViewBag->Title = "Mails";
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
     * Devuelve una lista de emails registrados en el sistema
     * @author alca259
     * @version OK
     */
	public function Mails_Read()
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
            $sItems = $this->mailModel->Search($_SESSION['GUID'], array());

            // Obtencion de datos
            if (!empty($sItems))
            {
                $bItems = $this->mailModel->Browse($_SESSION['GUID'], $sItems, "date_send DESC");
            }

            // Recorremos todos los campos
            foreach ($bItems as $item)
            {
                $itemData = array(
                    "MailId" => $item['id'],
                    "MailSendDate" => date("d/m/Y", strtotime($item['date_send'])),
                    "MailName" => utf8_encode($item['subject']),
                    "MailIsActive" => ($item['active'] == 1) ? true : false,
                    "MailTematica" => utf8_encode($item['tematica']),
                    "MailEsConfeti" => ($item["is_confeti"] == 1 ? true : false)
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
    
    #region Create/Update/Delete
	/**
     * Crea o modifica un correo
	 * @author alca259
	 * @version OK
	 */
	public function SaveMail()
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
                || !isset($json_data["Message"]))
            {
                throw new Exception("No data found");
            }

			// Preparamos los datos
            $header_new = isset($json_data["HeaderNew"]) && strlen($json_data['HeaderNew']) > 0 ? $json_data["HeaderNew"] : "";
            $header_old = isset($json_data["HeaderOld"]) && strlen($json_data['HeaderOld']) > 0 ? $json_data["HeaderOld"] : "";
            $tematica = isset($json_data["Tematica"]) && strlen($json_data['Tematica']) > 0 ? $json_data["Tematica"] : "";
            $tematica_desc = isset($json_data["TematicaDesc"]) && strlen($json_data['TematicaDesc']) > 0 ? $json_data["TematicaDesc"] : "";
            $is_confeti = isset($json_data["IsConfeti"]) && $json_data["IsConfeti"] == "true" ? true : false;
            $image_frontend = isset($json_data["ImageFrontend"]) && strlen($json_data['ImageFrontend']) > 0 ? $json_data["ImageFrontend"] : "";
            $image_carousel = isset($json_data["ImageCarousel"]) && strlen($json_data['ImageCarousel']) > 0 ? $json_data["ImageCarousel"] : "";
            $active = isset($json_data["Active"]) && $json_data["Active"] == "true" ? true : false;
            $date_to_send = date("Y-m-d", strtotime(str_replace('/', '-', $json_data['DateSend'])));
            $Action = $json_data['Action'];
			$IdMail = (isset($json_data['IdMail']) && strlen($json_data['IdMail']) > 0) ? $json_data['IdMail'] : "0";
            
			$data = array(
				'subject' => $json_data['Subject'],
				'header_for_new' => $header_new,
				'header_for_old' => $header_old,
				'body' => $json_data['Message'],
				'date_send' => $date_to_send,
				'active' => $active,
                'tematica' => $tematica,
                'tematica_desc' => $tematica_desc,
                'is_confeti' => $is_confeti,
                'image_frontend' => $image_frontend,
                'image_carousel' => $image_carousel
			);

            switch ($Action)
            {
            	case "Draft":
                    // Creamos el modelo
                    $IdMail = $this->mailModel->Create($_SESSION['GUID'], $data);
                    break;
                case "Edit":
                    // Creamos la busqueda de validacion
                    $domain_model = array(array("id", "=", $IdMail));

                    // Buscamos aquellos emails que coincidan
                    $sModel = $this->mailModel->Search($_SESSION['GUID'], $domain_model);

                    // Si lo encontramos, lo actualizamos
                    if (!empty($sModel))
                    {
                        $this->mailModel->Write($_SESSION['GUID'], $sModel, $data);
                    }
                    break;
                default:
                    throw new Exception(sprintf("Action %s not allowed", $Action));
            }

			$json_data["IdMail"] = $IdMail;

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
     * Elimina un correo
	 * @author alca259
	 * @version OK
	 */
	public function DeleteMail()
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
                || !isset($json_data["IdMail"]))
            {
                throw new Exception("No data found");
            }

			// Vars
			$IdMail = $json_data["IdMail"];

			// Creamos la busqueda de validacion
			$domain_model = array(array("id", "=", $IdMail));

			// Buscamos aquellos emails que coincidan
			$sModel = $this->mailModel->Search($_SESSION['GUID'], $domain_model);

			// Si lo encontramos, lo borramos
			if (!empty($sModel))
            {
				$this->mailModel->Unlink($_SESSION['GUID'], $sModel);
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
	 * Devuelve una lista de imagenes que estan vinculadas a un correo
	 * @author alca259
	 * @version OK
	 */
	public function Images_Read()
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
                || !isset($json_data["IdMail"]))
            {
                throw new Exception("No data found");
            }

		    $result["data"] = array(
			    "rows" => array(),
			    "totalCount" => 0
		    );

			// Vars
			$IdMail = $json_data["IdMail"];
            
			$valid_files = array();
			$binded_files = array();

			// Buscamos todos los ficheros permitidos
			$domain_files = array(
				array("active", "=", true),
				array("file_category", "=", "mail"),
				array("file_type", "=", "image"),
			);

			// Busqueda de ficheros que esten vinculados al correo
			$domain_mail_files = array(array("mail_id", "=", $IdMail));

			// Realizamos la busqueda
			$sFiles = $this->fileModel->Search($_SESSION['GUID'], $domain_files);

			// Buscamos aquellos ficheros que esten vinculados a este mail
			$sMailFiles = $this->mailFileModel->Search($_SESSION['GUID'], $domain_mail_files);

			// Obtencion de datos
			if (!empty($sFiles))
            {
				$valid_files = $this->fileModel->Browse($_SESSION['GUID'], $sFiles, "name ASC");
			}
			if (!empty($sMailFiles))
            {
				$binded_files = $this->mailFileModel->Browse($_SESSION['GUID'], $sMailFiles);
			}

			// Recorremos todos los ficheros validos
			foreach ($valid_files as $file)
            {
				// Si encontramos el fichero vinculado al  entre a los que se le ha enviado el e-mail, guardamos el estado
				foreach ($binded_files as $bind_file)
                {
					if ($bind_file['file_id'] == $file['id'])
                    {
						$itemData = array(
							"id" => $file['id'],
							"name" => utf8_encode($file['file_url']),
							//"url" => utf8_encode($file['file_url']),
                            "type" => "f",
						);

                        $result["data"]["rows"][] = $itemData;
                        $result["data"]["totalCount"]++;
						// Performance
						break;
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
	 * Devuelve una lista de ficheros que pueden ser vinculados a un correo
	 * @author alca259
	 * @version OK
	 */
	public function Attachments_Read()
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
                || !isset($json_data["IdMail"]))
            {
                throw new Exception("No data found");
            }

		    $result["data"] = array(
			    "rows" => array(),
			    "totalCount" => 0
		    );

			// Vars
			$IdMail = $json_data["IdMail"];
			$valid_files = array();
			$binded_files = array();

			// Buscamos todos los ficheros permitidos
			$domain_files = array(
				array("active", "=", true),
				array("file_category", "=", "mail"),
			);

			// Busqueda de ficheros que esten vinculados al correo
			$domain_mail_files = array(array("mail_id", "=", $IdMail));

			// Realizamos la busqueda
			$sFiles = $this->fileModel->Search($_SESSION['GUID'], $domain_files);

			// Buscamos aquellos ficheros que esten vinculados a este mail
			$sMailFiles = $this->mailFileModel->Search($_SESSION['GUID'], $domain_mail_files);

			// Obtencion de datos
			if (!empty($sFiles))
            {
				$valid_files = $this->fileModel->BrowseRecord($_SESSION['GUID'], $sFiles, "name ASC");
			}
            
			if (!empty($sMailFiles))
            {
				$binded_files = $this->mailFileModel->Browse($_SESSION['GUID'], $sMailFiles);
			}

			// Recorremos todos los ficheros validos
			foreach ($valid_files as $file)
            {
				$file_mail = false;

				// Si encontramos el fichero vinculado al  entre a los que se le ha enviado el e-mail, guardamos el estado
				foreach ($binded_files as $bind_file)
                {
					if ($bind_file['file_id'] == $file->data['id'])
                    {
						$file_mail = $bind_file;
						// Performance
						break;
					}
				}

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
					"file_type" => utf8_encode($file->columns['file_type']->getObjectSelected($file->data['file_type'])),
                    "file_date" => str_replace('-', '/', date("d-m-Y H:i", strtotime($fecha_modif))),
					"mail_bind" => "No vínculado",
				);

				if ($file_mail != false)
                {
					$itemData['mail_bind'] = "Vínculado";
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

	/**
     * Intercambia el estado de los ficheros adjuntos a un correo
	 * @author alca259
	 * @version OK
	 */
	public function Attachments_Toggle()
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
                || !isset($json_data["IdMail"])
                || !isset($json_data["Files"])
                || !isset($json_data["Bind"]))
            {
                throw new Exception("No data found");
            }
        
			$IdMail = $json_data['IdMail'];
			$Files = $json_data['Files'];
			$BindData = filter_var($json_data['Bind'], FILTER_VALIDATE_BOOLEAN);

			// Obtenemos la informacion del correo que queremos enviar
			$domain_mail = array(
				array("id", "=", $IdMail),
			);

			$sMail = $this->mailModel->Search($_SESSION['GUID'], $domain_mail);

			if (empty($sMail))
            {
				throw new Exception("No data for mail found");
			}

			// Recorremos cada uno de los ficheros
			foreach ($Files as $IdFile)
            {
				if ($BindData == false)
                {
					// Lo eliminamos si existe
					$domain_file = array(
						array("file_id", "=", $IdFile),
						array("mail_id", "=", $IdMail),
					);
					$sFile = $this->mailFileModel->Search($_SESSION['GUID'], $domain_file);

					if (!empty($sFile))
                    {
						//$mail_data = 
                        $this->mailFileModel->Unlink($_SESSION['GUID'], $sFile);
					}
				}
                else
                {
					// Lo vinculamos
					$dataToBind = array(
						"file_id" => $IdFile,
						"mail_id" => $IdMail,
					);

					$this->mailFileModel->create($_SESSION['GUID'], $dataToBind);
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
    
    #region Send functions
	/**
     * Envia un correo a todos los usuarios seleccionados
	 * @author alca259
	 * @version OK
	 */
	public function SendMail()
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
                || !isset($json_data["IdMail"])
                || !isset($json_data["Users"]))
            {
                throw new Exception("No data found");
            }
        
            // Preparamos los datos
			$IdMail = $json_data['IdMail'];			
			$data_users = $json_data['Users'];
            $error_msg = "";

			// Obtenemos la informacion del correo que queremos enviar
			$domain_mail = array(
				array("id", "=", $IdMail),
			);

			$sMail = $this->mailModel->Search($_SESSION['GUID'], $domain_mail);

			if (empty($sMail))
            {
				throw new Exception("No data found");
			}
            
            $mail_data = $this->mailModel->Browse($_SESSION['GUID'], $sMail);

			// Recorremos cada uno de los usuarios
			foreach ($data_users as $user_id)
            {
                $error_send = parent::SendConfetiMailToUser($IdMail, $user_id, $mail_data, true);

				// Si hay errores al enviar, vamos concatenando
				if (strlen($error_send) > 0)
                {
					$error_msg .= $error_send."<br />";
				}
			}

			if (strlen($error_msg) > 0)
            {
				throw new Exception($error_msg);
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
	 * Devuelve una lista de cuentas a las que se le ha enviado un correo
	 * @author alca259
	 * @version OK
	 */
	public function Subscriptions_Read()
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
                || !isset($json_data["IdMail"]))
            {
                throw new Exception("No data found");
            }

		    $result["data"] = array(
			    "rows" => array(),
			    "totalCount" => 0
		    );

			// Vars
			$IdMail = $json_data["IdMail"];
			$all_active_users = array();
			$sent_mail_users = array();
			$fecha_maxima_registro = date("Y-m-d H:i:s");

			// Buscamos el mail actual
			$bMail = $this->mailModel->browse($_SESSION['GUID'], array($IdMail));

			// Si tiene datos, obtenemos la fecha
			if (count($bMail) == 1)
            {
				$fecha_confetimail = $bMail[0]["date_send"];
				$fecha_maxima_registro = date('Y-m-d H:i:s', strtotime($fecha_confetimail. ' + 60 days'));
			}

			// Busquedas de usuarios
			$domain_users = array(
				array("active", "=", true),
				//array("admin", "!=", "1"),
				array("subscribed", "=", true),
				array("create_date", "<=", $fecha_maxima_registro)
			);

			// Busqueda de correos que esten vinculados a usuarios
			$domain_mails_users = array(array("mail_id", "=", $IdMail));

			// Get all active users with no admin privileges
			$sUsers = $this->accountModel->Search($_SESSION['GUID'], $domain_users);
			// Buscamos aquellos usuarios que esten vinculados a este mail
			$sMailUsers = $this->mailAccountModel->Search($_SESSION['GUID'], $domain_mails_users);

			// Obtencion de datos
			if (!empty($sUsers))
            {
				$all_active_users = $this->accountModel->Browse($_SESSION['GUID'], $sUsers, "name ASC");
			}
			if (!empty($sMailUsers))
            {
				$sent_mail_users = $this->mailAccountModel->Browse($_SESSION['GUID'], $sMailUsers);
			}

			// Recorremos a todos los usuarios filtrados
			foreach ($all_active_users as $user)
            {
				$user_mail = false;

				// Si encontramos al usuario entre a los que se le ha enviado el e-mail, guardamos el estado
				foreach ($sent_mail_users as $users_mail_value)
                {
					if ($users_mail_value['user_id'] == $user['id'])
                    {
						$user_mail = $users_mail_value;
						// Performance
						break;
					}
				}

				$itemData = array(
					"user_id" => $user['id'],
					"user_name" => utf8_encode($user['name']),
					"user_mail" => utf8_encode($user['email']),
				);

				if ($user_mail != false)
                {
					$itemData['mail_sent_date'] = str_replace('-', '/', date("d-m-Y H:i:s", strtotime($user_mail['date_sent'])));
					$itemData['mail_sent_status'] = $user_mail['status'];
					$itemData['mail_status_text'] = MailStatus::GetTextFor($user_mail['status']);
				}
                else
                {
					$itemData['mail_sent_date'] = '';
					$itemData['mail_sent_status'] = MailStatus::NoEnviado;
					$itemData['mail_status_text'] = MailStatus::NoEnviadoText;
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

    #endregion
    
	#region Private methods
	/**
	 * Devuelve los datos de un email, codificado en json y utf-8
	 * @author alca259
	 * @version OK
	 * @param $IdMail
	 * @param bool $EncodeToJson
	 */
	private function GetMailWithId($IdMail, $EncodeToJson = false)
    {
        try
        {
		    // preparamos las variables a devolver
		    $itemData = array();

		    // Creamos la busqueda de validacion
		    $domain_model = array(array("id", "=", $IdMail));

		    // Buscamos aquel email que coincida
		    $sModel = $this->mailModel->Search($_SESSION['GUID'], $domain_model);

		    // Si lo encontramos, lo obtenemos
		    if (empty($sModel) && $IdMail > 0)
            {
			    throw new Exception("Error al obtener el correo");
            }
            elseif ($IdMail == 0)
            {
                return $itemData;
            }
            
			$items = $this->mailModel->Browse($_SESSION['GUID'], $sModel);

			foreach ($items as $item)
            {
				$itemData = array(
					"Id" => $item['id'],
					"Subject" => utf8_encode($item['subject']),
					"HeaderNew" => html_entity_decode(stripslashes(utf8_encode($item['header_for_new']))),
					"HeaderOld" => html_entity_decode(stripslashes(utf8_encode($item['header_for_old']))),
					"Message" => html_entity_decode(stripslashes(utf8_encode($item['body']))),
					"DateSend" => str_replace('-', '/', date("d-m-Y", strtotime($item['date_send']))),
                    "Tematica" => utf8_encode($item['tematica']),
                    "TematicaDesc" => utf8_encode($item['tematica_desc']),
                    "ImageFrontend" => utf8_encode($item['image_frontend']),
                    "ImageCarousel" => utf8_encode($item['image_carousel']),
					"Active" => $item['active'] == 1 ? "true" : "false",
                    "IsConfeti" => $item['is_confeti'] == 1 ? "true" : "false"
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
