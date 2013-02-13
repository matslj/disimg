<?php
// ===========================================================================================
//
// File: PPictureArchive.php
//
// Description: Show the content of the users filearchive.
//
// Author: Mikael Roos, mos@bth.se
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
$intFilter->UserIsMemberOfGroupAdminOrDie();

// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
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
$db 		= new CDatabaseController();
$mysqli = $db->Connect();

// Get all the folders from db (the result will, later on, be used in order
// to populate drop downs).
$spListFolders = DBSP_ListFolders;
$query 	= <<< EOD
CALL {$spListFolders}();
EOD;
$res = $db->MultiQuery($query);
$results = Array();
$db->RetrieveAndStoreResultsFromMultiQuery($results);
$folders = Array();
while($row = $results[0]->fetch_object()) {
    $folders[] = $row->id . "#" . $row->name . "#" . $row->facet;
}

// Create file handler (CAttachment()). The file handler presents html
// for listing files.
$attachment = new CAttachment();
$archiveDb = $attachment -> getFileList($db, $userId, $pc->computePage());
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
        $("#dialogFileUpload").initDialog(dialogOptions);

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
            }
        });
    });
})(jQuery);
EOD;

$maxFileSize 	= FILE_MAX_SIZE;
$action 	= "?p=uploadp";
$redirect       = "?p=" . $pc->computePage();
$redirectFail   = "?p=" . $pc->computePage();

// -------------------------------------------------------------------------------------------
//
// Create HTML for page
//
$htmlMain = <<<EOD
<h1>Bildarkiv</h1>
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
                <select>
                <option value="volvo">Volvo</option>
                <option value="saab">Saab</option>
                <option value="mercedes">Mercedes</option>
                <option value="audi">Audi</option>
                </select>
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
            <input type='hidden' id='submit-ajax' name='do-submit' value='upload-return-html'>
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
$htmlLeft = "<div id='navigation'>" . $page ->PrepareLeftSideNavigationBar(ADMIN_MENU_NAVBAR) . "</div>";

$page->printPage('Användare', $htmlLeft, $htmlMain, $htmlRight, $htmlHead, $javaScript, $needjQuery);
exit;

?>