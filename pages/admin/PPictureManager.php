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
$intFilter->UserIsMemberOfGroupAdminOrDie();

$js = WS_JAVASCRIPT;
$needjQuery = TRUE;
$htmlHead = <<<EOD
    <!-- jQuery UI -->
    <script src="{$js}jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>

    <!-- jQuery Form Plugin -->
    <script type='text/javascript' src='{$js}jquery-form/jquery.form.js'></script>
EOD;
    
$redirectOnSuccess = 'json';
$javaScript = <<<EOD
// ----------------------------------------------------------------------------------------------
//
//
//
(function($){
    $(document).ready(function() {
        $('.toggle').button({
            icons: {secondary : "ui-icon-plusthick"},
            text: false
        }).click(function(event) {
            var sign = $(this).button("option", "icons").secondary == "ui-icon-plusthick" ? false : true;
            if (sign) {
                $(this).button("option", "icons", {secondary: "ui-icon-plusthick"});
                $(this).parent().parent().css('background-color', '');
            } else {
                $(this).button("option", "icons", {secondary: "ui-icon-minusthick"});
                $(this).parent().parent().css('background-color', '#66CD00');
            }
        });
        
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

// -------------------------------------------------------------------------------------------
//
// First we're going to present the user with a list of users (in a drop down).
// This choice will affect the list of folders presented next.
// 
// Reuse query from PUsersList.php
//
$httpRef = "";
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

// Close resultset
$res->close();

// -------------------------------------------------------------------------------------------
//
// Load the list of folders from DB
//
$spListFolders = DBSP_ListFolders;

// Create the query
$query 	= <<< EOD
CALL {$spListFolders}('{$orderStr}');
EOD;

// Perform the query
$res = $db->MultiQuery($query);

// Use results
$results = Array();
$db->RetrieveAndStoreResultsFromMultiQuery($results);

$htmlFolderList = <<< EOD
<div id="folderList">
    <table id="folders">
    <tr>
    <!-- <th><a href='{$httpRef}idFolder'>Id</a></th> -->
    <th class='namn'><a href='{$httpRef}nameFolder'>Namn</a></th>
    <th class='antal'><a href='{$httpRef}facet'>Antal</a></th>
    <th class='knapp'>&nbsp;</th>
    </tr>
EOD;

$i = 0;
while($row = $results[0]->fetch_object()) {
    $htmlFolderList .= <<< EOD
    <tr>
        <!-- <td id="idFolder_{$i}">{$row->id}</td> -->
        <td id="nameFolder_{$i}"><a href="?p=admin_archive&ff={$row->id}">{$row->name}</a></td>
        <td id="facet_{$i}">{$row->facet}</td>
        <td>
            <span id="dialogToggleSpan_{$i}" class="toggle"></span>
        </td>
    </tr>
EOD;
$i++;
}

$htmlFolderList .= <<< EOD
    </table>
</div>
EOD;

if ($i == 0) {
    $htmlFolderList = "";
}

$results[0]->close();

// -------------------------------------------------------------------------------------------
//
// Close DB
//
$mysqli->close();


$action = "?p=" . $pc->computePage() . "p";
$redirect = "?p=" . $pc->computePage();

// -------------------------------------------------------------------------------------------
//
// Create HTML for page
//
$htmlMain = <<<EOD
<h1>File archive</h1>
<div class='section'>
{$selectOption}
{$htmlFolderList}
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