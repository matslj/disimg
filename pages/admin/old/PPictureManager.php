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
$intFilter->UserIsMemberOfGroupAdminOrDie();

$js = WS_JAVASCRIPT;
$needjQuery = TRUE;
$htmlHead = <<<EOD
    <!-- jQuery UI -->
    <script src="{$js}jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>

    <!-- jQuery Form Plugin -->
    <script type='text/javascript' src='{$js}jquery-form/jquery.form.js'></script>
    <script type='text/javascript' src='{$js}jquery-twosidedmultiselector/jquery.twosidedmultiselector.js'></script>
EOD;
    
$redirectOnSuccess = 'json';
$javaScript = <<<EOD
// ----------------------------------------------------------------------------------------------
//
//
//
(function($){
    $(document).ready(function() {
        $(".multiselect").twosidedmultiselect();
        
        // How to get the current selected items (note, they won't always have a "selected" attribute, but they will be in the box with the original ID:
	// var selectedOptions = $("#yourselect")[0].options;
        

        var options = {
            success:       showResponse,  // post-submit callback 
            dataType:  "json"
        }; 
        // Bind to form
        $('#form1').ajaxForm(options);

        // post-submit callback 
        function showResponse(data) {
            $('#page_id').val(data.pageId);
            $('p.notice').html("Saved: " + data.timestamp);
            $('button#savenow').attr('disabled', 'disabled');
        }

        function getUserInfoFromRow(targetId) {
            obj = {};
            var indexDelimiter = targetId.indexOf("_");
            var rowIndex = -1;
            if (indexDelimiter > 0) {
                rowIndex = targetId.substring(indexDelimiter + 1);
                obj.accountid = $('#idUser_' + rowIndex).html();
                obj.accountname = $('#accountName_' + rowIndex).html();
                obj.name = $('#nameUser_' + rowIndex).html();
                obj.email = $('#emailUser_' + rowIndex).html();
             
                return obj;
            } else {
                return null;
            }
        }

	// ----------------------------------------------------------------------------------------------
	//
	// Event handler for buttons in form. Instead of messing up the html-code with javascript.
	// Using Event bubbling as described in this document:
	// http://docs.jquery.com/Tutorials:AJAX_and_Events
	//
	$('#userList').click(function(event) {
            if ($(event.target).is('.edit')) {
                $("#dialogEdit").dialog("open");
                var userObj = getUserInfoFromRow(event.target.id);
                if (userObj != null) {
                    $('#dialogEditUserId').val(userObj.accountid);
                    $('#dialogEditAccountName').val(userObj.accountname);
                    $('#dialogEditName').val(userObj.name);
                    $('#dialogEditEmail').val(userObj.email);
                }
                event.preventDefault();
            } else if ($(event.target).is('.delete')) {
                $("#dialogDelete").dialog("open");
                var userObj = getUserInfoFromRow(event.target.id);
                if (userObj != null) {
                    $('#dialogDeleteUserId').val(userObj.accountid);
                    $('#dialogDeleteName').html(userObj.name);
                }
                event.preventDefault();
            } else if ($(event.target).is('.create')) {
                $("#dialogCreate").dialog("open");
                event.preventDefault();
            }
	});
    });
})(jQuery);
EOD;

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
//$db 		= new CDatabaseController();
//$mysqli = $db->Connect();
//$attachment = new CAttachment();
//$archiveDb = $attachment -> getFileList($db, $userId, $pc->computePage());
//// $archiveDb = $attachment -> getDownloads($db, $userId, 'archive');
//$mysqli->close();

// -------------------------------------------------------------------------------------------
//
// Reuse query from PUsersList.php
//
$httpRef = "?p=admin_manager&amp;order=ASC&orderby=nameUser";
$db 	= new CDatabaseController();
$mysqli = $db->Connect();
$query = $db->LoadSQL('SAdminList.php');
$res = $db->Query($query);

// Set up a select-option list using the result from the query
$selectOption = <<< EOD
<select id="userSelect" name="userSelect">
  <option selected value="">Välj en användare...</option>
EOD;

while($row = $res->fetch_object()) {
    if (strcmp($row->idGroup, 'adm') != 0) {
    $selectOption .= <<< EOD
        <option value='{$row->idUser}'>{$row->nameUser}</option>
EOD;
    }
}

$selectOption .= <<< EOD
</select> 
EOD;

// Close resultset and db
$res->close();
$mysqli->close();


// -------------------------------------------------------------------------------------------
//
// Create HTML for page
//
$htmlMain = <<<EOD
<h1>File archive</h1>
<div class='section'>
{$selectOption}
<p>You can use it on as many select lists as you wish and each will work independently.
This list already has a couple of selected values.</p>
<form method="post" action="">
        <select name="yourselect" class="multiselect" size="6" multiple="true">
        <option value="A" selected="true">The Letter A</option>
        <option value="B">The Letter B</option>
        <option value="C" selected="true">The Letter C</option>
        <option value="D">The Letter D</option>
        <option value="E" selected="true">The Letter E</option>
        <option value="F">The Letter F</option>
        </select>
        <div style="clear: both;">
                <input type="submit" value="Go">
        </div>
</form>
</div>
EOD;

$htmlRight	= "";

// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

// Creating the left menu panel
$htmlLeft = "<div id='navigation'>" . $page ->PrepareLeftSideNavigationBar(ADMIN_MENU_NAVBAR) . "</div>";

// $page->PrintPage("File archive for user '{$account}'", $htmlLeft, $htmlMain, $htmlRight);
$page->printPage('Användare', $htmlLeft, $htmlMain, $htmlRight, $htmlHead, $javaScript, $needjQuery);
exit;

?>