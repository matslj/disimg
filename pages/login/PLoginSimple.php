<?php
// ===========================================================================================
//
// PLogin.php
//
// Show a login-form, ask for user name and password.
//
// Author: Mats Ljungquist
//

// $log = logging_CLogger::getInstance(__FILE__);

// -------------------------------------------------------------------------------------------
//
// Get pagecontroller helpers. Useful methods to use in most pagecontrollers
//
$pc = CPageController::getInstance(FALSE);
// $pc->LoadLanguage(__FILE__);

// -------------------------------------------------------------------------------------------
//
// Interception Filter, controlling access, authorithy and other checks.
//
$intFilter = new CInterceptionFilter();
$intFilter->FrontControllerIsVisitedOrDie();

// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
$adminLogin = $pc->GETisSetOrSetDefault('al', FALSE);

// -------------------------------------------------------------------------------------------
//
// Always redirect to latest visited page on success.
//
$redirectTo = $pc->SESSIONisSetOrSetDefault('history1');
$history2 = $pc->SESSIONisSetOrSetDefault('history2');

// Define variables
$title = "Inloggning";
$buttonText = "Logga in";
$captcha = captcha_CCaptcha::getInstance("");

// -------------------------------------------------------------------------------------------
//
// Show the login-form
//
$htmlRight = "";

$adminLoginText = "";
if ($adminLogin) {
$adminLoginText .= <<<EOD
    <tr>
        <td style="text-align: right">
            <label for="passwordUser">Lösenord: </label>
        </td>
        <td>
            <input id="passwordUser" class="password" type="password" name="passwordUser">
        </td>
    </tr>
EOD;
}

$htmlLeft = "";
$htmlMain = <<<EOD
<h1>{$title}</h1>
<div id='login'>
<fieldset>
<form action="?p=loginp" method="post">
<input type='hidden' name='redirect' value='{$redirectTo}'>
<input type='hidden' name='history1' value='{$redirectTo}'>
<input type='hidden' name='history2' value='{$history2}'>
<input type='hidden' name='admin' value='{$adminLogin}'>
EOD;
if (!$adminLogin) {
    $htmlMain .= "<input type='hidden' value='DIS1000' name='passwordUser'>";
}
$htmlMain .= <<<EOD
    <table>
        <tr>
            <td style="text-align: right">
                <label for="nameUser">Användarnamn: </label>
            </td>
            <td>
                <input id="nameUser" class="login" type="text" name="nameUser">
            </td>
        </tr>
        {$adminLoginText}
        <tr>
            <td>&nbsp;</td>
            <td>
                <!-- Captcha? -->
                {$captcha -> getAsHTML()}
            </td>
        </tr>
        <tr>
            <td colspan='2' style="text-align: right">
                <button type="submit" name="submit">{$buttonText}</button>
            </td>
        </tr>
    </table>
</form>
EOD;
if (!$adminLogin) {
    $htmlMain .= "<p style='text-align:right;'>[<a href='?p=login&al=TRUE'>Logga in som Admin</a>]</p>";
} else {
    $htmlMain .= "<p style='text-align:right;'>[<a href='?p=login'>Logga in som vanlig användare</a>]</p>";
}
$htmlMain .= <<<EOD
</fieldset>
</div> <!-- #login -->
EOD;

// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

$page->printPage('Inloggning', $htmlLeft, $htmlMain, $htmlRight);
exit;
?>