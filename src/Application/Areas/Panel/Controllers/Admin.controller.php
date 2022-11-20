<?php

class AdminController extends BaseController
{
    #region Variables privadas
    private $controllerName;
    private $mesesN = array(
        1 => "Enero",       2 => "Febrero",     3 => "Marzo",       4 => "Abril",
        5 => "Mayo",        6 => "Junio",       7 => "Julio",       8 => "Agosto",
        9 => "Septiembre",  10 => "Octubre",    11 => "Noviembre",  12 => "Diciembre"
    );
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
            $this->ViewBag->CurrentMenu = "Home";
            $this->ViewBag->Title = "Administración de Confeti";
            return new View(__FUNCTION__, $this->controllerName, $this->ViewBag, Constants::$PanelAreaName, true);
        }
        else
        {
            return parent::RedirectToAction("401");
        }
    }
    #endregion

    #region Action Ajax
    public function UsersMonthly_Read()
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
            $startInterval = new DateTime(date("Y-m-d", strtotime("-12 months")));
            $interval = DateInterval::createFromDateString('1 month');
            $period = new DatePeriod($startInterval, $interval, 12);

            foreach ($period as $dt)
            {
                $currentDate = $dt->format("Y-m-d");
                
                // Obtenemos las fechas de inicio y fin del mes en curso
                $startDate = date('Y-m-01', strtotime($currentDate));
                $endDate = date("Y-m-t", strtotime($currentDate));
                
                // Realizamos la busqueda
                $sUsers = $this->accountModel->Search($_SESSION['GUID'], array(
                    array ("create_date", "<=", $endDate),
                    array ("create_date", ">=", $startDate),
                ));
                
                $itemData = array(
                    "date" => date("Y-m-d", strtotime($currentDate)),
                    "monthName" => $this->mesesN[date("n", strtotime($currentDate))],
                    "yearName" => date("Y", strtotime($currentDate)),
                    "valueNumber" => count($sUsers)
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
    
    public function CommentsMonthly_Read()
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
            $result["data"] = array();

            $startInterval = new DateTime(date("Y-m-d", strtotime("-12 months")));
            $interval = DateInterval::createFromDateString('1 month');
            $period = new DatePeriod($startInterval, $interval, 12);

            foreach ($period as $dt)
            {
                $currentDate = $dt->format("Y-m-d");
                
                // Obtenemos las fechas de inicio y fin del mes en curso
                $startDate = date('Y-m-01', strtotime($currentDate));
                $endDate = date("Y-m-t", strtotime($currentDate));
                
                // Realizamos la busqueda
                $sReviews = $this->reviewPostModel->Search($_SESSION['GUID'], array(
                    array ("date_published", "<=", $endDate),
                    array ("date_published", ">=", $startDate),
                ));
                
                // Buscamos tambien los comentarios en los post
                $sComments = $this->blogPostCommentModel->Search($_SESSION['GUID'], array(
                    array ("date_published", "<=", $endDate),
                    array ("date_published", ">=", $startDate),
                ));
                
                $itemData = array(
                    "date" => date("Y-m-d", strtotime($currentDate)),
                    "monthName" => $this->mesesN[date("n", strtotime($currentDate))],
                    "yearName" => date("Y", strtotime($currentDate)),
                    "valueNumber" => count($sReviews) + count($sComments)
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
    
    public function PostsMonthly_Read()
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
            $result["data"] = array();

            $startInterval = new DateTime(date("Y-m-d", strtotime("-12 months")));
            $interval = DateInterval::createFromDateString('1 month');
            $period = new DatePeriod($startInterval, $interval, 12);

            foreach ($period as $dt)
            {
                $currentDate = $dt->format("Y-m-d");
                
                // Obtenemos las fechas de inicio y fin del mes en curso
                $startDate = date('Y-m-01', strtotime($currentDate));
                $endDate = date("Y-m-t", strtotime($currentDate));
                
                // Realizamos la busqueda
                $sReviews = $this->blogPostModel->Search($_SESSION['GUID'], array(
                    array ("date_published", "<=", $endDate),
                    array ("date_published", ">=", $startDate),
                ));
                
                $itemData = array(
                    "date" => date("Y-m-d", strtotime($currentDate)),
                    "monthName" => $this->mesesN[date("n", strtotime($currentDate))],
                    "yearName" => date("Y", strtotime($currentDate)),
                    "valueNumber" => count($sReviews)
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
    
    public function ScoreMonthly_Read()
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
            $result["data"] = array();

            $startInterval = new DateTime(date("Y-m-d", strtotime("-12 months")));
            $interval = DateInterval::createFromDateString('1 month');
            $period = new DatePeriod($startInterval, $interval, 12);

            foreach ($period as $dt)
            {
                $currentDate = $dt->format("Y-m-d");
                
                // Obtenemos las fechas de inicio y fin del mes en curso
                $startDate = date('Y-m-01', strtotime($currentDate));
                $endDate = date("Y-m-t", strtotime($currentDate));
                
                // Realizamos la busqueda
                $sReviews = $this->reviewPostModel->Search($_SESSION['GUID'], array(
                    array ("date_published", "<=", $endDate),
                    array ("date_published", ">=", $startDate),
                ));
                
                $itemData = array(
                    "date" => date("Y-m-d", strtotime($currentDate)),
                    "monthName" => $this->mesesN[date("n", strtotime($currentDate))],
                    "yearName" => date("Y", strtotime($currentDate)),
                    "valueNumber" => 0
                );
                
                if (!empty($sReviews))
                {
                    $bReviews = $this->reviewPostModel->Browse($_SESSION['GUID'], $sReviews);
                    
                    $total_numbers = count($sReviews);
                    $total_score = 0;
                    
                    foreach ($bReviews as $review)
                    {
                        $total_score += $review['score'];
                    }
                    
                    $itemData["valueNumber"] = ($total_score / $total_numbers);
                }
                
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
    
    public function SurveyUnits_Read()
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
            $result["data"] = array();

            $surveyFields = $this->surveyAccountModel->GetSurveyFields();

            // Cargamos los valores que ya tenia el usuario guardados en DB
            foreach ($surveyFields as $key1 => $field)
            {
                // Obtenemos el numero de usuarios que tienen este interes
                $sItems = $this->surveyAccountModel->Search($_SESSION['GUID'], array(
                    array ($field["field_name"], "=", true)
                ));
                
                $itemData = array(
                    "categories" => $field["field_title"],
                    "valueNumber" => count($sItems),
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
}
