<?php
session_name("confetimailSID");
session_start();

require_once './Application/AppConfig/GlobalConfig.php';

// Check language select
if (isset($_REQUEST['lang']) && in_array($_REQUEST['lang'], GlobalConfig::$supportedLocales))
{
    $_SESSION['LANG'] = $_REQUEST['lang'];
}

// Configure and load application
GlobalConfig::Configure();
// Start the application
$app = new Application();
?>