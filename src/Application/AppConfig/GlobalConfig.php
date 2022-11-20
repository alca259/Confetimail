<?php

/**
 * Start application with all data
 *
 * @version 1.0
 * @author Mod
 */
class GlobalConfig
{
    #region Private vars
    public static $appBaseUrl = "./Application";
    public static $appAreasUrl = "./Application/Areas/";
    public static $registeredPathAreas = array();
    public static $supportedLocales = array('en_US', 'es_ES');
    #endregion
    
    #region Public methods
    /**
     * Start and load all required libs
     */
    public static function Configure()
    {
        self::RegisterConfig();
        self::RegisterHelpers();
        self::RegisterCore();
        self::RegisterAreas();
        self::RegisterModels();
    }
    #endregion
    
    #region Private methods
    /**
     * Must be the first lib on load
     */
    private static function RegisterConfig()
    {
        self::LoadFiles(self::$appBaseUrl."/AppConfig/", array("GlobalConfig.php"));
    }
    
    /**
     * Register and load libs and app global core
     */
    private static function RegisterCore()
    {
        self::LoadFiles(self::$appBaseUrl."/AppCore/Interfaces/");
        self::LoadFiles(self::$appBaseUrl."/AppCore/");
    }
    
    /**
     * Register all areas for load models and controllers
     */
    private static function RegisterAreas()
    {
        // Reset
        self::$registeredPathAreas = array();
        
        $dir = opendir(self::$appAreasUrl);

        // Leo todas las carpetas de Area
        while ($elemento = readdir($dir))
        {
            // Descartamos los elementos . y .. que tienen todas las carpetas
            // Descartamos también todo lo que no sea una carpeta
            if($elemento == "." || $elemento == ".." || !is_dir(self::$appAreasUrl.$elemento))
            {
                continue;
            }
            
            self::$registeredPathAreas[$elemento] = self::$appAreasUrl.$elemento;
        }
    }
    
    private static function RegisterModels()
    {
        self::LoadFiles(self::$appBaseUrl."/Enums/");
        
        // Registering models for global application
        self::LoadFiles(self::$appBaseUrl."/Models/");
        
        // Registering models for each area
        foreach (self::$registeredPathAreas as $pathName => $pathArea)
        {
        	self::LoadFiles($pathArea."/Models/");
        }
    }
    
    /**
     * Register and load common helpers
     */
    private static function RegisterHelpers()
    {
        // Dependencies
        self::LoadFiles(self::$appBaseUrl."/Helpers/phpmailer/", array("PHPMailerAutoload.php"));
        self::LoadFiles(self::$appBaseUrl."/Helpers/phpmailer/language/");

        // International
        self::LoadFiles(self::$appBaseUrl."/Helpers/gettext/", array("gettext.php", "streams.php"));
        
        // International config
        $encoding = 'UTF-8';
        $defaultLocale = "es_ES";
        $localeDir = "./Application/i18n";
        $locale = (isset($_SESSION['LANG'])) ? $_SESSION['LANG'] : $defaultLocale;
        $domain = 'messages'; // Set the text domain as 'messages'

        // gettext setup
        T_setlocale(LC_MESSAGES, $locale);
        T_bindtextdomain($domain, $localeDir);
        T_bind_textdomain_codeset($domain, $encoding);
        T_textdomain($domain);

        header("Content-type: text/html; charset=$encoding");
        
        // Tools
        self::LoadFiles(self::$appBaseUrl."/Helpers/");
    }
    
    /**
     * Load all php files in path (Not subdirectories)
     * @param string $path 
     * @param array $exclude 
     */
    private static function LoadFiles($path, $exclude = array())
    {
        $dir = opendir($path);

        // Leo todos los ficheros de la carpeta
        while ($elemento = readdir($dir))
        {
            // Descartamos los elementos . y .. que tienen todas las carpetas
            // Descartamos también los directorios
            // Descartamos todos aquellos ficheros que no terminen en php
            if($elemento == "." || $elemento == ".." || is_dir($path.$elemento) || substr($elemento, strrpos($elemento, ".")) != ".php")
            {
                continue;
            }

            // Descartamos los excluidos
            if (count($exclude) > 0 && in_array($elemento, $exclude))
            {
                continue;
            }
            
            require_once $path.$elemento;
        }
    }
    #endregion
}
