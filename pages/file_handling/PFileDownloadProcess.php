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


// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
$file	 = $pc->GETisSetOrSetDefault('file');
$referer = $pc->GETisSetOrSetDefault('referer');

// -------------------------------------------------------------------------------------------
//
// Get file details/metadata from database
//
$db 	= new CDatabaseController();
$mysqli = $db->Connect();

$spFileDownload = DBSP_FileDetails;

// Create the query
$file 	= $mysqli->real_escape_string($file);
$query 	= <<< EOD
CALL {$spFileDownload}(NULL, '{$file}', @success);
SELECT @success AS success;
EOD;

// Perform the query
$res = $db->MultiQuery($query);
// Use results
$results = Array();
$db->RetrieveAndStoreResultsFromMultiQuery($results);

$row = $results[2]->fetch_object();

// If file is not valid then redirect to 403 with a message
if($row->success > 1) {
    $_SESSION['errorMessage'] = $row->status == 1 ? "No permission" : "File does not exist";
    $pc->RedirectTo($referer);
}

$row = $results[0]->fetch_object();
$name 		= $row->name;
$path 		= $row->path;
$size 		= $row->size;
$mimetype 	= $row->mimetype;
$created 	= $row->created;
$modified 	= $row->modified;

$results[2]->close();
$results[0]->close();
$mysqli->close();
// The file must exist, else redirect to 404
if(!is_readable($path)) {
	$_SESSION['errorMessage'] = "File does not exist on the file system";
        $pc->RedirectTo($referer);
}


// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
header("Content-type: {$mimetype}");
header("Content-Disposition: attachment; filename=\"{$name}\"");
readfile($path);
exit;

?>