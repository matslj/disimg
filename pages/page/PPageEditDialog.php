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

// The terms of use (not the legal terms :) ) for this page fragment is descibed by the parameters listed below.
// 
// ***** IN parameters for this page fragment *****
// These attributes MUST be initialized outside of this file (before it is included).
// $pageId - id of the page, used in db lookup
// $title - the current title of the page
// $content - the current content of the page
   $urlToProcessPage = "?p=page-save"; // This is the target page of the ajax call for storing the page.
// ***** OUT parameters for this page fragment *****
// The out parameters are set to default values (which is used when user != admin)
$htmlPageTitleLink = $title; // if admin: a clickable link which opens a edit dialog, otherwise just title text
$htmlPageContent = $content; // the content which is to be presented as content on the final page. This
                             // content is manipulated by javascript.
$htmlPageTextDialog = "";    // The html for the dialog box. Must be added somewhere, in the html, on the final page.
// *** End of IN/OUT parameter list

// *****************************************************************************
// **
// **               Initialize 'global' variables if needed
// Javascript settings
$js = WS_JAVASCRIPT;
$htmlHead = isset($htmlHead) ? $htmlHead : "";
$javaScript = isset($javaScript) ? $javaScript : "";

// *****************************************************************************
// **
// **                   THE CODE (where the magic happens) 
// The code below is only valid for admin users, for non admins there are nothing 
// more to process in this fragment.
$uo = CUserData::getInstance();
if ($uo -> isAdmin())
{
// Publish button is initially disabled
$publishDisabled = 'disabled="disabled"';

// Javascript settings
$htmlHead .= <<<EOD
    <!-- TinyEditor -->
    <link rel="stylesheet" href="{$js}tinyeditor/tinyeditor.css" type='text/css' media='screen'>
    <script type='text/javascript' src='{$js}tinyeditor/tiny.editor.packed.js'></script>
    <!-- jQuery UI -->
    <script src="{$js}jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>
    <script type='text/javascript' src='{$js}myJs/disimg-utils.js'></script>
EOD;

$javaScript .= <<<EOD
// ----------------------------------------------------------------------------------------------
//
//
//
(function($){
    $(document).ready(function() {
        var dialogOptions = {
            width: 615,
            url: "{$urlToProcessPage}",
            callback: function(data) {
                console.log("data-pageid: " + data.pageId);
                console.log("data-timestamp: " + data.timestamp);
                // console.log("data-content: " + data.content);
                if (data.pageId) {
                    $('#titlePage').html($('#titlePED').val());
                    $('#contentPage').html($('#contentPED').val());
                }
            }
        };
        var formData = {
            pageId: {$pageId}
        };
        $("#dialogPageTextChange").pageEditDialog(dialogOptions, formData);

	// ----------------------------------------------------------------------------------------------
	//
	// Event handler for buttons in form. Instead of messing up the html-code with javascript.
	// Using Event bubbling as described in this document:
	// http://docs.jquery.com/Tutorials:AJAX_and_Events
	//
	$('#titlePage').click(function(event) {
            if ($(event.target).is('.editText')) {
                // console.log("opening dialog just det");
                // Initialize the TinyEditor
                if (editor == null) {
                    editor = new TINY.editor.edit('editor', {
                        id: 'contentPED',
                        width: 584,
                        height: 175,
                        cssclass: 'tinyeditor',
                        controlclass: 'tinyeditor-control',
                        rowclass: 'tinyeditor-header',
                        dividerclass: 'tinyeditor-divider',
                        controls: ['bold', 'italic', 'underline', '|', 'subscript', 'superscript', '|',
                                'orderedlist', 'unorderedlist', '|', 'outdent', 'indent', '|', 'leftalign',
                                'centeralign', 'rightalign', 'blockjustify', '|', 'unformat', '|', 'undo', 'redo', 'n',
                                'style', '|', 'image', 'hr', 'link', 'unlink'],
                        footer: true,
                        fonts: ['Verdana','Arial','Georgia','Trebuchet MS'],
                        xhtml: true,
                        cssfile: 'js/tinyeditor/custom.css',
                        bodyid: 'editor',
                        footerclass: 'tinyeditor-footer',
                        toggle: {text: 'i kodform', activetext: 'i editorform', cssclass: 'toggle'},
                        resize: {cssclass: 'resize'}
                    });
                }
                $("#dialogPageTextChange").dialog("open");
                event.preventDefault();
            }
	});
});
})(jQuery);

EOD;

$htmlPageTitleLink = "<a id='titlePage' href='#' class='editText'>{$title}</a>";
$htmlPageContent = "<div id='contentPage'>{$content}</div>";
            
// -------------------------------------------------------------------------------------------
//
// Page specific code
//
$htmlPageTextDialog = <<<EOD
<!-- ui-dialog delete -->
<div id="dialogPageTextChange" title="Ändra text">
    <form id="dialogPageTextChangeForm" action='?p=page-save' method='POST'>
        <h3>Titel</h3> 
        <input id='titlePED' type='text' name='title' value='{$title}'>
        <h3>Innehåll</h3>
        <textarea id='contentPED' style="width: 400px; height: 200px">{$content}</textarea>
    </form>
</div>
EOD;
} // End of if uo -> isAdmin()

?>