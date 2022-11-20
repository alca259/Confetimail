<?php
class JsonHandler
{

	protected static $_messages = array(
		JSON_ERROR_NONE => 'No error has occurred',
		JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
		JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
		JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
		JSON_ERROR_SYNTAX => 'Syntax error',
		JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
	);

	public static function NormalEncode($value, $options = 0)
    {
		$result = json_encode($value, $options);
		if($result) 
        {
			return $result;
		}

		throw new RuntimeException(static::$_messages[json_last_error()]);
	}

	public static function NormalDecode($json, $assoc = false)
    {
        if ($json == "{}") return "";

		$result = json_decode($json, $assoc);

		if($result)
        {
			return $result;
		}

		throw new RuntimeException(static::$_messages[json_last_error()]);
	}

    private static function GetUrlVars($url)
    {
        $myJson = array();
        $hashes = explode("&", $url);
        //echo $url;
        for ($i = 0; $i < count($hashes); $i++)
        {
            $hash = explode("=", $hashes[$i]);
            if (!empty($hash))
            {
                //print_r($hash);
                $myJson[$hash[0]] = $hash[1];
            }
        }
        return $myJson;
    }

    public static function UrlDecode($url)
    {
        $data = static::GetUrlVars($url);

        if (!$data)
        {
            return false;
        }

        $data = static::normalEncode($data);
        $result = json_decode($data);

        if($result)
        {
            return $result;
        }

        throw new RuntimeException(static::$_messages[json_last_error()]);
    }

	public static function NormalIsJSON($string)
    {
		return is_string($string) && is_object(json_decode($string)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}

	public static function Encode($value, $options = 0)
    {
		$result = json_encode($value, $options);
		if($result)
        {
			return str_replace("'", '\\\'', $result);
		}

		throw new RuntimeException(static::$_messages[json_last_error()]);
	}

	public static function Decode($json, $assoc = false)
    {
		$json = str_replace('\\\'', "'", $json);
		$result = json_decode($json, $assoc);

		if($result)
        {
			return $result;
		}

		throw new RuntimeException(static::$_messages[json_last_error()]);
	}

	public static function IsJSON($string)
    {
		$string = str_replace('\\\'', "'", $string);
		return is_string($string) && is_object(json_decode($string)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}
}
?>