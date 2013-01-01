<?php
// ===========================================================================================
//
// PAdminIndex.php
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
$intFilter->UserIsSignedInOrRecirectToSignIn();
$intFilter->UserIsMemberOfGroupAdminOrDie();
$img = WS_IMAGES;

$redirect = $pc->computeRedirect();
$urlToEditPost = "?p=page-edit{$redirect}&amp;page-id=";

// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
$sidaId	= $pc->GETisSetOrSetDefault('sida-id', 1);
$userId	= isset($_SESSION['idUser']) ? $_SESSION['idUser'] : "";

// Always check whats coming in...
$pc->IsNumericOrDie($sidaId, 0);

// -------------------------------------------------------------------------------------------
//
// Create a new database object, connect to the database, get the query and execute it.
// Relates to files in directory TP_SQLPATH.
//
$pageName = basename(__FILE__);

$title 		= "";
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
    $content    = $row->content;
    $title = ($intFilter->IsUserMemberOfGroupAdmin()) ? "<a title='Ändra inlägg' href='{$urlToEditPost}{$row->id}'>$row->title</a>" : $row->title;
}

$results[0]->close();
$mysqli->close();

// -------------------------------------------------------------------------------------------
//
// Page specific code
//
$htmlMain = <<<EOD
<h1>{$title}</h1>
<p>
{$content}
</p>
EOD;

$htmlRight	= "";

// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

// Creating the left menu panel
$htmlLeft = "<div id='navigation'>" . $page ->PrepareLeftSideNavigationBar(ADMIN_MENU_NAVBAR) . "</div>";

$page->printPage('Admin', $htmlLeft, $htmlMain, $htmlRight);
exit;

?>