<?php
// ===========================================================================================
//
// File: PPictureArchive.php
//
// Description: Show the content of the users filearchive.
//
// Author: Mats Ljungquist
//

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

// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
$folderFilter = $pc->GETisSetOrSetDefault('ff', '');

$redirect       = "?p=" . $pc->computePage();
$action = $redirect . "p";

$uo = CUserData::getInstance();
$account = $uo -> getAccount();
$userId	= $uo -> getId();

// $log -> debug("userid: " . $userId);
// Always check whats coming in...
//$pc->IsNumericOrDie($articleId, 0);

// -------------------------------------------------------------------------------------------
//
// Get content of file archive from database
//
$db = new CDatabaseController();
$mysqli = $db->Connect();

// Get all the folders from db. This will form the left side folder nav system.
$folderHtml = "";
$currentFolderName = "";
$spListFolders = DBSP_ListFoldersByUserOnly;
$query 	= <<< EOD
CALL {$spListFolders}({$userId});
EOD;
$res = $db->MultiQuery($query);
$results = Array();
$total = 0;
$db->RetrieveAndStoreResultsFromMultiQuery($results);
while($row = $results[0]->fetch_object()) {
    $total = $total + $row->facet;
    $folderHtml .= "<div class='row'><a href='{$redirect}&ff={$row->id}'>{$row->name} ({$row->facet})</a></div>";
    if ($row->id == $folderFilter) {
        $currentFolderName = $row->name;
    }
}
$folderHtml = "<div class='row all'><a href='{$redirect}'>Alla ({$total})</a></div>{$folderHtml}";

// Create file handler (CAttachment()). The file handler presents html
// for listing files.
$attachment = new CAttachment();
$archiveDb = $attachment -> getFileList($db, $userId, $pc->computePage(), $folderFilter);
$mysqli->close();

// Link to images
$imageLink = WS_IMAGES;

// -------------------------------------------------------------------------------------------
//
// Add JavaScript and html head stuff related to JavaScript
//
$js = WS_JAVASCRIPT;
$needjQuery = TRUE;
$htmlHead = <<<EOD
    <!-- jQuery UI -->
    <script src="{$js}jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>

    <!-- jQuery Form Plugin -->
    <script type='text/javascript' src='{$js}jquery-form/jquery.form.js'></script>
    <script type='text/javascript' src='{$js}myJs/disimg-utils.js'></script>
EOD;
$htmlHead .= $attachment -> getHead();
$javaScript = $attachment -> getJavaScript($pc->computePage());

$javaScript .= <<<EOD
// ----------------------------------------------------------------------------------------------
//
//
//
var globalUrl = "{$action}";

(function($){
    $(document).ready(function() {
        // Event declaration
        $('.cbMark').click(function(event) {
            var userId = {$userId};
            var action = "";
            if ($(this).is(':checked')) {
                $.jGrowl("Ditt intresse är noterat.");
                action = "add";
            } else {
                $.jGrowl("Ditt ointresse är noterat.");
                action = "delete";
            }
            $.post(  
                globalUrl,
                {action: action, userid: userId, fileid: fileId}  
            );
        });
    });
})(jQuery);
EOD;


$redirectFail   = "?p=" . $pc->computePage();
$headerHtml = empty($currentFolderName) ? "Alla bilder" : "Bilder i katalogen: " . $currentFolderName;

// -------------------------------------------------------------------------------------------
//
// Create HTML for page
//
$htmlMain = <<<EOD
<h1>{$headerHtml}</h1>
    <div class='section'>
        {$archiveDb}
    </div>
    <p class="small" style="text-align: right;">Vänligen kryssa för de objekt du är intresserad av.</p>
EOD;

$htmlRight = "";

// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

// Creating the left menu panel
$htmlLeft = "<div id='navigation'>{$folderHtml}</div>";

$page->printPage('Bildarkiv', $htmlLeft, $htmlMain, $htmlRight, $htmlHead, $javaScript, $needjQuery);
exit;

?>