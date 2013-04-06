<?php
// ===========================================================================================
//
// PIndex.php
//
// A WYSIWYG editor
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
$img = WS_IMAGES;

$redirect = $pc->computeRedirect();
// $urlToEditPost = "?p=page-edit{$redirect}&amp;page-id=";

// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
$pageId = 0;
$userId	= isset($_SESSION['idUser']) ? $_SESSION['idUser'] : "";

// -------------------------------------------------------------------------------------------
//
// Create a new database object, connect to the database, get the query and execute it.
// Relates to files in directory TP_SQLPATH.
//
$pageName = basename(__FILE__);

$needjQuery = TRUE;
$htmlHead = "";
$javaScript = "";

$titleLink 	= "";
$title          = "";
$content 	= "";
$isEditable     = "";

// Connect
$db 	= new CDatabaseController();
$mysqli = $db->Connect();

// Get the SP names
$spGetSidaDetails	= DBSP_PGetSidaDetails;

$query = <<< EOD
CALL {$spGetSidaDetails}('$pageName');
EOD;

// Perform the query
$results = Array();
$res = $db->MultiQuery($query);
$db->RetrieveAndStoreResultsFromMultiQuery($results);

// Get article details
$row = $results[0]->fetch_object();
if ($row) {
    $pageId     = $row->id;
    $title      = $row->title;
    $content    = $row->content;
    // $titleLink = ($intFilter->IsUserMemberOfGroupAdmin()) ? "<a title='Ändra inlägg' href='{$urlToEditPost}{$row->id}'>$row->title</a>" : $row->title;
}
$results[0]->close();
$mysqli->close();

$htmlPageTitleLink = "";
$htmlPageContent = "";
$htmlPageTextDialog = "";

if ($pageId != 0) {
    require_once(TP_PAGESPATH . 'page/PPageEditDialog.php');
}

// -------------------------------------------------------------------------------------------
//
// Page specific code
//
$htmlMain = <<<EOD
<h1>{$htmlPageTitleLink}</h1>
{$htmlPageContent}
{$htmlPageTextDialog}
EOD;

$htmlLeft 	= "";
$htmlRight	= "";

// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

$page->printPage('DisImg - Ett bildarkiv', $htmlLeft, $htmlMain, $htmlRight, $htmlHead, $javaScript, $needjQuery);
exit;

?>