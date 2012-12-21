<?php

// ===========================================================================================
//
// config_nav.php
//
// Navigation specific configurations.
//

$menuNavBar = Array (
        'Hem'           => '?p=home',
        'Filarkiv' 	=> '?p=archive',
        'Ladda upp' 	=> '?p=upload',
	'Installera' 	=> '?p=install',
    	'Om' 		=> '?p=about',
);
define('MENU_NAVBAR', 	serialize($menuNavBar));
?>