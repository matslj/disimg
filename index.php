<?php

// ===========================================================================================
//
// index.php
//
// An implementation of a PHP frontcontroller for a web-site.
//
// All requests passes through this page, for each request is a pagecontroller choosen.
// The pagecontroller results in a response or a redirect.
//
// -------------------------------------------------------------------------------------------
//
// Require the files that are common for all pagecontrollers.
//

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php');

//
// start a timer to time the generation of this page (excluding config.php)
//
if(WS_TIMER) {
	$gTimerStart = microtime(TRUE);
}

//
// Enable autoload for classes. User PEAR naming scheme for classes.
// E.G captcha_CCaptcha as classname.
//
function __autoload($class_name) {
    $path = str_replace('_', DIRECTORY_SEPARATOR, $class_name);
    require_once(TP_SOURCEPATH . "$path.php");
}
session_start();
// Allow only access to pagecontrollers through frontcontroller
// $indexIsVisited = TRUE;

// -------------------------------------------------------------------------------------------
//
// Redirect to the choosen pagecontroller.
// Observe that all redirects in all subpages should consist of either the
// gPage attribute or the gSubPage-attribute. So no hard referencs in subpages.
//
$gPage = isset($_GET['p']) ? $_GET['p'] : 'home';
$gSubPage = '';

// In order to use modules this snippet is used
$gSubPages = explode("_", $gPage);
if (count($gSubPages) >= 2) {
    $gPage = $gSubPages[0];   // Pages accessed from this index file
    $gSubPage = $gSubPages[1]; // Pages accessed from a sub level index file
}

switch ($gPage) {
    //
    // Hem
    // changing from PIndex.php to forum/PIndex.php
    //
    case 'home':                require_once(TP_PAGESPATH . 'PIndex.php'); break;
    case 'about':               require_once(TP_PAGESPATH . 'PAbout.php'); break;

    //
    // Install database
    //
    case 'install':             require_once(TP_PAGESPATH . 'install/PInstall.php'); break;
    case 'installp':            require_once(TP_PAGESPATH . 'install/PInstallProcess.php'); break;

    //
    // Login
    //
    case 'login':               require_once(TP_PAGESPATH . 'login/PLogin.php'); break;
    case 'loginp':              require_once(TP_PAGESPATH . 'login/PLoginProcess.php'); break;
    case 'logoutp':             require_once(TP_PAGESPATH . 'login/PLogoutProcess.php'); break;

    //
    // User profile
    //
    case 'profile':             require_once(TP_PAGESPATH . 'userprofile/PProfileShow.php'); break;
    case 'profilep':            require_once(TP_PAGESPATH . 'userprofile/PProfileProcess.php'); break;

    //
    // Admin pages
    //
    case 'admin':               require_once(TP_PAGESPATH . 'admin/index.php'); break;

    //
    // Page updater
    //
    case 'page-edit':		require_once(TP_PAGESPATH . 'page/PPageEdit.php'); break;
    case 'page-save':		require_once(TP_PAGESPATH . 'page/PPageSave.php'); break;
    case 'archive':             require_once(TP_PAGESPATH . 'page/PPictures.php'); break;
    case 'archivep':            require_once(TP_PAGESPATH . 'page/PPicturesProcess.php'); break;

    //	
    //	File Archive
    //	
    case 'homef':		require_once(TP_PAGESPATH . 'file_handling/PIndex.php'); break;
    case 'upload':              require_once(TP_PAGESPATH . 'file_handling/PFileUpload.php'); break;	
    case 'uploadp':             require_once(TP_PAGESPATH . 'file_handling/PFileUploadProcess.php'); break;
    case 'file-delete':         require_once(TP_PAGESPATH . 'file_handling/PFileDeleteProcess.php'); break;
    case 'file-deleteMulti':    require_once(TP_PAGESPATH . 'file_handling/PFileDeleteProcessMulti.php'); break;
    case 'file-moveMulti':      require_once(TP_PAGESPATH . 'file_handling/PFileMoveProcessMulti.php'); break;
    case 'file-download':	require_once(TP_PAGESPATH . 'file_handling/PFileDownloadProcess.php'); break;

    //
    // Default case, trying to access some unknown page, should present some error message
    // or show the home-page
    //
    default:                    require_once(TP_PAGESPATH . 'P404.php'); break;
}
?>
