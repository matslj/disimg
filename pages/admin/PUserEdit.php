<?php
// ===========================================================================================
//
// PProfileProcess.php
//
// Updates user password, email or avatar.
// 
// @author Mats Ljungquist
//

$log = logging_CLogger::getInstance(__FILE__);

// -------------------------------------------------------------------------------------------
//
// Get pagecontroller helpers. Useful methods to use in most pagecontrollers
//
$pc = CPageController::getInstance();

// -------------------------------------------------------------------------------------------
//
// Interception Filter, controlling access, authorithy and other checks.
//
$intFilter = new CInterceptionFilter();

$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRecirectToSignIn();
// Check so that logged in user is admin
$intFilter->IsUserMemberOfGroupAdminOrTerminate();

// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
$user 		= $pc->POSTisSetOrSetDefault('accountid', 0);
$accountName 	= $pc->POSTisSetOrSetDefault('accountname', null);
$name 	= $pc->POSTisSetOrSetDefault('name', '');
$email 	= $pc->POSTisSetOrSetDefault('email', null);
$action	= $pc->POSTisSetOrSetDefault('action', '');

// Check incoming data
$pc->IsNumericOrDie($user, 0);

// Create database object (to get the required sql-config)
$db = new CDatabaseController();
$mysqli = $db->Connect();

// Sanitize data
if ($accountName != null) {
    $accountName = $mysqli->real_escape_string($accountName);
}
$name = $mysqli->real_escape_string($name);
if ($email != null) {
    $email = $mysqli->real_escape_string($email);
}

$query = '';

// Kolla vilken action som gäller och definiera query utifrån detta
if (strcmp($action, 'edit') == 0) {
    $spSetUserNameAndEmail = DBSP_SetUserNameAndEmail;
    $query = "CALL {$spSetUserNameAndEmail}({$user}, '{$accountName}', '{$name}', '{$email}');";
} else if (strcmp($action, 'create') == 0) {
    if (empty($accountName) && empty($email)) {
        $_SESSION['errorMessage'] = "Fel: användarnamn och/eller epost måste innehålla värde(n)";
    }
    $spCreateUserAccountOrEmail = DBSP_CreateUserAccountOrEmail;
    $query = "CALL {$spCreateUserAccountOrEmail}('{$accountName}', '{$name}', '{$email}', 'DIS1000');";
} else if (strcmp($action, 'delete') == 0) {
    $spDeleteUser = DBSP_DeleteUser;
    $query = "CALL {$spDeleteUser}({$user});";
} else {
    die("Bad command. Very bad.");
}

// Errors exist - Exit back to the userlist page
if (!empty($_SESSION['errorMessage'])) {
    $pc->RedirectTo($pc->POSTisSetOrSetDefault('redirect'));
    exit;
}

// Perform the query
$res = $db->MultiQuerySpecial($query);
if ($res != null) {
    // Ignore results but count successful statements.
    $nrOfStatements = $db->RetrieveAndIgnoreResultsFromMultiQuery();
    $log -> debug("Number of statements: " . $nrOfStatements);

    // Kolla vilken action som gäller och kolla hur det gick utfrån detta
    if (strcmp($action, 'edit') == 0) {
        if($nrOfStatements != 1) {
            $_SESSION['errorMessage']	= "Fel: kunde inte uppdatera användare";
        }
    } else if (strcmp($action, 'create') == 0) {
        if($nrOfStatements != 2) {
            $_SESSION['errorMessage']	= "Fel: kunde inte skapa användare";
        }
    } else if (strcmp($action, 'delete') == 0) {
        if($nrOfStatements != 1) {
            $_SESSION['errorMessage']	= "Fel: kunde inte radera användare";
        }
    }
}

$mysqli->close();

// -------------------------------------------------------------------------------------------
//
// Redirect to another page
//
$pc->RedirectTo($pc->POSTisSetOrSetDefault('redirect'));
exit;

?>