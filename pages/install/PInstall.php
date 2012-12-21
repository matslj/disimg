<?php
// ===========================================================================================
//
// PInstall.php
//
// Info page for installation. Links to page for creating tables in the database.
//


// -------------------------------------------------------------------------------------------
//
// Get pagecontroller helpers. Useful methods to use in most pagecontrollers
//
$pc = CPageController::getInstance();
//$pc->LoadLanguage(__FILE__);


// -------------------------------------------------------------------------------------------
//
// Interception Filter, controlling access, authorithy and other checks.
//
$intFilter = new CInterceptionFilter();

$intFilter->FrontControllerIsVisitedOrDie();
//$intFilter->UserIsSignedInOrRecirectToSignIn();
//$intFilter->UserIsMemberOfGroupAdminOrDie();


// -------------------------------------------------------------------------------------------
//
// Page specific code
//
require_once(TP_SQLPATH . 'config.php');

$host		= DB_HOST;
$database 	= DB_DATABASE;
$prefix		= DB_PREFIX;

$htmlMain = <<<EOD
<h1>Install database</h1>
<p>
Click below link to remove all contents from the database and create new tables and content from
scratch.
</p>
<p>
Database host: '{$host}'
</p>
<p>
Database name: '{$database}'
</p>
<p>
Prefix for tables: '{$prefix}'
</p>
<p>
Update the database config-file (usually sql/config.php) to change the values.
</p>
<p>
&not; <a href='?p=installp'>Destroy current database and create from scratch</a>
</p>
EOD;

$htmlLeft 	= "";
$htmlRight 	= "";


// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

$page->printPage('Install database', $htmlLeft, $htmlMain, $htmlRight);
exit;


?>