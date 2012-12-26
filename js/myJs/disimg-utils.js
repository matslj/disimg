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
 
(function($) {

/*
 * DisImg specific dialog written as a jQueryPlugin
 * The element using this method requires a form by the name 'elementid'Form
 */
$.fn.initDialog = function(options) {
    // Secure the 'this'-element
    var element = $(this);
    // Define default values (for those values wich are possible to set for a user using 'options').
    var width = 400;
    var modal = true;
    var buttons = [
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

    // If options are present; use them
    if (typeof options !== 'undefined') {
        console.log(options);
        // Set default value on width
        width = typeof options.width !== 'undefined' ? options.width : width;
        modal = typeof options.modal !== 'undefined' ? options.modal : modal;
//        if ((typeof options.cancel !== 'undefined') && (options.cancel === false)) {
//            // Cancel unwanted - pop the cancelbutton (it is last in the buttons array)
//            buttons.pop();
//        }
        if ((typeof options.cancel !== 'undefined') && (options.cancel === false)) {
            buttons = [
                {
                    text: "Stäng",
                    click: function() {
                        $( element ).dialog( "close" );
                    }
                }
            ];
        }
    }

    // Dialogify the element
    element.dialog({
        autoOpen: false,
        modal: modal,
        width: width,
        buttons: buttons
    });
}

})(jQuery);