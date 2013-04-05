/**
 * .1.0
 * jQuery impls
 *
 * Requires jQuery and jQuery-UI
 *
 * Written by Mats Ljungquist
 * Last updated: 20121217
 *
 */

var editor = null;
 
(function($) {

/*
 * DisImg specific dialog written as a jQueryPlugin
 * The element using this method requires a form by the name 'elementid'Form
 */
$.fn.disimgDialog = function(options) {
    // Secure the 'this'-element
    var element = $(this);
    var o = $.extend({}, $.fn.disimgDialog.defaults, options);

    if ((typeof o.cancel !== 'undefined') && (o.cancel === false)) {
        o.buttons = [
            {
                text: "Stäng",
                click: function() {
                    $( element ).dialog( "close" );
                }
            }
        ];
    }
    
    // If buttons are uninitialized -> perform standard initialization
    if (o.buttons == null) {
        o.buttons = [
            {
                text: "Ok",
                click: function() {
                    $("#" + element.attr('id') + "Form").submit();
                    $( element ).dialog( "close" );
                }
            },
            {
                text: "Avbryt",
                click: function() {
                    $( element ).dialog( "close" );
                }
            }
        ];
    }

    // Dialogify the element
    $.fn.disimgDialog.dialogify(element, o);
}

$.fn.pageEditDialog = function(options, data) {
    // Secure the 'this'-element
    var element = $(this);
    var o = $.extend({}, $.fn.disimgDialog.defaults, options);
    
    // Initialize tiny editor
    editor = new TINY.editor.edit('editor', {
	id: 'contentPED',
	width: 584,
	height: 175,
	cssclass: 'tinyeditor',
	controlclass: 'tinyeditor-control',
	rowclass: 'tinyeditor-header',
	dividerclass: 'tinyeditor-divider',
	controls: ['bold', 'italic', 'underline', 'strikethrough', '|', 'subscript', 'superscript', '|',
		'orderedlist', 'unorderedlist', '|', 'outdent', 'indent', '|', 'leftalign',
		'centeralign', 'rightalign', 'blockjustify', '|', 'unformat', '|', 'undo', 'redo', 'n',
		'font', 'size', 'style', '|', 'image', 'hr', 'link', 'unlink', '|', 'print'],
	footer: true,
	fonts: ['Verdana','Arial','Georgia','Trebuchet MS'],
	xhtml: true,
	cssfile: 'custom.css',
	bodyid: 'editor',
	footerclass: 'tinyeditor-footer',
	toggle: {text: 'source', activetext: 'wysiwyg', cssclass: 'toggle'},
	resize: {cssclass: 'resize'}
    });

    // Initialize buttons
    if (o.buttons == null) {
        o.buttons = [
            {
                text: "Ok",
                click: function() {
                    editor.post();
                    data.title = $('#titlePED').val();
                    data.content = $('#contentPED').val();
                    // $("#" + element.attr('id') + "Form").submit();
                    $.post(  
                        o.url,  
                        {page_id: data.pageId, redirect_on_success: "json", title: data.title, content: data.content},
                        o.callback,
                        "json"
                    );
                    $( element ).dialog( "close" );
                }
            },
            {
                text: "Avbryt",
                click: function() {
                    $( element ).dialog( "close" );
                }
            }
        ];
    }
    
    // Dialogify the element
    $.fn.disimgDialog.dialogify(element, o);
}

// This method uses jquery-ui to create a dialog of an element from the options
// in the parameters.
$.fn.disimgDialog.dialogify = function(element, o) {
    if ((typeof element === 'undefined') || (typeof o === 'undefined')) {
        throw new Error ('Error: dialogify must have all parameters set');
    }
    // Dialogify the element
    element.dialog({
        autoOpen: o.autoOpen,
        modal: o.modal,
        width: o.width,
        buttons: o.buttons
    });
}

// Default values for all kinds of dialogs in disimg
$.fn.disimgDialog.defaults = {
    autoOpen: false,
    width: 400,
    modal: true,
    buttons: null
};

})(jQuery);