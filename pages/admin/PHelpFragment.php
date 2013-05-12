<?php
// -------------------------------------------------------------------------------------------
//
// PHelpFragment.php
//
// This page is just a fragment; it provides a page help system.
// 
// Uses stylesheet from jquery-ui.
// 
// Example of use:
// 
// $helpContent = <<<EOD
// <p>
//    Alla filer som läggs upp i systemet måste läggas in i en katalog för att kunna
//    delas ut till en/flera användare och det är här du lägger upp katalogerna. I
//    tabellen nedan så betyder:
// </p>
// <ul>
//    <li>Namn - Namnet på katalogen. Detta bör inte vara alltför långt.</li>
//    <li>Antal - Antalet filer som just nu ligger i katalogen.</li>
//    <li>Kolumn för att ta bort en katalog. Du kan bara ta bort katalogen om den är tom.</li>
// </ul>
// EOD;
//
// // Provides help facility - include $htmlHelp in main content
// require_once(TP_PAGESPATH . 'admin/PHelpFragment.php');
// 
// $htmlMain = <<<EOD
// <h1>Användarkonton</h1>
// {$htmlHelp}
// EOD;
// 
// Author: Mats Ljungquist
//

// ****** IN - Parameters ******
//     $helpContent: This parameter should contain the actual help text in html format
$helpContent = isset($helpContent) ? $helpContent : "<p>Hjälptext saknas, kontakta systemkonstruktören.</p>";
// ****** OUT - Parameters ******
//     $htmlHelp: The final html help text

// -------------------------------------------------------------------------------------------
// The page code
//
$imageLink = WS_IMAGES;
$htmlHead = isset($htmlHead) ? $htmlHead : "";
$javaScript = isset($javaScript) ? $javaScript : "";

$htmlHead .= <<<EOD
    <style>
        .helpA {
            float:right;
        }

    </style>
EOD;

$javaScript .= <<<EOD
(function($){
    $(document).ready(function() {
        var c = $('h1').html();
        c = c + '<a id="pageHelpToggle" class="helpA" href="#"><img src="{$imageLink}question.png" /></a>'
        $('h1').html(c).after('{$htmlHelp}');
        

        // Hide page help and initialize page help handler
        $('#pageHelp').hide();
        $('#pageHelpToggle').click(function() {
            $('#pageHelp').toggle(400);
            return false;
        });
    });
})(jQuery);
EOD;
        
$htmlHelp = <<<EOD
<div id="pageHelp" class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
    <div><span class="ui-icon ui-icon-help" style="float: left; margin-right: .3em;"></span>
        {$helpContent}
    </div>
</div>
EOD;
?>
