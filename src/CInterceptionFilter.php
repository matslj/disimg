<?php
// ===========================================================================================
//
// Class CInterceptionFilter
//
// Used in each pagecontroller to check access, authority.
//
//
// Author: Mats Ljungquist
//


class CInterceptionFilter {
    
        private $uo = null;
        private $pc = null;
        
        public static $LOG = null;

	// ------------------------------------------------------------------------------------
	//
	// Internal variables
	//

	// ------------------------------------------------------------------------------------
	//
	// Constructor
	//
	public function __construct() {
            $this -> uo = CUserData::GetInstance();
            $this -> pc = CPageController::getInstance();
            self::$LOG = logging_CLogger::getInstance(__FILE__);
	}


	// ------------------------------------------------------------------------------------
	//
	// Destructor
	//
	public function __destruct() {
		;
	}


	// ------------------------------------------------------------------------------------
	//
	// Check if index.php (frontcontroller) is visited, disallow direct access to
	// pagecontrollers
	//
	public function FrontControllerIsVisitedOrDie() {

                // När man använder det reserverade ordet 'global' så innebär det att i en funktion
                // talar om att man vill referera till den globala 'varianten' av variabeln (Closesurs)
		global $gPage; // Always defined in frontcontroller

		if(!isset($gPage)) {
			die('No direct access to pagecontroller is allowed.');
		}
	}


	// ------------------------------------------------------------------------------------
	//
	// Check if user has signed in or redirect user to sign in page
	//
	public function UserIsSignedInOrRecirectToSignIn() {

		if(!$this -> uo -> isAuthenticated()) {
                    $_SESSION['errorMessage'] = 'Du måste vara inloggad för att komma åt den sidan';
                    $_SESSION['redirect'] = $_GET['p'];
                    require_once(TP_SOURCEPATH . 'CHTMLPage.php');
                    CHTMLPage::redirectTo('login');
		} else {
                    $_SESSION['redirect'] = '';
                    unset($_SESSION['redirect']);
                }
	}

	// ------------------------------------------------------------------------------------
	//
	// Check if admin
	//
	public function UserIsMemberOfGroupAdminOrDie() {
            // User must be member of group adm or die
            if(!$this -> uo -> isAdmin())
                    die('You do not have the authourity to access this page');
	}

        // ------------------------------------------------------------------------------------
	//
	// Check if user belongs to the admin group or is a specific user.
	//
	public function IsUserMemberOfGroupAdminOrIsCurrentUser($aUserId) {
		return $this -> uo -> isAdmin() || $this -> uo -> isUser($aUserId);
	}
        
        // ------------------------------------------------------------------------------------
	//
	// Check if user belongs to the admin group or is a specific user.
	//
	public function IsUserMemberOfGroupAdmin() {
		return $this -> uo -> isAdmin();
	}
        
        // ------------------------------------------------------------------------------------
	//
	// Check if user belongs to the admin group or is a specific user.
	//
	public function IsUserMemberOfGroupAdminOrTerminate() {
		if (!$this -> uo -> isAdmin())
                    die('Unauthorized access. Terminating');
	}
        
        // ------------------------------------------------------------------------------------
	//
	// Check if user belongs to the admin group or is a specific user.
	//
	public function IsUserMemberOfGroupAdminOrIsCurrentUserOrTerminate($aUserId) {
                $userId = empty($aUserId) ? $this -> uo -> getId() : $aUserId;
                self::$LOG -> debug("userid: " . $userId);
                // sanitize data
                // $sanUserId = filter_var($userId, FILTER_SANITIZE_STRING);
                CPageController::IsNumericOrDie($userId);
		if (!($this -> uo -> isAdmin() || $this -> uo -> isUser($userId)))
                        die('Unauthorized access. Terminating');
	}


} // End of Of Class

?>