<?php
// ===========================================================================================
//
// File: PFileUploadProcess.php
//
// Description: Upload and store files in the users file archive.
//
// Author: Mats Ljungquist
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
$intFilter->UserIsSignedInOrRecirectToSignIn();

// Get user-object
$uo = CUserData::getInstance();
$userId = $uo -> getId();

// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
$filename	= $pc->GETisSetOrSetDefault('file');
$filenames	= $pc->GETisSetOrSetDefault('filenames');
$referer	= $pc->GETisSetOrSetDefault('referer');
$ext	= $pc->GETisSetOrSetDefault('ext');

$account = $pc->SESSIONisSetOrSetDefault('accountUser');
$archivePath = FILE_ARCHIVE_PATH . DIRECTORY_SEPARATOR . $account . DIRECTORY_SEPARATOR . $filename . "." . $ext;

if (!is_file($archivePath)) die("The file does not exist in the file system.");

// Delete from database
$db = new CDatabaseController();
$mysqli = $db->Connect();

$udfDeleteFile = DBUDF_FileDelete;

// Create the query
$query 	= <<< EOD
SELECT {$udfDeleteFile}('{$filename}', '{$userId}') AS status;
EOD;

// Perform the query and manage results
$results = $db->Query($query);

$row = $results -> fetch_object();
if($row->status > 0) {
        $_SESSION['errorMessage'] = $row->status == 1 ? "No permission" : "File does not exist";
} else {
    // Delete from file system
    unlink($archivePath);
}

$results->close();
$mysqli->close();

$pc->RedirectTo($referer);

?>