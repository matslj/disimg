<?php
// ===========================================================================================
//
// PAbout.php
//
// Display information about Persia, display the README-file.
//
// Author: Mats Ljungquist
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
$intFilter->UserIsSignedInOrRecirectToSignIn();
$intFilter->UserIsMemberOfGroupAdminOrDie();


// -------------------------------------------------------------------------------------------
//
// Page specific code
//
$readme = file_get_contents('README.md');
$readme = htmlspecialchars($readme);
$htmlMain = <<<EOD
EOD;

$htmlMain = <<<EOD
<h1>About DisImg</h1>
<h2>README</h2>
<p>
This is the README-file.
</p>
<p>
<pre>
{$readme}
</pre>
</p>
EOD;

$htmlLeft = "";
$htmlRight = "";


// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

$page->printPage('About DisImg', $htmlLeft, $htmlMain, $htmlRight);
exit;

?>