<?php
// ===========================================================================================
//
// File: PPictureArchive.php
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
    $folderHtml .= "<div class='row'><a href='{$redirect}&ff={$row->id}'>{$row->name} ({$row->facet})</a></div>";
    if ($row->id == $folderFilter) {
        $currentFolderName = $row->name;
    }
}

// Create file handler (CAttachment()). The file handler presents html
// for listing files.
$attachment = new CAttachment();
$archiveDb = $attachment ->getFilesOfInterestAsJSON($db, "", "", "", "");
$total = 0;
$folderHtml = "<div class='row all'><a href='{$redirect}'>Alla ({$total})</a></div>{$folderHtml}";
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
$thumbFolder = WS_SITELINK . FILE_ARCHIVE_FOLDER . '/';

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
EOD;
$htmlHead .= $attachment -> getHead();
$javaScript .= $attachment -> getJavaScript($pc->computePage());

$thumbs = $thumbFolder . $row -> account . '/thumbs/' . '80px_thumb_' . $row -> uniquename . ".jpg";
                $ext = pathinfo($row->path, PATHINFO_EXTENSION);
                $imgs = $thumbFolder . $row -> account . '/' . $row -> uniquename . '.' . $ext;


$javaScript .= <<<EOD
// ----------------------------------------------------------------------------------------------
//
//
//
var globalUrl = "{$action}";
var globalMaxColumns = 5;

(function($){
    $(document).ready(function() {
        $.getJSON(globalUrl, function(data) {
            var tbody = $("#CustomerTable > tbody").html("");
            var length = data.length;
            var content = "";
            var previousFolder = "";
            var folderContentCounter = 0;
            for (var i = 0; i < length; i++) {
                var row = data[i];
                var thumbs = '{$thumbFolder}' + row.account + '/thumbs/80px_thumb_' + row.uniquename + '.jpg';
                var imgs = '{$thumbFolder}' + row.account + '/' + row.uniquename + '.' + row.ext;
                if (row.foldername != previousFolder) {
                    content += "<div class='biHeader'>" + row.foldername + "</div>";
                    content += "<div class='biRow'>";
                    folderContentCounter = 0;
                }
                if (folderContentCounter > globalMaxColumns) {
                    // End the previous biRow and start a new one
                    content += "</div><div class='biRow'>";
                }
                content += "<span><a href='" + imgs + "'><img src='" + thumbs + "' title='Klicka för att titta på bilden' /></a></span>";
                folderContentCounter++;
            }
            if (!content) {
                // end last biRow
                content += "</div>";
            }
        });

        // Event declaration
        $('.cbMark').click(function(event) {
            var userId = {$userId};
            var tempId = $(this).attr('id');
            var index = tempId.indexOf('#');
            var fileId = tempId.substring(0, index);
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

$htmlRight = "<div id='navigation'>{$folderHtml}</div>";

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