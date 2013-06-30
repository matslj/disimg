<?php
// ===========================================================================================
//
// PPageEdit.php
//
// A WYSIWYG editor for changing the page info (title + content).
// Must be admin to be able to do this.
//
// Author: Mats Ljungquist
//

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
$pageId	= $pc->GETisSetOrSetDefault('page-id', 0);
$redirect	= $pc->GETisSetOrSetDefault('redirect', '');
$userId		= $_SESSION['idUser'];

// Always check whats coming in...
$pc->IsNumericOrDie($pageId, 0);

// -------------------------------------------------------------------------------------------
//
// Create a new database object, connect to the database, get the query and execute it.
// Relates to files in directory TP_SQLPATH.
//
$title 		= "";
$content 	= "";
// Publish button is initially disabled
$publishDisabled = 'disabled="disabled"';

// Using nicedit for wysiwyg
$nicedit = <<<EOD
<!-- Updated for NiceEditor ============================================================= -->
<script src="http://js.nicedit.com/nicEdit-latest.js" type="text/javascript"></script>
<script type="text/javascript">
bkLib.onDomLoaded(function() {
    new nicEditor({buttonList : ['bold','italic','underline','strikethrough','image','fontSize']}).panelInstance('content');
});
</script>
EOD;

$nicedit = "";

// Connect
$db 	= new CDatabaseController();
$mysqli = $db->Connect();

// Get the SP names
$spGetArticleAndTopicDetails	= DBSP_PGetSidaDetailsById;

$query = <<< EOD
CALL {$spGetArticleAndTopicDetails}({$pageId});
EOD;

// Perform the query
$results = Array();
$res = $db->MultiQuery($query);
$db->RetrieveAndStoreResultsFromMultiQuery($results);
$saved = 'Not yet';

// Get article details
$row = $results[0]->fetch_object();
if ($row) {
    $title = $row->title;
    $content 	= $row->content;
    $saved	= empty($row->latest) ? 'Not yet' : $row->latest;
}
$results[0]->close();

$mysqli->close();

$htmlArticleTitle = "<p>Title: <input id='title' class='changables title' type='text' name='title' value='{$title}'></p>";


// Javascript settings
$js = WS_JAVASCRIPT;
$needjQuery = TRUE;
$htmlHead = <<<EOD
<!-- Nicedit styling -->
<style>
form.editor1 {
}

form.editor1 input.title {
	font-family: Verdana, Sans-serif;
	font-size: 1.5em;
	width: 400px;
}

form.editor1 textarea.size500x400 {
	font-family: Verdana, Sans-serif;
	font-size: 1em;
	width: 500px;
	height: 400px;
}

form.editor1 p.notice {
	font-size: x-small;
	font-style: italic;
}
</style>

<!-- jGrowl latest -->
<link rel='stylesheet' href='{$js}jgrowl/jquery.jgrowl.css' type='text/css' />
<script type='text/javascript' src='{$js}jgrowl/jquery.jgrowl.js'></script>
<!-- jquery.form -->
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
        // Define options for jQuery.form.plugin
        var options = { 
            beforeSubmit:  showRequest,  // pre-submit callback 
            success:       showResponse,  // post-submit callback
            dataType:  "json"
        }; 
        // Bind form to plugin
        $('#form1').ajaxForm(options);
        
        // pre-submit callback 
        function showRequest(formData, jqForm, options) { 
            $.jGrowl("Changes have been made. Saving...");
            // var queryString = $.param(formData);
            //alert("About to submit: " + queryString); 
            return true; 
        }

        // post-submit callback 
        function showResponse(data) {
            if (data.action == 'publish') {
                window.location = "?p={$redirect}";
            } else {
                $('#page_id').val(data.pageId);
                $('p.notice').html("Saved: " + data.timestamp);
                $('button#savenow').attr('disabled', 'disabled');
                $.jGrowl("Saving complete");
            }
        }

        // This function regulates the disabled state of the publish button.
        function manipulatePublishButton() {
            var empty = true;
            $('.changables').each(function() {
                // console.log(this.id);
                if ($(this).val()) {
                    empty = false;
                }
            });
            // console.log("Empty = " + empty);
            if (empty) {
                $('button#publish').attr('disabled', 'disabled');
            } else {
                $('button#publish').removeAttr('disabled');
            }
        }
        
        // Some event binding - used only for regulating disabled status on buttons
        $('#form1').bind('keyup', function() {
            $('button#savenow').removeAttr('disabled');
            manipulatePublishButton();
        });

	// ----------------------------------------------------------------------------------------------
	//
	// Event handler for buttons in form. Instead of messing up the html-code with javascript.
	// Using Event bubbling as described in this document:
	// http://docs.jquery.com/Tutorials:AJAX_and_Events
	//
	$('#form1').click(function(event) {
		if ($(event.target).is('button#publish')) {
                        $('#action').val('publish');
                        // $('#redirect').val('{$redirect}');
			// Disable the button until form has changed again
			$(event.target).attr('disabled', 'disabled');
			// $(event.target).submit();
		} else if ($(event.target).is('button#savenow')) {
			$('#action').val('draft');
                        $(event.target).attr('disabled', 'disabled');
                        // $(event.target).submit();
		} else if ($(event.target).is('button#discard')) {
			history.back();
		} else if ($(event.target).is('a#viewPost')) {
			$.jGrowl('View published post...');
			if($('#isPublished').val() == 1) {
				$('a#viewPost').attr('href', '?p=article-edit&amp;article-id=%1\$d&amp;topic-id=%2\$d' + $('#topic_id').val() + '#post-' + $('#post_id').val());
			} else {
				alert('The post is not yet published. Press "Publish" to do so.');
				return(false);
			}
		}
	});
});
})(jQuery);

EOD;

$img = WS_IMAGES;

// <input type='hidden' name='redirect_on_success' value='article-edit&amp;article-id=%1\$d&amp;topic-id=%2\$d'>

// -------------------------------------------------------------------------------------------
//
// Page specific code
//
$htmlMain = <<<EOD
{$nicedit}
<!-- ==================================================================================== -->
<h1>Ändra text</h1>
<form id="form1" class='editor1' action='?p=page-save' method='POST'>
<input id='redirect' type='hidden' name='redirect_on_success' value='{$redirectOnSuccess}'>
<input type='hidden' name='redirect_on_failure' value='page-edit&amp;page-id=%1\$d'>
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
EOD;

$htmlLeft 	= "";
$htmlRight	= "";

// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

$page->printPage('Edit article', $htmlLeft, $htmlMain, $htmlRight, $htmlHead, $javaScript, $needjQuery);
exit;

?>