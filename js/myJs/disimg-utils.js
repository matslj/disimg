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
$.fn.initDialog = function() {
    var element = $(this);
    element.dialog({
        autoOpen: false,
        width: 400,
        buttons: [
            {
                text: "Ok",
                click: function() {
                    $("#" + element.attr('id') + "Form").submit();
                    $( this ).dialog( "close" );
                }
            },
            {
                text: "Avbryt",
                click: function() {
                    $( this ).dialog( "close" );
                }
            }
        ]
    });
}

})(jQuery);