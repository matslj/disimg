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

// These attributes MUST be initialized outside of this file (before it is included).
//
// $pageId
// $title
// $content

// Publish button is initially disabled
$publishDisabled = 'disabled="disabled"';

// Javascript settings
$js = WS_JAVASCRIPT;
if (!isset($htmlHead)) {
    $htmlHead = "";
}
$htmlHead .= <<<EOD
    <!-- jQuery UI -->
    <script src="{$js}jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>
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
        var dialogOptions = {
            url: "?p=page-save",
            callback: function(data) {
                console.log("data-pageid: " + data.pageId);
                console.log("data-timestamp: " + data.timestamp);
            }
        };
        var formData = {
            pageId: {$pageId},
            title: "{$title}",
            content: "{$content}"
        };
        $("#dialogPageTextChange").pageEditDialog(dialogOptions, formData);

	// ----------------------------------------------------------------------------------------------
	//
	// Event handler for buttons in form. Instead of messing up the html-code with javascript.
	// Using Event bubbling as described in this document:
	// http://docs.jquery.com/Tutorials:AJAX_and_Events
	//
	$('#dialogPageTextDivOpen').click(function(event) {
            if ($(event.target).is('.editText')) {
            console.log("opening dialog just det");
                $("#dialogPageTextChange").dialog("open");
                event.preventDefault();
            }
	});
});
})(jQuery);

EOD;

$htmlPageTitleLink = "<div id='dialogPageTextDivOpen'><a href='#' id='load-link' class='editText'>Ändra</a></div>";
            
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
    <form id="dialogPageTextChangeForm" action='?p=page-save' method='POST'>
        {$htmlArticleTitle}
        <p>
            <textarea class='changables size500x400' id='content' name='content'>{$content}</textarea>
        </p>
    </form>
</div>
EOD;

?>