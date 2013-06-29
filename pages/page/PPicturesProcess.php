<?php
// ===========================================================================================
//
// PPictureManagerProcess.php
//
// This process adds or deletes user interest in a folder.
//
// @author Mats Ljungquist
//

// $log = logging_CLogger::getInstance(__FILE__);

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
$intFilter->UserIsSignedInOrRecirectToSignIn();

// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
$fileId     = $pc->POSTisSetOrSetDefault('fileid', 0);
$userId     = $pc->POSTisSetOrSetDefault('userid', 0);
$action     = $pc->POSTisSetOrSetDefault('action', '');

// Check incoming data
$pc->IsNumericOrDie($fileId, 0);
$pc->IsNumericOrDie($userId, 0);

// Create database object (to get the required sql-config)
$db = new CDatabaseController();
$mysqli = $db->Connect();

$query = '';

// Kolla vilken action som gäller och definiera query utifrån detta
if (strcmp($action, 'add') == 0) {
    $spInsertBildIntresse = DBSP_PInsertBildIntresse;
    $query = "CALL {$spInsertBildIntresse}({$userId},{$fileId});";
} else if (strcmp($action, 'delete') == 0) {
    $spDeleteBildIntresse = DBSP_PDeleteBildIntresse;
    $query = "CALL {$spDeleteBildIntresse}({$userId},{$fileId});";
} else {
    die("Bad command. Very bad.");
}

// Errors exist - Exit back to the userlist page
if (!empty($_SESSION['errorMessage'])) {
    $pc->RedirectTo($pc->POSTisSetOrSetDefault('redirect'));
    exit;
}

// Perform the query
$res = $db->MultiQuery($query);
if ($res != null && $res != false) {
    // Ignore results but count successful statements.
    $nrOfStatements = $db->RetrieveAndIgnoreResultsFromMultiQuery();
    if($nrOfStatements != 1) {
        $_SESSION['errorMessage']	= "Fel: kunde inte registrera/avregistrera din markering";
    }
}

$mysqli->close();

?>