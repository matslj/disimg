<?php
// ===========================================================================================
//
// PInstallProcess.php
//
// Executes SQL statments in database, displays the results.
//
// Author: Mikael Roos
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
// Prepare the text
//
$htmlMain = <<< EOD
<h1>Database installed</h1>
EOD;

$htmlLeft 	= "";
$htmlRight	= "";

// -------------------------------------------------------------------------------------------
//
// Create a new database object, connect to the database, get the query and execute it.
//
$db 	= new CDatabaseController();
$mysqli = $db->Connect();

// -------------------------------------------------------------------------------------------
//
// Execute several queries and print out the result.
//
$queries = Array('SQLCreateUserAndGroupTables.php', 'SQLCoreFile.php', 'SQLCreateImageAndPageTables.php');

foreach($queries as $val) {

	$query 	= $db->LoadSQL($val);
	$res 	= $db->MultiQuery($query);
	$no	= $db->RetrieveAndIgnoreResultsFromMultiQuery();
        
        $errorStyle = $mysqli->errno == 0 ? "green" : "red";

	$htmlMain .= <<< EOD
<h3>SQL Query '{$val}'</h3>
<p>
<div class="sourcecode">
<pre>{$query}</pre>
</div>
</p>
<p style="font-weight: bold; color:{$errorStyle};">**** Statements that succeeded: {$no} ****</p>
<p style="font-weight: bold; color:{$errorStyle};">**** Error code: {$mysqli->errno} ({$mysqli->error}) ****</p>
EOD;
}


// -------------------------------------------------------------------------------------------
//
// Close the connection to the database
//
$mysqli->close();


// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

$page->printPage('Database installed', $htmlLeft, $htmlMain, $htmlRight);
exit;

?>