<?php

/**
 * Constants short summary.
 *
 * Constants description.
 *
 * @version 1.0
 * @author Mod
 */
class Constants
{
    // Areas
    public static $PanelAreaName = "Panel";
    
    // Actions
    public static $IndexName = "Index";
    
    public static $UploadFilesUrl = './Public/files/';
    public static $UploadFilesRelativeUrl = 'files/';
    public static $UploadImagesUrl = './Public/img/uploaded/';
    public static $UploadImagesRelativeUrl = 'uploaded/';
    public static $UploadUserImagesUrl = './Public/img/users/';
    
    public static $ViewImagesFolder = '/Public/img/';
    
    public static function GetMailImagesPath()
    {
        if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1')
        {
            return sprintf("http://%s", "confetimail-webpage");
        }
        
        return sprintf("http://%s", "www.confetimail.net");
    }
}