<?php

class Dictionary extends ArrayObject
{
    public function __set($name, $val)
    {
        $this[$name] = $val;
    }

    public function __get($name)
    {
        return isset($this[$name]) ? $this[$name] : "";
    }
}
