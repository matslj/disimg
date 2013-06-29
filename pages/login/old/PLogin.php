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
//$intFilter->UserIsSignedInOrRecirectToSignIn();
//$intFilter->UserIsMemberOfGroupAdminOrDie();

// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
$create	= $pc->GETisSetOrSetDefault('createAccount', FALSE);
$captchaChoice = $pc->GETisSetOrSetDefault('captchaChoice', "");

// -------------------------------------------------------------------------------------------
//
// Always redirect to latest visited page on success.
//
$redirectTo = $pc->SESSIONisSetOrSetDefault('history1');
$history2 = $pc->SESSIONisSetOrSetDefault('history2');

// Define variables
$title = "Login";
$buttonText = "Log on";
$captcha = captcha_CCaptcha::getInstance($captchaChoice);

// Create new account?
if ($create) {
    $title = "Create account";
    $buttonText = "Create";
}

// -------------------------------------------------------------------------------------------
//
// Show the login-form
//
$htmlRight = "";
if ($create) {
$htmlRight .= <<<EOD
<h3 class='columnMenu'>Options</h3>
<p>
Already have an account? Goto <a href='?p=login'>login</a>.
</p>
<p>
Captcha choices:
</p>
<ul>
<li>
    <a href='?p=login&createAccount=TRUE&captchaChoice=securimage'>Securimage</a>
</li>
<li>
    <a href='?p=login&createAccount=TRUE&captchaChoice=dummy'>No captcha</a>
</li>
</ul>
EOD;
} else {
$htmlRight .= <<<EOD
<h3 class='columnMenu'>Test users</h3>
<p>
Two testusers are prepared (username - password):
</p>
<ul>
<li>mikael - hemligt</li>
<li>doe - doe</li>
</ul>
EOD;
}

$htmlLeft = "";
$htmlMain = <<<EOD
<h1>{$title}</h1>
<div class='sidebox'>
<div id='login'>
<fieldset>
<form action="?p=loginp" method="post">
<input type='hidden' name='redirect' value='{$redirectTo}'>
<input type='hidden' name='history1' value='{$redirectTo}'>
<input type='hidden' name='history2' value='{$history2}'>
<!-- Tells the process (of the form) that a new account is to be created -->
<input type='hidden' name='createNewAccount' value='{$create}'>
<table>
<tr>
    <td>&nbsp;</td>
    <td><p>Enter username and password</p></td>
</tr>
<tr>
<td style="text-align: right">
<label for="nameUser">Username: </label>
</td>
<td>
<input id="nameUser" class="login" type="text" name="nameUser">
</td>
</tr>
<tr>
<td style="text-align: right">
<label for="passwordUser">Password: </label>
</td>
<td>
<input id="passwordUser" class="password" type="password" name="passwordUser">
</td>
</tr>
EOD;
if ($create) {
$htmlMain .= <<<EOD
<tr>
<td style="text-align: right">
<label for="passwordUserAgain">Retype password: </label>
</td>
<td>
<input id="passwordUserAgain" class="password" type="password" name="passwordUserAgain">
</td>
</tr>
<tr>
    <td>&nbsp;</td>
    <td>
        <!-- Captcha? -->
        {$captcha -> getAsHTML()}
    </td>
</tr>
EOD;
}
$htmlMain .= <<<EOD
<tr>
<td colspan='2' style="text-align: right">
<button type="submit" name="submit">{$buttonText}</button>
</td>
</tr>
</table>
</form>
EOD;
if (!$create) {
    $htmlMain .= "<p>[<a href='?p=login&createAccount=TRUE'>Create new account</a>]</p>";
}
$htmlMain .= <<<EOD
</fieldset>
<!--
<p><a href="PGetPassword.php">Skapa en ny användare!</a></p>
<p><a href="PGetPassword.php">Jag har glömt mitt lösenord!</a></p>
-->
</div> <!-- #login -->
</div> <!-- .sidebox -->

EOD;


// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

$page->printPage('Template', $htmlLeft, $htmlMain, $htmlRight);
exit;


?>