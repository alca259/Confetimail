<?php

/**
 * View short summary.
 *
 * This class search views for model
 * First in areas folder, if not exist in views and shared folders
 * -> 1 - Areas/<Area name>/Views/<Controller name>/<ActionName>(.html|.php)
 * -> 2 - Areas/<Area name>/Views/Shared/<ActionName>(.html|.php)
 * -> 3 - Views/<Controller name>/<ActionName>(.html|.php)
 * -> 4 - Views/Shared/<ActionName>(.html|.php)
 * -> 5 - 404.html
 *
 * @version 1.0
 * @author Mod
 */
class View
{
    #region Private vars
    private $baseFolder = "Application";
    private $areaBaseFolder = "";
    private $sharedFolderName = "Shared";
    private $viewFolderName = "Views";
    
    private $layoutTemplatePath = "Views/Shared/_Layout.html";
    private $layoutAdminTemplatePath = "Views/Shared/_LayoutAdmin.html";
    private $page404Path = "404.html";
    #endregion
    
    #region Constructors
    /**
     * Create a new view
     * @param string $actionName 
     * @param string $controllerName 
     * @param array $ViewBag 
     * @param string $areaName (Optional)
     * @param bool $isAdmin (Optional, default false)
     */
    public function __construct($actionName, $controllerName, $ViewBag, $areaName = "", $isAdmin = false, $noLayout = false)
    {
        $this->areaBaseFolder = $this->baseFolder."/Areas";

        try
        {
        	return $this->LoadView($this->FindViewPath($actionName, $controllerName, $areaName), $controllerName, $areaName, $ViewBag, $isAdmin, $noLayout);
        }
        catch (MindException $exception)
        {
            // Page not found, return 404 error
            return $this->LoadView($this->page404Path, $controllerName, $areaName, $ViewBag, $isAdmin, true);
        }
    }
    #endregion
    
    #region Private methods
    /**
     * Busca una vista en varias carpetas
     * @param string $actionName 
     * @param string $controllerName 
     * @param string $areaName 
     * @throws MindException Si no encuentra la ruta, lanza excepcion
     * @return string Path file
     */
    private function FindViewPath($actionName, $controllerName, $areaName = "")
    {
        if ($actionName == "")
        {
            // 5 - Page not found, return 404 error
            throw new MindException(T_("Page not found"));
        }
        
        if (file_exists($this->areaBaseFolder) && is_dir($this->areaBaseFolder) && $areaName != "")
        {
            // Areas folder exists, browsing view inside controller folders
            if ($controllerName != "")
            {
                $file = $this->ExistFile($this->areaBaseFolder."/".$areaName."/".$this->viewFolderName."/".$controllerName."/".$actionName);
            
                if ($file != false) 
                {
                    // 1 - Action view found at controller folder
                    return $file;
                }
            }
            
            // We check Shared folder of area
            $file = $this->ExistFile($this->areaBaseFolder."/".$areaName."/".$this->viewFolderName."/".$this->sharedFolderName."/".$actionName);
            
            if ($file != false) 
            {
                // 2 - Action view found at shared folder
                return $file;
            }
        }
        
        if ($controllerName != "")
        {
            // Browsing in root path if view is inside
            $file = $this->ExistFile($this->baseFolder."/".$this->viewFolderName."/".$controllerName."/".$actionName);
        
            if ($file != false) 
            {
                // 3 - Action view found at controller folder
                return $file;
            }
        }
        
        // We check Shared folder of area
        $file = $this->ExistFile($this->baseFolder."/".$this->viewFolderName."/".$this->sharedFolderName."/".$actionName);
        
        if ($file != false) 
        {
            // 4 - Action view found at shared folder
            return $file;
        }
        
        // 5 - Page not found, return 404 error
        throw new MindException(T_("Page not found"));
    }
    
    /**
     * Render a full view
     * @param mixed $filepath 
     * @param mixed $controllerName 
     * @param mixed $areaName 
     * @param mixed $ViewBag 
     * @param mixed $isAdmin 
     * @param mixed $isError 
     * @return mixed
     */
    private function LoadView($filepath, $controllerName, $areaName, $ViewBag, $isAdmin = false, $isError = false, $noLayout = false)
    {
        if ($isError) 
        {
            require_once($filepath);
            return false;
        }
        
        if ($noLayout)
        {
            require_once($filepath);
            return false;
        }
        
        // We looking for master layout page
        $template_contents = $isAdmin
            ? file_get_contents($this->baseFolder."/".$this->layoutAdminTemplatePath)
            : file_get_contents($this->baseFolder."/".$this->layoutTemplatePath);
        
        // If not found, return 404 error
        if (!$template_contents)
        {
            require_once($this->page404Path);
            return false;
        }
        
        // Looking for RenderBody tag
        $renderBodyTag = "{{ RenderBody }}";
        $renderTitleTag = "{{ Title }}";
        $renderBaseUrlTag = "{{ BaseUrl }}";
        $regexPartialTag = '/\{\{ RenderPartial\(\"(.*?)\"\) \}\}/s';
        
        $file_contents = file_get_contents($filepath);
        $max_replaces = 1;
        
        // Override tags
        $data = str_replace($renderBodyTag, $file_contents, $template_contents, $max_replaces);
        $data = str_replace($renderTitleTag, T_($ViewBag->Title), $data);
        //$data = str_replace($renderBaseUrlTag, URL, $data);
        
        /* Render partials */
        preg_match_all($regexPartialTag, $data, $matches);
        
        for ($i = 0; $i < count($matches[1]); $i++)
        {
            // Only one level of partial render is allowed
            $viewPartialName = $matches[1][$i];

            // Find the view or die with exception
            $partialFile = $this->FindViewPath($viewPartialName, $controllerName, $areaName);
            
            // We override match with content of partial
            $data = preg_filter($regexPartialTag, $this->LoadPartial($partialFile), $data, 1);
        }
        
        return $this->RequireEval($data, $ViewBag);
    }
    
    /**
     * Load a partial
     * @param string $partialFile 
     * @return string
     */
    private function LoadPartial($partialFile)
    {
        return file_get_contents($partialFile);
    }
    
    /**
     * Eval a mix code html and php and generate a temp file for include
     * @param string $phpCode 
     * @param Dictionary $ViewBag 
     * @return array of vars
     */
    private function RequireEval($phpCode, $ViewBag) {
        // Set dir and prefix for file
        $tmpfname = tempnam("/tmp", "RequireEval");
        // Open it
        $handle = fopen($tmpfname, "w+");
        // Write code
        fwrite($handle, $phpCode);
        // Close file
        fclose($handle);
        // Include file
        require_once($tmpfname);
        // Delete file
        unlink($tmpfname);
        // Return all defined vars
        return get_defined_vars();
    }
    
    /**
     * Check if file is in path and return it, if not found, return false
     * @param string $path 
     * @return mixed
     */
    private function ExistFile($path)
    {
        $allowed_extensions = array(".html", ".php");
        
        foreach ($allowed_extensions as $ext)
        {
            // We check if exist file with extension
            if (!file_exists($path.$ext))
            {
                continue;
            }
            
            // If found it, we return it
            return $path.$ext;
        }
        
        return false;
    }
    #endregion
}

?>