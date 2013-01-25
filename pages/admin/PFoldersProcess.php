<?php
// ===========================================================================================
//
// PFoldersProcess.php
//
// Create/delete folder
// 
// @author Mats Ljungquist
//

// $log = CLogger::getInstance(__FILE__);

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
// Check so that logged in user is admin
$intFilter->IsUserMemberOfGroupAdminOrTerminate();

// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
$folderId 		= $pc->POSTisSetOrSetDefault('folderid', 0);
$folderName 	= $pc->POSTisSetOrSetDefault('foldername', '');
$action	= $pc->POSTisSetOrSetDefault('action', '');

// Check incoming data
$pc->IsNumericOrDie($folderId, 0);

// Create database object (to get the required sql-config)
$db = new CDatabaseController();
$mysqli = $db->Connect();

// Sanitize data
if ($folderName != null) {
    $folderName = $mysqli->real_escape_string($folderName);
}

$query = '';

// Kolla vilken action som gäller och definiera query utifrån detta
if (strcmp($action, 'create') == 0) {
    if (empty($folderName)) {
        $_SESSION['errorMessage'] = "Fel: katalogen måste ha ett namn";
    }
    $spCreateFolder = DBSP_InsertFolder;
    $query = "CALL {$spCreateFolder}('{$folderName}');";
} else if (strcmp($action, 'delete') == 0) {
    $udfDeleteFolder = DBUDF_FolderDelete;
    $query = "SELECT {$udfDeleteFolder}({$folderId}) AS status;";
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
if ($res != null) {
    // Ignore results but count successful statements.
    $nrOfStatements = $db->RetrieveAndIgnoreResultsFromMultiQuery();

    // Kolla vilken action som gäller och kolla hur det gick utfrån detta
    if (strcmp($action, 'create') == 0) {
        if($nrOfStatements != 1) {
            $_SESSION['errorMessage']	= "Fel: kunde inte skapa katalog";
        }
    } else if (strcmp($action, 'delete') == 0) {
        $results = Array();
        $db->RetrieveAndStoreResultsFromMultiQuery($results);
        $row = $results[0]->fetch_object();
        if ($row->status == 1) {
            $_SESSION['errorMessage']	= "Fel: katalogen innehåller bilder och kan därför inte raderas. Radera bilderna i katalogen först.";
        }
        $results[0] -> close();
    }
}

$mysqli->close();

// -------------------------------------------------------------------------------------------
//
// Redirect to another page
//
$pc->RedirectTo($pc->POSTisSetOrSetDefault('redirect'));
exit;

?>