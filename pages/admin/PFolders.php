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
        $("#dialogCreate").disimgDialog();
        $("#dialogDelete").disimgDialog();
        
        $('.delete').button({
            icons: {secondary : "ui-icon-close"},
            text: false
        }).click(function(event) {
            var substr = $(this).attr('id').split(':');
            $('#dialogDeleteFolderId').val(substr[1]);
            $('#dialogDeleteFolderName').html(substr[2]);
            $('#dialogDelete').dialog("open");
        });
        
        var options = {
            dataType:  "json"
        }; 
        // Bind to form
        $('#form1').ajaxForm(options);

	// ----------------------------------------------------------------------------------------------
	//
	// Event handler for buttons in form. Instead of messing up the html-code with javascript.
	// Using Event bubbling as described in this document:
	// http://docs.jquery.com/Tutorials:AJAX_and_Events
	//
	$('#folderList').click(function(event) {
            if ($(event.target).is('.create')) {
                $("#dialogCreate").dialog("open");
                event.preventDefault();
            }
	});
    });
})(jQuery);
EOD;

// ------------------------------------------------------------
// --
// --                  Systemhjälp
// --
$helpContent = <<<EOD
<p>
    Alla filer som läggs upp i systemet måste läggas in i en kategori för att kunna
    delas ut till en/flera användare och det är här du lägger upp kategorierna. I
    tabellen nedan så betyder:
</p>
<ul>
    <li>Namn - Namnet på kategorin. Får vara max 20 tecken långt, inklusive mellanslag.</li>
    <li>Antal - Antalet filer som just nu ligger i kategorin.</li>
    <li>Kolumn för att ta bort en kategori. Du kan bara ta bort kategorin om den är tom.</li>
</ul>
EOD;

// Provides help facility - include $htmlHelp in main content
require_once(TP_PAGESPATH . 'admin/PHelpFragment.php');
// -------------------- Slut Systemhjälp ----------------------

$htmlMain = <<<EOD
<h1>Kategorier</h1>
{$htmlHelp}
EOD;

$htmlRight = "";
$redirect = "?p=" . $pc->computePage();

// -------------------------------------------------------------------------------------------
//
// Take care of _GET variables. Store them in a variable (if they are set).
// Then prepare the ORDER BY SQL-statement, but only if the _GET variables has a value.
//
$orderBy 	= $pc->GETisSetOrSetDefault('orderby', 'idFolder');
$orderOrder 	= $pc->GETisSetOrSetDefault('order', 'DESC');

$orderStr = "";
if(!empty($orderBy) && !empty($orderOrder)) {
    $orderStr = " ORDER BY {$orderBy} {$orderOrder}";
}

// -------------------------------------------------------------------------------------------
//
// Prepare the order by ref, can you figure out how it works?
//
$ascOrDesc = $orderOrder == 'ASC' ? 'DESC' : 'ASC';
$httpRef = $redirect . "&amp;order={$ascOrDesc}&orderby=";

// -------------------------------------------------------------------------------------------
//
// Create a new database object, connect to the database.
//
$db 	= new CDatabaseController();
$mysqli = $db->Connect();

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

// -------------------------------------------------------------------------------------------
//
// Show the results of the query
//

$htmlMain .= <<< EOD
<div id="folderList">
    <p><a href="#" id="folder-link" class="dialog-link ui-state-default ui-corner-all create"><span class="ui-icon ui-icon-newwin create"></span>Skapa katalog</a></p>
EOD;

$htmlMainTemp = <<< EOD
<table class="disImgTable">
    <tr>
    <th class='namn'><a href='{$httpRef}nameFolder'>Namn</a></th>
    <th class='antal'><a href='{$httpRef}facet'>Antal</a></th>
    <th class='knapp'>&nbsp;</th>
    </tr>
EOD;

$i = 0;
while($row = $results[0]->fetch_object()) {
    $htmlMainTemp .= <<< EOD
    <tr>
        <td id="nameFolder_{$i}"><a href="?p=admin_archive&ff={$row->id}">{$row->name}</a></td>
        <td id="facet_{$i}">{$row->facet}</td>
        <td><span id="{$i}:{$row->id}:{$row->name}" class="delete"></span></td>
    </tr>
EOD;
$i++;
}

$htmlMainTemp .= "</table>";

if ($i == 0) {
    $htmlMainTemp = "";
}

$action = "?p=" . $pc->computePage() . "p";

$htmlMain .= <<< EOD
    {$htmlMainTemp}
</div>
<!-- ui-dialog create -->
<div id="dialogCreate" title="Dialog Title">
    <form id='dialogCreateForm' action='{$action}' method='POST'>
        <input type='hidden' name='redirect' value='{$redirect}'>
        <input type='hidden' name='redirect-failure' value='{$redirect}'>
        <input type='hidden' id='dialogCreateAction' name='action' value='create'>
        <fieldset>
            <p>Skapa ny kategori</p>
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
<div id="dialogDelete" title="Radera kategori">
    <form id='dialogDeleteForm' action='{$action}' method='POST'>
        <input type='hidden' name='redirect' value='{$redirect}'>
        <input type='hidden' name='redirect-failure' value='{$redirect}'>
        <input type='hidden' id='dialogDeleteFolderId' name='folderid' value=''>
        <input type='hidden' id='dialogDeleteAction' name='action' value='delete'>
        <fieldset>
            <p>Vill du radera den här kategorin?</p>
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
$htmlLeft = $page ->PrepareLeftSideNavigationBar(ADMIN_MENU_NAVBAR, "Admin - undermeny");

// $page->printPage($htmlLeft, $htmlMain, $htmlRight, '', $displayAs);
$page->printPage('Kategorier', $htmlLeft, $htmlMain, $htmlRight, $htmlHead, $javaScript, $needjQuery);
exit;

?>
