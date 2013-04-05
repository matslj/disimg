<?php
// ===========================================================================================
//
// PPageSave.php
//
// Saves an article to database
//
// Author: Mats Ljungquist

$log = CLogger::getInstance(__FILE__);

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
//$intFilter->UserIsMemberOfGroupAdminOrDie();


// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
$title		= $pc->POSTisSetOrSetDefault('title', 'No title');
$content	= $pc->POSTisSetOrSetDefault('content', 'No content');
$pageId         = $pc->POSTisSetOrSetDefault('page_id', 0);
$action		= $pc->POSTisSetOrSetDefault('action', '');
$success	= $pc->POSTisSetOrSetDefault('redirect_on_success', '');
$failure	= $pc->POSTisSetOrSetDefault('redirect_on_failure', '');
$userId		= $_SESSION['idUser'];
$log ->debug("title: " . $title . " content: " . $content . " id: " . $pageId . " action: " . $action . " success: " . $success . " failure: " . $failure . " userid: " . $userId);
// Always check whats coming in...
$pc->IsNumericOrDie($pageId, 0);

// Clean up HTML-tags
$tagsAllowed = '<h1><h2><h3><h4><h5><h6><p><a><br><i><em><li><ol><ul>';
$title 		= strip_tags($title, $tagsAllowed);
$content 	= strip_tags($content, $tagsAllowed);

// -------------------------------------------------------------------------------------------
//
// Create a new database object, connect to the database, get the query and execute it.
// Relates to files in directory TP_SQLPATH.
//
$db 	= new CDatabaseController();
$mysqli = $db->Connect();

// Get the SP names
$spPInsertOrUpdateSida			= DBSP_PInsertOrUpdateSida;

// Create the query
$query = <<< EOD
SET @aPageId = {$pageId};
CALL {$spPInsertOrUpdateSida}(@aPageId, '{$userId}', '', '{$title}', '{$content}');
SELECT
    @aPageId AS id,
    NOW() AS timestamp
;
EOD;

// Perform the query
$res = $db->MultiQuery($query);

// Use results
$results = Array();
$db->RetrieveAndStoreResultsFromMultiQuery($results);

// Store inserted/updated article id
$row = $results[2]->fetch_object();
$pageId = $row->id;
$timestamp = $row->timestamp;

$results[2]->close();
$mysqli->close();

// -------------------------------------------------------------------------------------------
//
// Redirect to another page
// Support $redirect to be local uri within site or external site (starting with http://)
//
//$log -> debug($success);
if(strcmp($success, 'json') == 0) {
    $json = <<<EOD
{
	"pageId": {$pageId},
        "timestamp": "{$timestamp}",
        "action": "{$action}",
        "title": "{$title}",
        "content": "{$content}"
}
EOD;
    echo $json;
} else {
    $log -> debug($success);
    CPageController::RedirectTo($success);
}
exit;

?>