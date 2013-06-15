<?php
// ===========================================================================================
//
// File: PFileUpload.php
//
// Description: Various samples of uploading files.
//
// Author: Mikael Roos, mos@bth.se
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


// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
//$articleId	= $pc->GETisSetOrSetDefault('article-id', 0);
//$userId		= $_SESSION['idUser'];

// Always check whats coming in...
//$pc->IsNumericOrDie($articleId, 0);


// Link to images
$imageLink = WS_IMAGES;
$attachment = new CAttachment();

// -------------------------------------------------------------------------------------------
//
// Add JavaScript and html head stuff related to JavaScript
//
$js = WS_JAVASCRIPT;
$needjQuery = TRUE;
$htmlHead = $attachment -> getHead();
$javaScript = $attachment -> getJavaScript();


// -------------------------------------------------------------------------------------------
//
// Page specific code
//

$maxFileSize 	= FILE_MAX_SIZE;
$action 	= "?p=uploadp";
$redirect 	= "?p=upload";
$redirectFail   = "?p=upload";

$htmlMain = <<<EOD
<div class='section'>
<h1>Sample file uploads</h1>
<p>
Each file you upload will be visible in the 'Archive'.
</p>
</div>

<div class='section'>
<p>
This is a Ajax-enabled form for file upload. It uses jQuery form plugin as described here: 
<a href='http://jquery.malsup.com/form/#file-upload'>http://jquery.malsup.com/form/#file-upload</a>.
</p>
{$attachment -> getAsHTML()}
</div>
EOD;

$htmlLeft 	= "";
$htmlRight	= <<<EOD
<h3 class='columnMenu'></h3>
<p>
Later...
</p>

EOD;


// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

$page->printPage('Edit article', $htmlLeft, $htmlMain, $htmlRight, $htmlHead, $javaScript, $needjQuery);
exit;

?>