<?php
// ===========================================================================================
//
// File: PFileArchive.php
//
// Description: Show the content of the users filearchive.
//
// Author: Mats Ljungquist
//

$log = logging_CLogger::getInstance(__FILE__);


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


// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
$uo = CUserData::getInstance();
$account = $uo -> getAccount();
$userId	= $uo -> getId();


$log -> debug("userid: " . $userId);
// Always check whats coming in...
//$pc->IsNumericOrDie($articleId, 0);


// -------------------------------------------------------------------------------------------
//
// Open and read a directory, show its content
//
$dir = FILE_ARCHIVE_PATH . DIRECTORY_SEPARATOR . $account;

$list = Array();
if(is_dir($dir)) {
	if ($dh = opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if($file != '.' && $file != '..') {
				$list[$file] = "{$file}";
			}
		}
	closedir($dh);
	}
}

ksort($list);

$archiveDisk = "<table><tr><th>Name</th></tr>";
foreach($list as $val => $key) {
    $archiveDisk .= "<tr><td>{$key}</td></tr>";
}
$archiveDisk .= '</table>';

// -------------------------------------------------------------------------------------------
//
// Get content of file archive from database
//
$db 		= new CDatabaseController();
$mysqli = $db->Connect();
$attachment = new CAttachment();
$dto = new CFileDto($userId, 'archive');
$archiveDb = $attachment -> getFileList($db, $userId);
// $archiveDb = $attachment -> getDownloads($db, $userId, 'archive');
$mysqli->close();

// -------------------------------------------------------------------------------------------
//
// Create HTML for page
//
$htmlMain = <<<EOD
<h1>File archive</h1>
<div class='section'>
<h2>Databaseview of the archive</h2>
{$archiveDb} 
</div>

<div class='section'>
<h2>Actual content on the disk</h2>
{$archiveDisk} 
</div>

EOD;

$htmlLeft 	= "";
$htmlRight	= <<<EOD
<h3 class='columnMenu'>Tags</h3>
<p>
Later...
</p>

<!--
<h3 class='columnMenu'>Hot Tags</h3>
<p>
Later...<br>
(Complete Tag Cloud)
</p>
<h3 class='columnMenu'>Recent Activity</h3>
<p>
Later...
</p>
-->
EOD;


// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

$page->PrintPage("File archive for user '{$account}'", $htmlLeft, $htmlMain, $htmlRight);
exit;

?>