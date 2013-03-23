<?php
// ===========================================================================================
//
// PLogoutProcess.php
//
// Logout by destroying the session.
//

// -------------------------------------------------------------------------------------------
//
// Get pagecontroller helpers. Useful methods to use in most pagecontrollers
//
$pc = CPageController::getInstance();
//$pc->LoadLanguage(__FILE__);

$redirectTo = "?p=home"; // $pc->SESSIONisSetOrSetDefault('history2');


// -------------------------------------------------------------------------------------------
//
// Interception Filter, controlling access, authorithy and other checks.
//
$intFilter = new CInterceptionFilter();

$intFilter->FrontControllerIsVisitedOrDie();
//$intFilter->UserIsSignedInOrRecirectToSignIn();
//$intFilter->UserIsMemberOfGroupAdminOrDie();

// -------------------------------------------------------------------------------------------
//
// Destroy the current session (logout user), if it exists.
//
require_once(TP_SOURCEPATH . 'FDestroySession.php');


// -------------------------------------------------------------------------------------------
//
// Redirect to the latest page visited before logout.
//
$pc->RedirectTo($redirectTo);
exit;

?>