<?php

// ===========================================================================================
//
// config_nav.php
//
// Navigation specific configurations.
//
$menuNavBarNoLogin = Array (
        'Hem'           => '?p=home',
    	'Om' 		=> '?p=about',
);
define('MENU_NAVBAR_NO_LOGIN',           serialize($menuNavBarNoLogin));

$menuNavBar = Array (
        'Hem'           => '?p=home',
        'Bildarkiv' 	=> '?p=archive',
    	'Om' 		=> '?p=about',
);
define('MENU_NAVBAR',           serialize($menuNavBar));

$menuNavBarForAdmin = Array (
        'Hem'           => '?p=home',
	'Installera' 	=> '?p=install',
        'Admin'         => '?p=admin',
    	'Om' 		=> '?p=about',
);
define('MENU_NAVBAR_FOR_ADMIN', serialize($menuNavBarForAdmin));

$adminMenuNavBar = Array (
        'Användare'         => '?p=admin_anvandare',
        'Kataloger'         => '?p=admin_folders',
        'Bildarkiv'         => '?p=admin_archive',
        'Koppla användare'  => '?p=admin_manager',
);
define('ADMIN_MENU_NAVBAR',      serialize($adminMenuNavBar));
?>