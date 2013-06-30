<?php
// ===========================================================================================
//
// P404.php
//
// Generate a 404 header and print message, should also logg into database.
//
// Author: Mikael Roos, mos@bth.se
//


// -------------------------------------------------------------------------------------------
//
// Get pagecontroller helpers. Useful methods to use in most pagecontrollers
//
$pc = CPageController::getInstance();

// -------------------------------------------------------------------------------------------
//
// Interception Filter, controlling access, authorithy and other checks.
//
$intFilter = new CInterceptionFilter();

$intFilter->FrontControllerIsVisitedOrDie();

// -------------------------------------------------------------------------------------------
//
// Page specific code
//

$htmlMain = <<<EOD
<h1>404 Not Found</h1>
<p>
You have used a link that is not supported. The page you are trying to reach does not exist.
</p>
EOD;

// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

header("HTTP/1.0 404 Not Found");
$page->printPage('404 Not Found', "", $htmlMain, "");
exit;

?>