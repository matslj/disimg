<?php

// ===========================================================================================
//
// config.php
//
// Website specific configurations.
// Navigation in separate config since it more varyproning
//

// -------------------------------------------------------------------------------------------
//
// Settings for this website (WS), some used as default values in CHTMPLPage.php
//
// PERHAPS SPLIT THIS WHEN UPDATING CHTMLPAGE
//
define('WS_MELINK',   'http://localhost/dbwebb2/me/'); // Link to me pages.
define('WS_SITELINK',   'http://localhost/disimg/'); // Link to site.
define('WS_TITLE', 	'DisImg');		    // The title of this site.
define('WS_SUB_TITLE',  'Disas album');
define('WS_STYLESHEET', 'style/plain/stylesheet_liquid.css');	// Default stylesheet of the site.
define('WS_FAVICON', 	'img/favicon.ico'); // Small icon to display in browser
define('WS_FOOTER', 	'DisImg &copy; 2012 by Mats Ljungquist Home Copyrights Privacy About');	// Footer at the end of the page.
define('WS_VALIDATORS', TRUE);	            // Show links to w3c validators tools.
define('WS_TIMER', 	TRUE);              // Time generation of a page and display in footer.
define('WS_CHARSET', 	'utf-8');           // Use this charset
define('WS_LANGUAGE', 	'sv');              // Default language
define('WS_IMAGES',			WS_SITELINK . 'img/');
define('WS_JAVASCRIPT',	WS_SITELINK . 'js/');	// JavaScript code

define('WS_HOME',	'home');	// Starting page

// The logging system
// Available logging modes are:
// * 'file' - saves to a log file in TP_LOGPATH. Ensure writing rights to that path.
// * 'dummy' - means no logging.
// * No logger (commented away) - the same as 'dummy'
define('WS_LOGGER',	'file');

// The system captcha
// Available captchas are:
// * 'securimage' - use the securimage captcha.
// * 'dummy' - means no captcha.
// * No captcha (commented away) - the same as 'dummy'
define('WS_CAPTCHA',	'securimage');

// File handling
define('FILE_ARCHIVE_FOLDER', 'uploads');
//define('FILE_ARCHIVE_PATH', 'c:\wamp\www\disimg\\' . FILE_ARCHIVE_FOLDER); // Must be writable by webserver
define('FILE_ARCHIVE_PATH', '/var/www/disimg/' . FILE_ARCHIVE_FOLDER); // Must be writable by webserver
define('FILE_MAX_SIZE', 6000000); // Filesize in bytes
define('FILE_ACTION' , '?p=uploadp');	// JavaScript code

// -------------------------------------------------------------------------------------------
//
// Settings for commonly used external resources, for example javascripts.
//
define('JS_JQUERY', WS_JAVASCRIPT . 'jquery/jquery-1.7.2.min.js');

define('JS_JQUERY_UI_THEME', 'sunny'); // This is the theme currently used.
define('JS_JQUERY_UI_CSS', WS_JAVASCRIPT . 'jquery-ui/' . JS_JQUERY_UI_THEME . '/jquery-ui-1.9.2.custom.css');

// -------------------------------------------------------------------------------------------
//
// Define the navigation menu.
//
require_once('config_nav.php');

// -------------------------------------------------------------------------------------------
//
// Settings for the template (TP) structure, show where everything are stored.
// Support for storing in directories, no need to store everything under one directory
//
define('TP_ROOT',		dirname(__FILE__) . DIRECTORY_SEPARATOR);	// The root of installation
define("TP_SOURCEPATH",		dirname(__FILE__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR);	// Classes, functions, code
define('TP_PAGESPATH',		dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR);	// Pagecontrollers and modules
define('TP_LANGUAGEPATH',	dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR);	// Multi-language support
define('TP_SQLPATH',		dirname(__FILE__) . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR);	// SQL code
define('TP_LOGPATH',		dirname(__FILE__) . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR);	// log

?>