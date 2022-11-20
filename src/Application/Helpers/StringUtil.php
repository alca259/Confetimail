<?php
class StringUtil
{
    public static function StartsWith($haystack, $needle)
    {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    public static function EndsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    /* random string */
    public static function RandString($length)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $size = strlen( $chars );
        $str = "";
        for( $i = 0; $i < $length; $i++ )
        {
            $str .= $chars[ rand( 0, $size - 1 ) ];
        }
        return $str;
    }
    
    public static function UrlAction($actionName, $controllerName, $areaName = "")
    {
        $url = $areaName != "" ? sprintf("%s%s", "/", $areaName) : "";
        return sprintf("%s/%s/%s", $url, $controllerName, $actionName);
    }
}
?>