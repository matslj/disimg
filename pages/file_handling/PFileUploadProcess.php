<?php
// ===========================================================================================
//
// File: PFileUploadProcess.php
//
// Description: Upload and store files in the users file archive.
//
// Author: Mikael Roos, mos@bth.se
//


// -------------------------------------------------------------------------------------------
//
// Get pagecontroller helpers. Useful methods to use in most pagecontrollers
//
$pc = CPageController::getInstance();
$log = CLogger::getInstance(__FILE__);

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
$submitAction	= $pc->POSTisSetOrSetDefault('do-submit');
$redirect	= $pc->POSTisSetOrSetDefault('redirect');
$redirectFail	= $pc->POSTisSetOrSetDefault('redirect-fail');

//$referenceId	= $pc->POSTisSetOrSetDefault('referenceId', 0);
//$pc->IsNumericOrDie($referenceId, 0);

$account = $pc->SESSIONisSetOrSetDefault('accountUser');
$archivePath = FILE_ARCHIVE_PATH . DIRECTORY_SEPARATOR . $account . DIRECTORY_SEPARATOR;
$archivePath = addslashes($archivePath);
if(!is_dir($archivePath)) {
	mkdir($archivePath);
}

// -------------------------------------------------------------------------------------------
//
// Depending on the submit-action, do whats to be done. If, else if, else, replaces switch.
//

// Define error messages
// http://www.php.net/manual/en/features.file-upload.errors.php
$errorMessages = Array (
        UPLOAD_ERR_INI_SIZE 	=> "The uploaded file exceeds the 'upload_max_filesize' directive in php.ini.",
        UPLOAD_ERR_FORM_SIZE 	=> "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.",
        UPLOAD_ERR_PARTIAL 	=> "The uploaded file was only partially uploaded.",
        UPLOAD_ERR_NO_FILE 	=> "No file was uploaded.",
        UPLOAD_ERR_NO_TMP_DIR   => "Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.",
        UPLOAD_ERR_CANT_WRITE   => "Failed to write file to disk. Introduced in PHP 5.1.0.",
        UPLOAD_ERR_EXTENSION 	=> "A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help. Introduced in PHP 5.2.0.",		
);

// -------------------------------------------------------------------------------------------
//
// Do some insane checking to avoid misusage, errormessage if not correct.
// 
if(false) {

}

// -------------------------------------------------------------------------------------------
//
// Upload single file and return html success/failure message. Ajax-like.
// 
else if($submitAction == 'upload-return-html') {
        if (!isset($_FILES['file']) || empty($_FILES['file'])) {
            exit(CHTMLHelpers::GetErrorMessageAsJSON($errorMessages[4]));
        }
	// Check that uploaded filesize is within limit
	if ($_FILES['file']['size'] > FILE_MAX_SIZE) {
		exit(CHTMLHelpers::GetErrorMessageAsJSON(sprintf("Failed to upload the file. Filesize, as defined in config.php (%s), is to large.", FILE_MAX_SIZE)));
	}

	// Create a unique filename
	do {
		$file = uniqid();
		$path = $archivePath . $file;
	} while(file_exists($path));

	// Move the uploaded file
	if (!move_uploaded_file($_FILES['file']['tmp_name'], $archivePath . $file)) {
		exit(CHTMLHelpers::GetErrorMessageAsJSON(sprintf("Failed to upload the file. Error code = %1d. %2s", $_FILES['file']['error'], $errorMessages[$_FILES['file']['error']])));
	}
	
	// Store metadata of the file in the database
	$db 	= new CDatabaseController();
	$mysqli = $db->Connect();
        
        $spInsertFile = DBSP_InsertFile;
        $spFileUpdateUniqueName = DBSP_FileUpdateUniqueName;
        
	// Create the query
	$query 	= <<< EOD
CALL {$spInsertFile}('{$userId}', '{$_FILES['file']['name']}', '{$path}', '{$file}', {$_FILES['file']['size']}, '{$_FILES['file']['type']}', @fileId, @status);
SELECT @fileId AS fileid, @status AS status;
EOD;

        // Perform the query
        $res = $db->MultiQuery($query);
        // Use results
        $results = Array();
        $db->RetrieveAndStoreResultsFromMultiQuery($results);

	// Check if the unique key was accepted, else, create a new one and try again
	$row = $results[1]->fetch_object();
	$status = $row->status;
	$fileid = $row->fileid;
	$results[1]->close();

	// Did the unique key update correctly?	
	if($row->status) {
		// Create query to set new unique name
		do {
			$newid = uniqid();
                        $file = $newid;
			$query 	= <<< EOD
CALL {$spFileUpdateUniqueName}('{$fileid}', '{$newid}', @status);
SELECT @status AS status;
EOD;

			$row 		= $results[1]->fetch_object();
			$status = $row->status;
			$results[1]->close();
		} while ($status != 0);
	}

	$mysqli->close();
        $date = date("Y-m-d H:i:s");
    $json = <<<EOD
{
        "uploadedFile": {
            "fileName": "{$_FILES['file']['name']}",
            "size": "{$_FILES['file']['size']}",
            "mimeType": "{$_FILES['file']['type']}",
            "uniqueName": "{$file}",
            "created": "{$date}"
        }
}
EOD;
    exit($json);
}


// -------------------------------------------------------------------------------------------
//
// Default, submit-action not supported, show error and die.
// 
die("Not suported.");

?>