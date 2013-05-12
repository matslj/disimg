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
$total = 0;
$spListFolders = $uo -> isAdmin() ? DBSP_ListFolders : DBSP_ListFoldersByUserOnly;
$query 	= $uo -> isAdmin() ? "CALL {$spListFolders}('')" : "CALL {$spListFolders}({$userId})";
$res = $db->MultiQuery($query);
$results = Array();
$db->RetrieveAndStoreResultsFromMultiQuery($results);

while($row = $results[0]->fetch_object()) {
    $total = $total + $row->facet;
    $classSelected = "";
    if ($row->id == $folderFilter) {
        $currentFolderName = $row->name;
        $classSelected = " selected";
    }
    $folderHtml .= "<div class='row{$classSelected}'><a href='{$redirect}&ff={$row->id}'>{$row->name} ({$row->facet})</a></div>";
}

// Create file handler (CAttachment()). The file handler presents html
// for listing files.
$attachment = new CAttachment();
$archiveDb = $attachment -> getFileList($db, $userId, $pc->computePage(), $folderFilter, true);
$total = $uo -> isAdmin() ? $attachment->getTotalNrOfFiles($db) : $total;
$markRow = empty($currentFolderName) ? " selected" : "";
$folderHtml = "<div class='row all{$markRow}'><a href='{$redirect}'>Alla ({$total})</a></div>{$folderHtml}";
$results[0]->close();

$htmlHead = "";
$javaScript = "";

// -------------------------------------------------------------------------------------------
// 
// Read editable text for page
//
$pageName = basename(__FILE__);
$title          = "";
$content 	= "";
$pageId         = 0;

// Get the SP names
$spGetSidaDetails	= DBSP_PGetSidaDetails;

$query = <<< EOD
CALL {$spGetSidaDetails}('$pageName');
EOD;

// Perform the query
$results = Array();
$res = $db->MultiQuery($query);
$db->RetrieveAndStoreResultsFromMultiQuery($results);

// Get article details
$row = $results[0]->fetch_object();
if ($row) {
    $pageId     = $row->id;
    $content    = $row->content;
}
$results[0]->close();

$title = empty($currentFolderName) ? "Alla bilder" : "Bilder i katalogen: " . $currentFolderName;

$htmlPageTitleLink = "";
$htmlPageContent = "";
$htmlPageTextDialog = "";
$hideTitle = true;

require_once(TP_PAGESPATH . 'page/PPageEditDialog.php');

// -------------------------------------------------------------------------------------------
//
// Close DB connection
//
$mysqli->close();

// Link to images
$imageLink = WS_IMAGES;

// -------------------------------------------------------------------------------------------
//
// Add JavaScript and html head stuff related to JavaScript
//
$js = WS_JAVASCRIPT;
$needjQuery = TRUE;
$htmlHead .= <<<EOD
    <!-- jQuery UI -->
    <script src="{$js}jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>

    <!-- jQuery Form Plugin -->
    <script type='text/javascript' src='{$js}jquery-form/jquery.form.js'></script>
    <script type='text/javascript' src='{$js}myJs/disimg-utils.js'></script>
EOD;
$htmlHead .= $attachment -> getHead();
$javaScript .= $attachment -> getJavaScript($pc->computePage());

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
            var tempId = $(this).attr('id');
            var index = tempId.indexOf('#');
            var fileId = tempId.substring(0, index);
            var action = "";
            if ($(this).is(':checked')) {
                $.jGrowl("Registrerat intresse.");
                action = "add";
            } else {
                $.jGrowl("Avregistrerat intresse.");
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
// $headerHtml = empty($currentFolderName) ? "Alla bilder" : "Bilder i katalogen: " . $currentFolderName;

// -------------------------------------------------------------------------------------------
//
// Create HTML for page
//
$belowTableText = $uo->isAdmin() ? "Du är admin och kan därför inte kryssa för bilderna på den här sidan." : "Vänligen kryssa för de objekt du är intresserad av.";
$htmlMain = <<<EOD
<h1>{$htmlPageTitleLink}</h1>
    <p>
        {$htmlPageContent}
    </p>
    <div class='section'>
        {$archiveDb}
    </div>
    <p class="small" style="text-align: right;">{$belowTableText}</p>
    {$htmlPageTextDialog}
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