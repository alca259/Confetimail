<?php
class ORMException extends Exception
{
    function __construct($message = "")
    {
        parent::__construct($message, 0, NULL);
    }
}
?>