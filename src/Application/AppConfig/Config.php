<?php

/**
 * Configuration
 * For more info about constants please @see http://php.net/manual/en/function.define.php
 * If you want to know why we use "define" instead of "const" @see http://stackoverflow.com/q/2447791/1114320
 */

if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
	/**
	 * Configuration for: Error reporting
	 * Useful to show every little problem during development, but only show hard errors in production
	 */
    
    // Define errors settings
	error_reporting(E_ALL);
	ini_set("display_errors", "1");

    // Define application settings
    define('APPLICATION_DEBUG', true);
    define('HOME_PATH', '/');
    define('DEFAULT_FROM', 'info@confetimail.net');

    /**
     * Configuration for: Database
     * This is the place where you define your database credentials, database type etc.
     */
    
    // Define db info
	define('DB_TYPE', 'mysql');
	define('DB_HOST', '127.0.0.1');
	define('DB_NAME', 'confetimail');
	define('DB_USER', 'root');
	define('DB_PASS', '');

	// Define mail info
    define('SMTP_DEBUG', 0); // 0 = None, 1 = Errors and messages, 2 = Messages only, 3-4 MORE INFO
    define('SMTP_SERVER', 'localhost');
    define('SMTP_PORT', 25);
    define('SMTP_USER', "info@confetimail.net");
    define('SMTP_PASS', "");
	define('SMTP_BACKUP_MAIL', 'confetimail@gmail.com');
	define('SMTP_HELO', 'localhost');

} else {
	/**
	 * Configuration for: Error reporting
	 * Useful to show every little problem during development, but only show hard errors in production
	 */
    
    // Define errors settings
	error_reporting(E_USER_NOTICE);
	ini_set("display_errors", "0");

    // Define application settings
    define('APPLICATION_DEBUG', false);
    define('HOME_PATH', './');
    define('DEFAULT_FROM', 'info@confetimail.net');

    /**
     * Configuration for: Database
     * This is the place where you define your database credentials, database type etc.
     */
    
    // Define db info
    define('DB_TYPE', 'mysql');
    define('DB_HOST', 'localhost');
	define('DB_NAME', 'confetimail');
	define('DB_USER', 'root');
    define('DB_PASS', '');

	// Define mail info
    define('SMTP_DEBUG', 0); // 0 = None, 1 = Errors and messages, 2 = Messages only, 3-4 MORE INFO
    define('SMTP_SERVER', 'mail.confetimail.net');
    define('SMTP_PORT', 25);
    define('SMTP_USER', 'info@confetimail.net');
    define('SMTP_PASS', '');
    define('SMTP_BACKUP_MAIL', 'confetimail@gmail.com');
    define('SMTP_HELO', 'www.confetimail.net');
}

/*
 * Configuration for: Root user
 * This is the place where you define your root user ID, he have full access, take care
 */

// Global settings
define('ROOT_USER', 1);
define('ADMIN_PASS', '');