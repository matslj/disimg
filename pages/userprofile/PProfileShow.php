<?php
// ===========================================================================================
//
// PProfileShow.php
//
// Show the users profile information in a form and make it possible to edit the information.
//

$log = logging_CLogger::getInstance(__FILE__);

// Get user-object
$uo = CUserData::getInstance();

$userId = $uo -> getId();

$log -> debug("UO = " . $uo -> getId());
// -------------------------------------------------------------------------------------------
//
// Get pagecontroller helpers. Useful methods to use in most pagecontrollers
//
$pc = CPageController::getInstance();
//$pc->LoadLanguage(__FILE__);

// -------------------------------------------------------------------------------------------
//
// Interception Filter, access, authorithy and other checks.
//
$intFilter = new CInterceptionFilter();
$intFilter->frontcontrollerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRecirectToSignIn();
//$intFilter->userIsMemberOfGroupAdminOrDie();
$intFilter ->IsUserMemberOfGroupAdminOrIsCurrentUser($userId);

// -------------------------------------------------------------------------------------------
//
// Page specific code
//
$htmlLeft = "";
$htmlRight = "";
$htmlMain = <<<EOD
<h1>Kontoinställningar</h1>
EOD;

// I could have gone directly on the userObject in session, but this wont work
// if admin is supposed to be able to change userdata. Therefore I read from db.

// -------------------------------------------------------------------------------------------
//
// Create a new database object, connect to the database.
//
$db 	= new CDatabaseController();
$mysqli = $db->Connect();

// Get the SP names
$spUserDetails = DBSP_GetUserDetails;

// Create the query
$query = "CALL {$spUserDetails}({$userId});";
$log ->debug($query);
// Perform the query
$res = $db->MultiQuery($query);

// -------------------------------------------------------------------------------------------
//
// Show the results of the query
//
// Use results
$results = Array();
$db->RetrieveAndStoreResultsFromMultiQuery($results);
$index = 0;
$row = $results[$index]->fetch_object();

$idUser = $row -> id;
$accountUser = $row -> account;
$emailUser = $row -> email;
$idGroup = $row -> groupid;
$nameGroup = $row -> groupname;
$avatarUser = $row -> avatar;
$gravatar = $row -> gravatar;
$gravatarsmall = $row -> gravatarsmall;

$readonly = "";
if ($uo -> isAdmin()) {
$readonly .= <<< EOD
    <tr>
        <td><label for="account">Lösenord:</label></td>
        <td style='text-align: right;'><input class='password' type='password' name='password1'></td>
    </tr>
    <tr>
        <td><label for="account">Lösenord (igen):</label></td>
        <td style='text-align: right;'><input class='password' type='password' name='password2'></td>
    </tr>
    <tr>
        <td colspan='2' style='text-align: right;'><button type='submit' name='submit' value='change-password'>Ändra lösenord</button></td>
    </tr>
EOD;
}

$action = "?p=profilep";
$redirect = "?p=profile";
$imageLink = WS_IMAGES;

$htmlMain .= <<< EOD
<div id="userProfileWrap">
<div id="userProfile">

    <!-- userid and password -->
    <h2>Användarid</h2>
    <form action='{$action}' method='POST'>
        <input type='hidden' name='redirect' value='{$redirect}#basic'>
        <input type='hidden' name='redirect-fail' value='{$redirect}'>
        <input type='hidden' name='accountid' value='{$idUser}'>
        <fieldset class='accountsettings'>
            <table width='99%'>
                <tr>
                    <td><label for="account">Användarid:</label></td>
                    <td style='text-align: right;'><input class='account-dimmed' type='text' name='account' readonly value='{$accountUser}'></td>
                </tr>
                {$readonly}
            </table>
        </fieldset>
    </form>

    <!-- email -->
    <h2 id='email'>Epost</h2>
    <form action='{$action}' method='POST'>
        <input type='hidden' name='redirect' value='{$redirect}#email'>
        <input type='hidden' name='redirect-failure' value='{$redirect}'>
        <input type='hidden' name='accountid' value='{$idUser}'>
        <fieldset class='accountsettings'>
            <table width='99%'>
                <tr>
                    <td><label for="account">Epost: </label></td>
                    <td style='text-align: right;'><input class='email' type='text' name='email' value='{$emailUser}' /></td>
                </tr>
                <tr>
                    <td colspan='2' style='text-align: right;'><button type='submit' name='submit' value='change-email'>Ändra epost</button></td>
                </tr>
            </table>
        </fieldset>
    </form>

    <!-- avatar -->
    <h2 id='avatar'>Avatar</h2>
    <form action='{$action}' method='POST'>
        <input type='hidden' name='redirect' value='{$redirect}#avatar'>
        <input type='hidden' name='redirect-failure' value='{$redirect}'>
        <input type='hidden' name='accountid' value='{$idUser}'>
        <fieldset class='accountsettings'>
            <table width='99%'>
                <tr>
                    <td><label for="account">Avatar:</label></td>
                    <td style='text-align: right;'><input class='avatar' type='text' name='avatar' value='{$avatarUser}' placeholder="Insert link to avatar here">
                </td>
                </tr>
                <tr>
                    <td><img src='{$avatarUser}' alt=':)'></td>
                    <td style='text-align: right;'><button type='submit' name='submit' value='change-avatar'>Ändra avatar</button></td>
                </tr>
            </table>
        </fieldset>
    </form>
    
    <!-- gravatar -->
    <h2 id='gravatar'>Gravatar</h2>
    <form action='{$action}' method='POST'>
        <input type='hidden' name='redirect' value='{$redirect}#gravatar'>
        <input type='hidden' name='redirect-failure' value='{$redirect}'>
        <input type='hidden' name='accountid' value='{$idUser}'>
        <fieldset class='accountsettings'>
        <table width='99%'>
            <tr>
                <td colspan='2'><p>Din Gravatar från <a href='http://gravatar.com'>gravatar.com</a></p></td>
            </tr>
            <tr>
                <td><label for="gravatar">Gravatar id (epost):</label></td>
                <td style='text-align: right;'><input class='gravatar' type='text' name='gravatar' value='{$gravatar}' placeholder="Insert gravatar id here"></td>
            </tr>
            <tr>
                <td><img src='{$gravatarsmall}' alt=''></td>
                <td style='text-align: right;'><button type='submit' name='submit' value='change-gravatar'>Ändra gravatar</button></td>
            </tr>
        </table>
        </fieldset>
    </form>
    
</div> <!-- div userProfile -->
</div> <!-- div userProfileWrap -->
EOD;

// -------------------------------------------------------------------------------------------
//
// Close the connection to the database
//
$results[$index]->close();
$mysqli->close();


// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
require_once(TP_SOURCEPATH . 'CHTMLPage.php');

$page = new CHTMLPage(WS_STYLESHEET);

$page->printPage("Your account", $htmlLeft, $htmlMain, $htmlRight);
exit;

?>