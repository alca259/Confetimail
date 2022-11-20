<?php
abstract class BasicEnum {
    private static $constCacheArray = NULL;

    private static function GetConstants()
    {
        if (self::$constCacheArray == NULL)
        {
            self::$constCacheArray = array();
        }
        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, self::$constCacheArray))
        {
            $reflect = new ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->GetConstants();
        }
        return self::$constCacheArray[$calledClass];
    }

    public static function IsValidName($name, $strict = false)
    {
        $constants = self::GetConstants();

        if ($strict)
        {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys);
    }

    public static function IsValidValue($value)
    {
        $values = array_values(self::GetConstants());
        return in_array($value, $values, true);
    }
}
?>