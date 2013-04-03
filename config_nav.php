<?php

// ===========================================================================================
//
// config_nav.php
//
// Navigation specific configurations.
//

$menuNavBar = Array (
        'Hem'           => '?p=home',
        'Bildarkiv' 	=> '?p=archive',
    	'Om' 		=> '?p=about',
);
define('MENU_NAVBAR',           serialize($menuNavBar));

$menuNavBarForAdmin = Array (
        'Hem'           => '?p=home',
        'Bildarkiv' 	=> '?p=archive',
        'Admin'         => '?p=admin',
    	'Om' 		=> '?p=about',
);
define('MENU_NAVBAR_FOR_ADMIN', serialize($menuNavBarForAdmin));

// Admin menu - side menu (column menu) but it can of course be used in other ways
$adminMenuNavBar = Array (
        'Användare'         => '?p=admin_anvandare',
        'Kataloger'         => '?p=admin_folders',
        'Bildarkiv'         => '?p=admin_archive',
        'Koppla användare'  => '?p=admin_manager',
);
define('ADMIN_MENU_NAVBAR',      serialize($adminMenuNavBar));
?>