<?php
// ===========================================================================================
//
// File: CHTMLHelpers.php
//
// Description: Class CHTMLHelpers
//
// Small code snippets to reduce coding in the pagecontrollers. The snippets are mainly for
// creating HTML code.
//
// Author: Mikael Roos, mos@bth.se
//


class CHTMLHelpers {

	// ------------------------------------------------------------------------------------
	//
	// Internal variables
	//
	

	// ------------------------------------------------------------------------------------
	//
	// Constructor
	//
	public function __construct() { ;	}
	

	// ------------------------------------------------------------------------------------
	//
	// Destructor
	//
	public function __destruct() { ; }

	
	// ------------------------------------------------------------------------------------
	//
	// Create a positive (Ok/Success) feedback message for the user.
	//
	public static function GetHTMLUserFeedbackPositive($aMessage) {
		return "<span class='userFeedbackPositive' style=\"background: url('".WS_IMAGES."/silk/accept.png') no-repeat; padding-left: 20px;\">{$aMessage}</span>";
	}
	
	
	// ------------------------------------------------------------------------------------
	//
	// Create a negative (Failed) feedback message for the user.
	//
	public static function GetHTMLUserFeedbackNegative($aMessage) {
		return "<span class='userFeedbackNegative' style=\"background: url('".WS_IMAGES."/silk/cancel.png') no-repeat; padding-left: 20px;\">{$aMessage}</span>";
	}
	
	
	// ------------------------------------------------------------------------------------
	//
	// Create feedback notices if functions was successful or not. The messages are stored
	// in the session. This is useful in submitting form and providing user feedback.
	// This method reviews arrays of messages and stores them all in an resulting array.
	//
	public static function GetHTMLForSessionMessages($aSuccessList, $aFailedList) {
	
		$messages = Array();
		foreach($aSuccessList as $val) {
			$m = CPageController::GetAndClearSessionMessage($val);
			$messages[$val] = empty($m) ? '' : self::GetHTMLUserFeedbackPositive($m);
		}
		foreach($aFailedList as $val) {
			$m = CPageController::GetAndClearSessionMessage($val);
			$messages[$val] = empty($m) ? '' : self::GetHTMLUserFeedbackNegative($m);
		}

		return $messages;
	}


	// ------------------------------------------------------------------------------------
	//
	// Static function, HTML helper
	// Create a horisontal sidebar menu
	//
	public static function GetSidebarMenu($aMenuitems, $aTarget="") {

		global $gPage;

		$target = empty($aTarget) ? $gPage : $aTarget;

		$menu = "<ul>";
		foreach($aMenuitems as $key => $value) {
			$selected = (strcmp($target, substr($value, 3)) == 0) ? " class='sel'" : "";
			$menu .= "<li{$selected}><a href='{$value}'>{$key}</a></li>";
		}
		$menu .= '</ul>';
		
		return $menu;
	}


} // End of Of Class


?>