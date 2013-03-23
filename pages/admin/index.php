<?php
// ===========================================================================================
//
// index.php
//
// Modulecontroller. An implementation of a PHP module frontcontroller (module controller). 
// This page is called from the global frontcontroller. Its function could be named a 
// sub-frontcontroller or module frontcontroller. I call it a modulecontroller.
//
// All requests passes through this page, for each request a pagecontroller is choosen.
// The pagecontroller results in a response or a redirect.
//
// Author: Mats Ljungquist
//

// -------------------------------------------------------------------------------------------
//
// Redirect to the choosen pagecontroller.
//
global $gSubPages;
$thePage = "home";
if (count($gSubPages) >= 2) {
    $thePage = $gSubPages[1]; // Module 1 level down
}

switch($thePage) {
    //
    // User management pages
    //
    case 'home': require_once(TP_PAGESPATH . 'admin/PAdminIndex.php');
    break;
    case 'anvandare': require_once(TP_PAGESPATH . 'admin/PUsersList.php');
    break;
    case 'anvandarep': require_once(TP_PAGESPATH . 'admin/PUserEdit.php');
    break;

    // Folder management pages
    case 'folders': require_once(TP_PAGESPATH . 'admin/PFolders.php');
    break;
    case 'foldersp': require_once(TP_PAGESPATH . 'admin/PFoldersProcess.php');
    break;

    // Pages for connecting user to folders
    case 'manager':             require_once(TP_PAGESPATH . 'admin/PPictureManager.php'); break;
    case 'managerp':            require_once(TP_PAGESPATH . 'admin/PPictureManagerProcess.php'); break;

    //
    //	File Archive
    //
    case 'homef':		require_once(TP_PAGESPATH . 'file_handling/PIndex.php'); break;
    case 'archive':             require_once(TP_PAGESPATH . 'admin/PPictureArchive.php'); break;
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
    default: require_once(TP_PAGESPATH . 'P404.php');
        break;
}


?>