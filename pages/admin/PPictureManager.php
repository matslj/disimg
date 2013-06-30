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

$userId = $pc->GETisSetOrSetDefault('userid', 0);

$pc->IsNumericOrDie($userId, 0);

$action = "?p=" . $pc->computePage() . "p";
$redirect = "?p=" . $pc->computePage();

$js = WS_JAVASCRIPT;
$needjQuery = TRUE;
$htmlHead = <<<EOD
    <style type="text/css">
        .added {
            background-color: #66CD00;
        }
        
        #userSelect {
            padding: 0px 0px 0px 1px;
            margin: 0 0 7px 0;
            height:24px;
        }
        
        #userSelect .option {
            margin: 2px 1px 2px 1px;
        }
    </style>
   
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
var globalUrl = "{$action}";

(function($){
    $(document).ready(function() {
        $.ajaxSetup ({  
            cache: false  
        }); 

        $('.toggle').each( function() {
        var iconmark = $(this).parent().parent().hasClass('added') ? "ui-icon-minusthick" : "ui-icon-plusthick";
        $(this).button({
            icons: {secondary : iconmark},
            text: false
        }).click(function(event) {
            var userId = {$userId}; // $('#userSelect').val();
            if (userId) {
                var sign = $(this).button("option", "icons").secondary == "ui-icon-plusthick" ? false : true;
                var tr = $(this).parent().parent();
                tr.toggleClass('added');
                var folderId = tr.attr('id');
                if (sign) {
                    $(this).button("option", "icons", {secondary: "ui-icon-plusthick"});
                    // tr.css('background-color', '');
                } else {
                    $(this).button("option", "icons", {secondary: "ui-icon-minusthick"});
                    // tr.css('background-color', '#66CD00');
                }
                var action = sign ? "delete" : "add";
                $.post(  
                    globalUrl,  
                    {action: action, userid: userId, folderid: folderId}  
                );
                // console.log(userId + " " + folderId);
            } else {
                alert("Du måste välja en användare först");
            }
        });
        });
        
        // get your select element and listen for a change event on it
        $('#userSelect').change(function() {
          // set the window's location property to the value of the option the user has selected
          window.location = "{$redirect}&userid=" + $('#userSelect').val();
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
// $userId	= $uo -> getId();

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
<select id="userSelect" class="ui-widget ui-state-default" name="userSelect">
  <option value="">Välj en användare...</option>
EOD;

while($row = $res->fetch_object()) {
    if (strcmp($row->idGroup, 'adm') != 0) {
        $selected = $userId == $row->idUser ? "selected" : "";
    $selectOption .= <<< EOD
        <option {$selected} value='{$row->idUser}'>{$row->nameUser}</option>
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
$spListFolders = DBSP_ListFoldersByUser;

// Create the query
$query 	= <<< EOD
CALL {$spListFolders}('{$orderStr}',{$userId});
EOD;

// Perform the query
$res = $db->MultiQuery($query);

// Use results
$results = Array();
$db->RetrieveAndStoreResultsFromMultiQuery($results);

$htmlFolderList = <<< EOD
<div id="folderList">
    <table class="disImgTable">
    <tr>
    <!-- <th><a href='{$httpRef}idFolder'>Id</a></th> -->
    <th class='namn'><a href='{$httpRef}nameFolder'>Namn</a></th>
    <th class='antal'><a href='{$httpRef}facet'>Antal</a></th>
    <th class='knapp'>&nbsp;</th>
    </tr>
EOD;

$i = 0;
while($row = $results[0]->fetch_object()) {
    $mark = "";
    if ($row->mark == 1) {
        $mark = " class='added'";
    }
    $htmlFolderList .= <<< EOD
    <tr id="{$row->id}"{$mark}>
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

// ------------------------------------------------------------
// --
// --                  Systemhjälp
// --
$helpContent = <<<EOD
<p>
    På den här sidan kan du koppla en användare till en eller flera kategorier.
    Koppla gör du genom att trycka på plus-tecknet i tabellens högerkant. Användaren
    som du kopplar kan bara se de kategorier som du kopplat ihop användaren med.
</p>
<p>
    Du kopplar loss användare från en kategori genom att klicka på minus-tecknet.
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
<h1>Kategoribehörighet</h1>
{$htmlHelp}
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
$htmlLeft = $page ->PrepareLeftSideNavigationBar(ADMIN_MENU_NAVBAR, "Admin - undermeny");

// $page->PrintPage("File archive for user '{$account}'", $htmlLeft, $htmlMain, $htmlRight);
$page->printPage('Koppla kategoribehörighet', $htmlLeft, $htmlMain, $htmlRight, $htmlHead, $javaScript, $needjQuery);
exit;

?>