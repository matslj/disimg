<?php
// ===========================================================================================
//
// PPageEditDialog.php
//
// Provides a dialog for editing title and content of a page.
// Currently there is a limit to 1 title and 1 content. Multiple titles/contents
// cannot be handled.
//
// Author: Mats Ljungquist
//

// -------------------------------------------------------------------------------------------
//
// Get pagecontroller helpers. Useful methods to use in most pagecontrollers
//

// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
// $pageId
// $redirect
// $userId		

// -------------------------------------------------------------------------------------------
//
// Create a new database object, connect to the database, get the query and execute it.
// Relates to files in directory TP_SQLPATH.
//
$title 		= "";
$content 	= "";
// Publish button is initially disabled
$publishDisabled = 'disabled="disabled"';

// Javascript settings
$js = WS_JAVASCRIPT;
$needjQuery = TRUE;
if (!isset($htmlHead)) {
    $htmlHead = "";
}
$htmlHead .= <<<EOD
    <!-- jQuery UI -->
    <script src="{$js}jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>

    <!-- jQuery Form Plugin -->
    <script type='text/javascript' src='{$js}jquery-form/jquery.form.js'></script>
    <script type='text/javascript' src='{$js}myJs/disimg-utils.js'></script>
EOD;

if (!isset($javaScript)) {
    $javaScript = "";
}
$javaScript .= <<<EOD
// ----------------------------------------------------------------------------------------------
//
//
//
(function($){
    $(document).ready(function() {
        $("#dialogPageTextChange").initDialog();

	// ----------------------------------------------------------------------------------------------
	//
	// Event handler for buttons in form. Instead of messing up the html-code with javascript.
	// Using Event bubbling as described in this document:
	// http://docs.jquery.com/Tutorials:AJAX_and_Events
	//
	$('#form1').click(function(event) {
            if ($(event.target).is('.editText')) {
                $("#dialogPageTextChange").dialog("open");
                event.preventDefault();
            } else if ($(event.target).is('button#savenow')) {
                $('#action').val('draft');
                $(event.target).attr('disabled', 'disabled');
                // $(event.target).submit();
            }
	});
});
})(jQuery);

EOD;

$img = WS_IMAGES;

// <input type='hidden' name='redirect_on_success' value='article-edit&amp;article-id=%1\$d&amp;topic-id=%2\$d'>
$htmlArticleTitle = "<p>Title: <input id='title' class='changables title' type='text' name='title' value='{$title}'></p>";
// -------------------------------------------------------------------------------------------
//
// Page specific code
//
$htmlPageTextDialog = <<<EOD
<!-- ui-dialog delete -->
<div id="dialogPageTextChange" title="Ändra text">
    <h1>Ändra text</h1>
    <form id="dialogPageTextChangeForm" class='editor1' action='?p=page-save' method='POST'>
        <input type='hidden' name='redirect' value='{$redirect}'>
        <input type='hidden' name='redirect-failure' value='{$redirect}'>
        <input type='hidden' id='page_id' name='page_id' value='{$pageId}'>
        <input type='hidden' id='action' name='action' value=''>
        {$htmlArticleTitle}
        <p>
            <textarea class='changables size500x400' id='content' name='content'>{$content}</textarea>
        </p>
        <p class="notice">
            Saved: {$saved}
        </p>
        <p>
            <button id='publish' {$publishDisabled} type='submit'><img src='{$img}/silk/accept.png' alt=''> Spara och visa</button>
            <button id='savenow' disabled='disabled' type='submit'><img src='{$img}/silk/disk.png' alt=''> Spara</button>
            <button id='discard' type='reset'><img src='{$img}/silk/cancel.png' alt=''> Återgå</button>
        </p>
    </form>
</div>
EOD;

?>