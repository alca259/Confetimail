<?php

class StoreController extends BaseController
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
        $this->ViewBag->CurrentMenu = "Store";
		$this->ViewBag->Title = T_("Store");
        return new View(__FUNCTION__, $this->controllerName, $this->ViewBag);
	}
    #endregion
}