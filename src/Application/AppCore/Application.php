<?php

class Application {

    /**
     * Area/Controlador/Accion
     */
    private $url_area = "";
	private $url_controller = "";
	private $url_action = "";

	/**
     * Máximo de tres parámetros mediante GET
     */
	private $url_parameter_1 = "";
	private $url_parameter_2 = "";
	private $url_parameter_3 = "";

	/**
     * "Start" the application:
     * Analyze the URL elements and calls the according controller/method or the fallback
     */
	public function __construct()
    {
		// Create array with URL parts in $url
		$this->SplitUrl();

        $full_url_controller = $this->url_area != ""
            ? GlobalConfig::$registeredPathAreas[$this->url_area]."/Controllers/".$this->url_controller.".controller.php"
            : GlobalConfig::$appBaseUrl."/Controllers/".$this->url_controller.".controller.php";

		// check for controller: does such a controller exist ?
		if (!file_exists($full_url_controller))
        {
			// Invalid URL, show 404 error
            return new View("", "", array());
        }

		// if so, then load this file and create this controller
		// example: if controller would be "car", then this line would translate into: $this->car = new car();
		require_once $full_url_controller;

		// Agregamos la extension controller al nombre de la clase
        $controllerClass = $this->url_controller."Controller";
		// Creamos un objeto nuevo para este controlador
		$controllerClass = new $controllerClass;

		// check for method: does such a method exist in the controller ?
		if (!method_exists($controllerClass, $this->url_action))
        {
            // default/fallback: call the index() method of a selected controller
			return $controllerClass->Index();
        }

		// call the method and pass the arguments to it
		if ($this->url_parameter_3 != "")
        {
			// will translate to something like $this->home->method($param_1, $param_2, $param_3);
			return $controllerClass->{$this->url_action}($this->url_parameter_1, $this->url_parameter_2, $this->url_parameter_3);
		}
        elseif ($this->url_parameter_2 != "")
        {
			// will translate to something like $this->home->method($param_1, $param_2);
			return $controllerClass->{$this->url_action}($this->url_parameter_1, $this->url_parameter_2);
		}
        elseif ($this->url_parameter_1 != "")
        {
			// will translate to something like $this->home->method($param_1);
			return $controllerClass->{$this->url_action}($this->url_parameter_1);
		}
        else
        {
			// if no parameters given, just call the method without parameters, like $this->home->method();
			return $controllerClass->{$this->url_action}();
		}
	}

	/**
     * Get and split the URL
     */
	private function SplitUrl()
    {
        // Default data
        $this->url_controller = "Home";
        $this->url_action = "Index";
        
		if (isset($_GET['url']))
        {
			// split URL
			$url = rtrim($_GET['url'], '/');
			$url = filter_var($url, FILTER_SANITIZE_URL);
			$url = explode('/', $url);

			// Put URL parts into according properties
			// By the way, the syntax here is just a short form of if/else, called "Ternary Operators"
			// @see http://davidwalsh.name/php-shorthand-if-else-ternary-operators
            // We never call Area name same of controller name, or this crash

            $areaUrl = isset($url[0]) && array_key_exists($url[0], GlobalConfig::$registeredPathAreas);
            
            if ($areaUrl)
            {
                $this->url_area = (isset($url[0]) ? $url[0] : "");
                $this->url_controller = (isset($url[1]) ? $url[1] : "");
                $this->url_action = (isset($url[2]) ? $url[2] : "");
                $this->url_parameter_1 = (isset($url[3]) ? $url[3] : "");
                $this->url_parameter_2 = (isset($url[4]) ? $url[4] : "");
                $this->url_parameter_3 = (isset($url[5]) ? $url[5] : "");
                
                if (isset($url[6])) die("GET Method exception: No puedes suministrar más de tres parametros mediante una URL");
            }
            else
            {
                $this->url_controller = (isset($url[0]) ? $url[0] : "");
                $this->url_action = (isset($url[1]) ? $url[1] : "");
                $this->url_parameter_1 = (isset($url[2]) ? $url[2] : "");
                $this->url_parameter_2 = (isset($url[3]) ? $url[3] : "");
                $this->url_parameter_3 = (isset($url[4]) ? $url[4] : "");
                
                if (isset($url[5])) die("GET Method exception: No puedes suministrar más de tres parametros mediante una URL");
            }
		}
	}

}
