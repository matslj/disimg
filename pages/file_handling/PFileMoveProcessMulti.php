<?php
// ===========================================================================================
//
// File: PFileUploadProcess.php
//
// Description: Upload and store files in the users file archive.
//
// Author: Mats Ljungquist
//

$log = logging_CLogger::getInstance(__FILE__);
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
$filenames	= $pc->POSTisSetOrSetDefault('filenames');
$folderId	= $pc->GETisSetOrSetDefault('folderid');
$action         = $pc->POSTisSetOrSetDefault('action');

$log -> debug(print_r($filenames, true));

if (!is_numeric($folderId)) {
    die("Error: Parameter -folerid- is not a number");
}
if (!(is_array($filenames) && count($filenames) >= 1)) {
    die ("Error: Parameter -filenames- is not an array");
}

$max = count($filenames);

// Prepare for database access
$db = new CDatabaseController();
$mysqli = $db->Connect();

// ****************************************************
// **    Iterate over the selected files and change
// **    their folder ( a file can only belong to one
// **    folder.
// **

// Get db-function name
$udfMoveFile = DBUDF_FileUpdateFolder;

$log -> debug("där och max: " . $max);

// Loop over the unique names in the array
for ($i = 0; $i < $max; ++$i) {
    $index1 = strpos($filenames[$i], "#");
    $index2 = strpos($filenames[$i], "#", $index1 + 1);
    $filename = substr($filenames[$i], $index1 +1 , $index2 - $index1 - 1);
    
    // Create the query
    $query 	= <<< EOD
    SELECT {$udfMoveFile}('{$filename}', '{$userId}', {$folderId}) AS status;
EOD;

    // Perform the query and manage results
    $results = $db->Query($query);

    $row = $results -> fetch_object();
    if($row->status > 0) {
            $_SESSION['errorMessage'] = $row->status == 1 ? "No permission" : "File does not exist";
    }
    
    $results->close();
}

// ****************************************************
// **     Lastly, get folder name and facets
// **     This is needed for updating the gui.
// **
$spDetailFolder = DBSP_DetailFolder;
$queryFolder 	= <<< EOD
CALL {$spDetailFolder}({$folderId});
EOD;
$res = $db->MultiQuery($queryFolder);

// Use results
$results = Array();
$db->RetrieveAndStoreResultsFromMultiQuery($results);
$row = $results[0]->fetch_object();
$folderName = $row -> name;
$facet = $row -> facet;
$results[0]->close();

// ****************************************************
// **     Close DB-connection and return dat needed
// **     for updating the gui asynchroniously
// **
$mysqli->close();

$json = <<<EOD
{
	"folderId": {$folderId},
        "folderName": "{$folderName}",
        "facet": "{$facet}",
        "action": "{$action}"
}
EOD;
echo $json;
exit;

?>