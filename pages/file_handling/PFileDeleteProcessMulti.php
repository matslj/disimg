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
$action         = $pc->POSTisSetOrSetDefault('action');

$log -> debug(print_r($filenames, true));

if (!(is_array($filenames) && count($filenames) >= 1)) {
    $log -> debug ("hääär");
    die ("Not an array");
}

$max = count($filenames);
$account = $pc->SESSIONisSetOrSetDefault('accountUser');

// Prepare for database access
$db = new CDatabaseController();
$mysqli = $db->Connect();
// Get db-function name
$udfDeleteFile = DBUDF_FileDelete;

$log -> debug("där och max: " . $max);

// Loop over the unique names in the array
for ($i = 0; $i < $max; ++$i) {
    $index1 = strpos($filenames[$i], "#");
    $index2 = strpos($filenames[$i], "#", $index1 + 1);
    $filename = substr($filenames[$i], $index1 +1 , $index2 - $index1 - 1);
    $ext = substr($filenames[$i], $index2 + 1);
    $archivePath = FILE_ARCHIVE_PATH . DIRECTORY_SEPARATOR . $account . DIRECTORY_SEPARATOR . $filename . "." . $ext;
    if (!is_file($archivePath)) die("The file does not exist in the file system.");
    
    $log -> debug($archivePath);
    
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
}

$mysqli->close();

$json = <<<EOD
{
        "action": "{$action}"
}
EOD;
echo $json;
exit;

?>