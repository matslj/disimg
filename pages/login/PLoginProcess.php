<?php

// ===========================================================================================
//
// PLoginProcess.php
//
// Creates a session and store userinfo in.
// 
// There are two paths for this login processor:
// 1) Authorize or refuse login attempt
// 2) Create new user
//
// Both destory the current session and log on the user (if creation/authentication is ok).
//
// @author Mats Ljungquist

$log = logging_CLogger::getInstance(__FILE__);

// -------------------------------------------------------------------------------------------
//
// Get pagecontroller helpers. Useful methods to use in most pagecontrollers
//
$pc = CPageController::getInstance(FALSE);
//$pc->LoadLanguage(__FILE__);

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
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
$user 		= $pc->POSTisSetOrSetDefault('nameUser', '');
$password 	= $pc->POSTisSetOrSetDefault('passwordUser', '');
$passwordAgain 	= $pc->POSTisSetOrSetDefault('passwordUserAgain', '');
$createAccount 	= $pc->POSTisSetOrSetDefault('createNewAccount', FALSE);
$adminLogin     = $pc->POSTisSetOrSetDefault('admin', FALSE);

$errorRedirect = "login&al={$adminLogin}&createAccount={$createAccount}";

// --------------------------------------------------------------------------------------------
// Validate input fields
// 
// Note: Only create account requires special validation.
if ($createAccount) {
    $log ->debug("in here: ");
    if (empty($password) || empty($passwordAgain)) {
        $_SESSION['errorMessage']	= "Lösenordsfälten får inte vara tomma";
    } else if (strcmp($password, $passwordAgain) != 0) {
        $_SESSION['errorMessage']	= "Lösenordesfälten måste matcha varandra";
    } else {
        // Validate captcha
        $captcha = captcha_CCaptcha::getInstance();
        if (!$captcha -> validateInput()) {
            $_SESSION['errorMessage'] = $captcha ->getErrorMsg();
        }
    }
} else {
    // Validate captcha
    $captcha = captcha_CCaptcha::getInstance();
    if (!$captcha -> validateInput()) {
        $_SESSION['errorMessage'] = $captcha ->getErrorMsg();
    }
}

// If there is an error exit back to login page.
if (!empty($_SESSION['errorMessage'])) {
    $pc->RedirectTo($errorRedirect);
    exit;
}

// Validation passed. Now we will authenticate/create and log on the user.
// This requires destorying the old session.

// -------------------------------------------------------------------------------------------
//
// Create a new database object, connect to the database, call stored procedure.
//
$db 	= new CDatabaseController();
$mysqli = $db->Connect();

// Get the SP names
$spLogin = $createAccount ? DBSP_CreateUser : DBSP_AuthenticateUser;

// Create the query
$query = "CALL {$spLogin}('{$user}', '{$password}');";
// $log ->debug("query: " . $query);
// Perform the query
$res = $db->MultiQuery($query);

// Use results
$results = Array();
$db->RetrieveAndStoreResultsFromMultiQuery($results);
//$log -> debug("längd: " . count($results));
$index = 0;
// Store inserted/updated article id
$row = $results[$index]->fetch_object();

// -------------------------------------------------------------------------------------------
//
// Use the results of the query to populate a session that shows we are logged in
//

// Must be one row in the resultset
if($results[$index]->num_rows === 1) {
        // Authentication / create was successfull.
        // - Destroy current session (logout user), if it exists, and create a new one.
        // - Store new user in session
        require_once(TP_SOURCEPATH . 'FDestroySession.php');
        // Recreate session
        session_start(); 		// Must call it since we destroyed it above.
        session_regenerate_id(); 	// To avoid problems
        // Store user
        // Get user-object
        $uo = CUserData::getInstance();
        $uo -> populateUserData($row->id, $row->account, $row->name, $row->email, $row->avatar, $row->groupid);
        // $log -> debug("id = " . $uo -> getId());
} else {
        $_SESSION['errorMessage']	= "Inloggning misslyckades - felaktigt användarnamn och/eller lösenord.";
        $_POST['redirect'] 		= $errorRedirect;
}


$results[$index]->close();
$mysqli->close();

// -------------------------------------------------------------------------------------------
//
// Redirect to another page
//
$pc->RedirectTo($pc->POSTisSetOrSetDefault('redirect'));
exit;

?>