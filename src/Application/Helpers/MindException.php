<?php
class MindException extends Exception
{
	function __construct($message = null)
    {
		parent::__construct($message, 0, null);
	}
}
?>