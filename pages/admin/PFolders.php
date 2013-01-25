<?php
// -------------------------------------------------------------------------------------------
//
// PUsersList.php
//
// Show all users in a list.
//

// -------------------------------------------------------------------------------------------
//
// Get pagecontroller helpers. Useful methods to use in most pagecontrollers
//
require_once(TP_SOURCEPATH . 'CPageController.php');

$pc = CPageController::getInstance();

// -------------------------------------------------------------------------------------------
//
// Interception Filter, access, authorithy and other checks.
//
require_once(TP_SOURCEPATH . 'CInterceptionFilter.php');

$intFilter = new CInterceptionFilter();
$intFilter->frontcontrollerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRecirectToSignIn();
$intFilter->UserIsMemberOfGroupAdminOrDie();

// -------------------------------------------------------------------------------------------
//
// Take care of global pageController settings, can exist for several pagecontrollers.
// Decide how page is displayed, review CHTMLPage for supported types.
//
$displayAs = $pc->GETisSetOrSetDefault('pc_display', '');

// -------------------------------------------------------------------------------------------
//
// Page specific code
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

$redirectOnSuccess = 'json';
$javaScript = <<<EOD
// ----------------------------------------------------------------------------------------------
//
//
//
(function($){
    $(document).ready(function() {
        $("#dialogCreate").initDialog();
        $("#dialogDelete").initDialog();
        
        var options = {
            dataType:  "json"
        }; 
        // Bind to form
        $('#form1').ajaxForm(options);

        function getUserInfoFromRow(targetId) {
            obj = {};
            var indexDelimiter = targetId.indexOf("_");
            var rowIndex = -1;
            if (indexDelimiter > 0) {
                rowIndex = targetId.substring(indexDelimiter + 1);
                obj.id = $('#idFolder_' + rowIndex).html();
                obj.name = $('#nameFolder_' + rowIndex).html();
             
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
	$('#folderList').click(function(event) {
            if ($(event.target).is('.delete')) {
                $("#dialogDelete").dialog("open");
                var folderObj = getUserInfoFromRow(event.target.id);
                if (folderObj != null) {
                    $('#dialogDeleteFolderId').val(folderObj.id);
                    $('#dialogDeleteFolderName').html(folderObj.name);
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

$htmlMain = <<<EOD
<h1>Kataloger</h1>
EOD;

$htmlRight = "";

// -------------------------------------------------------------------------------------------
//
// Take care of _GET variables. Store them in a variable (if they are set).
// Then prepare the ORDER BY SQL-statement, but only if the _GET variables has a value.
//
$httpRef = "";

// -------------------------------------------------------------------------------------------
//
// Create a new database object, connect to the database.
//
$db 	= new CDatabaseController();
$mysqli = $db->Connect();

$spListFolders = DBSP_ListFolders;

// Create the query
$query 	= <<< EOD
CALL {$spListFolders}();
EOD;

// Perform the query
$res = $db->MultiQuery($query);

// Use results
$results = Array();
$db->RetrieveAndStoreResultsFromMultiQuery($results);

// -------------------------------------------------------------------------------------------
//
// Show the results of the query
//

$htmlMain .= <<< EOD
<div id="folderList">
    <p><a href="#" id="dialog-link" class="ui-state-default ui-corner-all create"><span class="ui-icon ui-icon-newwin create"></span>Skapa katalog</a></p>
    <table id="folders">
    <tr>
    <th><a href='{$httpRef}idFolder'>Id</a></th>
    <th><a href='{$httpRef}nameFolder'>Namn</a></th>
    <th><a href='{$httpRef}facets'>Innehåll<br/>(antal filer)</a></th>
    <th>&nbsp;</th>
    </tr>
EOD;

$i = 0;
while($row = $results[0]->fetch_object()) {
    $htmlMain .= <<< EOD
    <tr>
        <td id="idFolder_{$i}">{$row->id}</td>
        <td id="nameFolder_{$i}">{$row->name}</td>
        <td id="facet_{$i}">{$row->facet}</td>
        <td>
            <a href="#" id="dialogDelete_{$i}" class="ui-state-default ui-corner-all dialogRowIcon delete">
                <span id="dialogDeleteSpan_{$i}" class="ui-icon ui-icon-close delete"></span>
            </a>
        </td>
    </tr>
EOD;
$i++;
}


$action = "?p=" . $pc->computePage() . "p";
$redirect = "?p=" . $pc->computePage();
$htmlMain .= <<< EOD
    </table>
</div>
<!-- ui-dialog create -->
<div id="dialogCreate" title="Dialog Title">
    <form id='dialogCreateForm' action='{$action}' method='POST'>
        <input type='hidden' name='redirect' value='{$redirect}'>
        <input type='hidden' name='redirect-failure' value='{$redirect}'>
        <input type='hidden' id='dialogCreateAction' name='action' value='create'>
        <fieldset>
            <p>Skapa ny katalog</p>
            <table width='99%'>
                <tr>
                    <td><label for="dialogCreateFolderName">Namn: </label></td>
                    <td style='text-align: right;'><input id='dialogCreateFolderName' class='name' type='text' name='foldername' value='' /></td>
                </tr>
            </table>
        </fieldset>
    </form>
</div>
<!-- ui-dialog delete -->
<div id="dialogDelete" title="Radera katalog">
    <form id='dialogDeleteForm' action='{$action}' method='POST'>
        <input type='hidden' name='redirect' value='{$redirect}'>
        <input type='hidden' name='redirect-failure' value='{$redirect}'>
        <input type='hidden' id='dialogDeleteFolderId' name='folderid' value=''>
        <input type='hidden' id='dialogDeleteAction' name='action' value='delete'>
        <fieldset>
            <p>Vill du radera den här katalogen?</p>
            <div id="dialogDeleteFolderName"></div>
        </fieldset>
    </form>
</div>
EOD;

$results[0]->close();


// -------------------------------------------------------------------------------------------
//
// Close the connection to the database
//

$mysqli->close();


// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
require_once(TP_SOURCEPATH . 'CHTMLPage.php');

$page = new CHTMLPage(WS_STYLESHEET);

// Creating the left menu panel
$htmlLeft = "<div id='navigation'>" . $page ->PrepareLeftSideNavigationBar(ADMIN_MENU_NAVBAR) . "</div>";

// $page->printPage($htmlLeft, $htmlMain, $htmlRight, '', $displayAs);
$page->printPage('Kataloger', $htmlLeft, $htmlMain, $htmlRight, $htmlHead, $javaScript, $needjQuery);
exit;

?>
