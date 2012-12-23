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
        'Admin' => '?p=admin',
    	'Om' 		=> '?p=about',
);
define('MENU_NAVBAR', 	serialize($menuNavBar));

$adminMenuNavBar = Array (
        'Användare'           => '?p=admin_anvandare',
        'Bildarkiv' 	=> '?p=admin_archive',
        'Koppla användare' 	=> '?p=something',
);
define('ADMIN_MENU_NAVBAR', 	serialize($adminMenuNavBar));
?>