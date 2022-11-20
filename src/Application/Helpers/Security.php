<?php
class Security
{
	/**
	 * Check if user is logged
	 */
	public static function IsOnline()
    {
		if (isset($_SESSION['GUID']) && $_SESSION['GUID'] != '')
        {
			return true;
		}
		return false;
	}

	public static function IsAuthorizedAdmin() {
		if (isset($_SESSION['GUID']) && $_SESSION['GUID'] != '' && isset($_SESSION['ROLE']) && $_SESSION['ROLE'] == 1)
        {
			return true;
		}
		return false;
	}

	public static function GetUser()
    {
		if (self::isOnline())
        {
			return $_SESSION['GUID'];
		}
		return false;
	}

	public static function LoginUser($user)
    {
		$_SESSION['GUID'] = $user['id'];
		$_SESSION['ROLE'] = ($user['admin'] != '' ? $user['admin'] : 0);
	}

	public static function LogoutUser()
    {
		$_SESSION['GUID'] = null;
		$_SESSION['ROLE'] = null;
        $_SESSION['LAST_LOGIN'] = null;
		session_destroy();
	}

	public static function VerifyAjax($serverMethod)
    {
		if($serverMethod == "GET")
        {
			return 0;
		}
		return 1;
	}
}
?>