<?php
// ===========================================================================================
//
// File: PPictureArchive.php
//
// Description:
// On this page an admin user can move upload images (thumbnails will be automatically
// created), move images between folders and delete images.
// 
// In the left column there is a menu for the admin panel and also there is a folder
// chooser (similar to the ones that exist on many other pages in this application).
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
$intFilter->UserIsMemberOfGroupAdminOrDie();

// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
$folderFilter = $pc->GETisSetOrSetDefault('ff', '');

$uo = CUserData::getInstance();
$account = $uo -> getAccount();
$userId	= $uo -> getId();

$action 	= "?p=uploadp";
$redirect       = "?p=" . $pc->computePage();
$redirectFail   = "?p=" . $pc->computePage();

// $log -> debug("userid: " . $userId);
// Always check whats coming in...
//$pc->IsNumericOrDie($articleId, 0);

// -------------------------------------------------------------------------------------------
//
// Get content of file archive from database
//
$db 		= new CDatabaseController();
$mysqli = $db->Connect();


// Get all the folders from db. This will form the left side folder nav system.
$folderTreeHtml = "";
$currentFolderName = "";
$spListFolders = DBSP_ListFolders;
$query 	= "CALL {$spListFolders}('')";
$currentTotal = -1;
$res = $db->MultiQuery($query);
$results = Array();
$db->RetrieveAndStoreResultsFromMultiQuery($results);
$folders = Array();
while($row = $results[0]->fetch_object()) {
    $folders[$row->id] = $row->name . "#" . $row->facet;
    $classSelected = "";
    if ($row->id == $folderFilter) {
        $currentFolderName = $row->name;
        $currentTotal = $row->facet;
        $classSelected = " selected";
    }
    $folderTreeHtml .= "<div class='row{$classSelected}'><a href='{$redirect}&ff={$row->id}'>{$row->name} ({$row->facet})</a></div>";
}

// Create file handler (CAttachment()). The file handler presents html
// for listing files.
$attachment = new CAttachment();
$dto = new CFileDto($userId, $pc->computePage(), $folderFilter);
$total = $attachment->getTotalNrOfFiles($db);
$navTotal = $currentTotal >= 0 ? $currentTotal : $total;
$navigate = CPageController::pageBrowser($navTotal, 6, 20, $pc->computePage());
$dto -> setPagePagination($navigate);
$archiveDb = $attachment -> getFileList($db, $dto);
$markRow = empty($currentFolderName) ? " selected" : "";
$folderTreeHtml = "<div class='folderTree'><div class='row all{$markRow}'><a href='{$redirect}'>Alla ({$total})</a></div>{$folderTreeHtml}</div>";
$results[0]->close();
// $archiveDb = $attachment -> getDownloads($db, $userId, 'archive');
$mysqli->close();


// Link to images
$imageLink = WS_IMAGES;
$attachment = new CAttachment();

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
(function($){
    $(document).ready(function() {
        var dialogOptions = { width: 500, cancel: false, modal: false};
        $("#dialogFileUpload").disimgDialog(dialogOptions);

        // Event declaration
        $('.section').click(function(event) {
            if ($(event.target).is('.upload')) {
                $(".status").html("");
                $("#fileInput").val("");
                $("#dialogFileUpload").dialog("open");
                event.preventDefault();
            } else if ($(event.target).is('.delete')) {
                // Anropa javascript-metoden i CAttachment.php som samlar ihop
                // och postar alla kryssade checkboxes.
                sendCheckedCheckboxes();
                event.preventDefault();
            } else if ($(event.target).is('.move')) {
                var ddSelect = $("#ddFolders").val();
                if (ddSelect) {
                    // Anropa javascript-metoden i CAttachment.php som samlar ihop
                    // och postar alla kryssade checkboxes.
                    sendCheckedCheckboxes('file-moveMulti', ddSelect);
                } else {
                    $.jGrowl("Inget händer - ingen kryssruta är markerad.");
                }
                event.preventDefault();
            }
        });
    });
})(jQuery);
EOD;

$maxFileSize 	= FILE_MAX_SIZE;

$folderHtml = <<<EOD
    <select id="ddFolders">
    <option value="">Välj...</option>
EOD;
foreach ($folders as $key => $value) {
    $indexF = strpos($value, "#");
    $nameF = substr($value, 0, $indexF);
    $facetF = substr($value, $indexF + 1);
    $folderHtml .= "<option value='{$key}'>{$nameF}</option>"; 
}
$folderHtml .= "</select>";

// ------------------------------------------------------------
// --
// --                  Systemhjälp
// --
$helpContent = <<<EOD
<p>
    Här kan du lägga upp, ta bort och flytta filer. Flytta handlar om att flytta
    filer mellan olika kategorier.
</p>
EOD;

// Provides help facility - include $htmlHelp in main content
require_once(TP_PAGESPATH . 'admin/PHelpFragment.php');
// -------------------- Slut Systemhjälp ----------------------

// -------------------------------------------------------------------------------------------
//
// Create HTML for page
//
$htmlMain = <<<EOD
<h1>Bildarkiv</h1>
{$htmlHelp}
    <div class='section'>
        <div id='fileArchiveDiv'>
            <p>
                <a href="#" id="load-link" class="dialog-link ui-state-default ui-corner-all upload">
                    <span class="ui-icon ui-icon-newwin create"></span>Ladda upp filer
                </a>
            </p>
        </div>
        {$archiveDb}
        <div id='fileArchiveControlsDiv'>
            <span class='control'>
                {$folderHtml}
            </span>
            <span class='control'>
                <a href="#" id="move-link" class="dialog-link ui-state-default ui-corner-all move">
                    <span class="ui-icon ui-icon-newwin create"></span>Flytta markerade filer
                </a>
            </span>
            <span class='control'>
                <a href="#" id="delete-link" class="dialog-link ui-state-default ui-corner-all delete">
                    <span class="ui-icon ui-icon-newwin create"></span>Radera markerade filer
                </a>
            </span>
        </div>
    </div>
EOD;
        
$htmlMain .= <<< EOD
    <!-- ui-dialog create -->
    <div id="dialogFileUpload" title="Dialog Title">
        <form id='dialogFileUploadForm' action='{$action}' method='POST'>
            <input type='hidden' name='redirect' value='{$redirect}'>
            <input type='hidden' name='redirect-failure' value='{$redirect}'>
            <input type='hidden' id='dialogCreateUserId' name='accountid' value=''>
            
            <input type='hidden' id='dialogCreateAction' name='action' value='?p=uploadp'>
            <fieldset>
                <p>
                    Med hjälp av filväljaren nedan så kan du ladda upp filer.
                </p>
                {$attachment -> getAsHTML()}
            </fieldset>
        </form>
    </div>
EOD;

$htmlRight = "";

// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

// Creating the left menu panel
$htmlLeft = $page ->PrepareLeftSideNavigationBar(ADMIN_MENU_NAVBAR, "Admin - undermeny");
$htmlLeft .= <<<EOD
<div class="Box-A" style="width: 100%; margin-top: 15px;">
    <div class="boxhead">
        <h2>
            <span>Kategori </span>
        </h2>
    </div>
    <div class="boxbody" style="padding: 5px;">
        <div id='navigation'>{$folderTreeHtml}</div>
    </div>
</div>
EOD;

$page->printPage('Bildarkiv', $htmlLeft, $htmlMain, $htmlRight, $htmlHead, $javaScript, $needjQuery);
exit;

?>