<?php
// ===========================================================================================
//
// Class CPagecontroller
//
// Nice to have utility for common methods useful in most pagecontrollers.
//
class CPageController {
    
        private static $instance;

	// ------------------------------------------------------------------------------------
	//
	// Internal variables
	//
	public $lang = Array();


	// ------------------------------------------------------------------------------------
	//
	// Constructor
	// @param historize if true -> mark page in history
        //        BEWARE of multiple constructs of CPageController if trying to disable history (solved - singelton).
	private function __construct($historize) {
            if ($historize === TRUE) {
		$_SESSION['history2'] = self::SESSIONisSetOrSetDefault('history1', 'home');
		$_SESSION['history1'] = self::CurrentURL();
		// print_r($_SESSION);
            }
            // print_r($_SESSION);
	}

	// ------------------------------------------------------------------------------------
	//
	// Destructor
	//
	public function __destruct() {
		;
	}
        
        public static function getInstance($historize = TRUE) {
            if (empty(self::$instance)) {
                self::$instance = new self($historize);
            }
            return self::$instance;
        }

	// ------------------------------------------------------------------------------------
	//
	// Load language file
	//
	public function LoadLanguage($aFilename) {

		// Load language file
		$langFile = TP_LANGUAGEPATH . WS_LANGUAGE . '/' . substr($aFilename, strlen(TP_ROOT));

		if(!file_exists($langFile)) {
			die(sprintf("Language file does not exists: $s", $langFile));
		}

		require_once($langFile);
		$this->lang = array_merge($this->lang, $lang);
	}

        public function computePage() {
            global $gPage;
            global $gSubPage;
            $returnValue = $gPage;
            if (!empty($gSubPage)) {
                $returnValue = $returnValue . '_' . $gSubPage;
            }
            return $returnValue;
        }

        public function computeRedirect() {
            return '&amp;redirect=' . $this->computePage();
        }

	// ------------------------------------------------------------------------------------
	//
	// Check if corresponding $_GET[''] is set, then use it or return the default value.
	//
	public static function GETisSetOrSetDefault($aEntry, $aDefault = '') {

		return isset($_GET["$aEntry"]) && !empty($_GET["$aEntry"]) ? $_GET["$aEntry"] : $aDefault;
	}


	// ------------------------------------------------------------------------------------
	//
	// Check if corresponding $_POST[''] is set, then use it or return the default value.
	//
	public static function POSTisSetOrSetDefault($aEntry, $aDefault = '') {

		return isset($_POST["$aEntry"]) && !empty($_POST["$aEntry"]) ? $_POST["$aEntry"] : $aDefault;
	}
        
        // ------------------------------------------------------------------------------------
	//
	// Check if corresponding $_POST[''] is set, then use it or return the default value.
	//
	public static function REQUESTisSetOrSetDefault($aEntry, $aDefault = '') {

		return isset($_REQUEST["$aEntry"]) && !empty($_REQUEST["$aEntry"]) ? $_REQUEST["$aEntry"] : $aDefault;
	}

        // ------------------------------------------------------------------------------------
	//
	// Check if corresponding $_SESSION[''] is set, then use it or return the default value.
	//
	public static function SESSIONisSetOrSetDefault($aEntry, $aDefault = '') {

		return isset($_SESSION["$aEntry"]) && !empty($_SESSION["$aEntry"]) ? $_SESSION["$aEntry"] : $aDefault;
	}

        // ------------------------------------------------------------------------------------
	//
	// Sets a session attribute and return the value.
	//
	public static function SESSIONSet($aEntry, $value) {
                $_SESSION[$aEntry] = $value;
		return $value;
	}

	// ------------------------------------------------------------------------------------
	//
	// Check if the value is numeric and optional in the range.
	//
	public static function IsNumericOrDie($aVar, $aRangeLow = '', $aRangeHigh = "") {

		$inRangeH = empty($aRangeHigh) ? TRUE : ($aVar <= $aRangeHigh);
		$inRangeL = empty($aRangeLow)  ? TRUE : ($aVar >= $aRangeLow);
		if(!(is_numeric($aVar) && $inRangeH && $inRangeL)) {
			die(sprintf("The variable value '$s' is not numeric or it is out of range.", $aVar));
		}
	}

        // ------------------------------------------------------------------------------------
	//
	// Check if the value is a string.
	//
	public static function IsStringOrDie($aVar) {

		if(!is_string($aVar)) {
			die(sprintf("The variable value '$s' is not a string.", $aVar));
		}
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


	// ------------------------------------------------------------------------------------
	//
	// Static function
	// Redirect to another page
	// Support $aUri to be local uri within site or external site (starting with http://)
	// If empty, redirect to home page of current module.
	//
	public static function RedirectTo($aUri) {
                if (empty($aUri)) {
                    $aUri = WS_HOME;
                    $_SESSION['errorMessage'] = "The requested page does not exist";
                }
		if(!strncmp($aUri, "http://", 7)) {
			;
		} else if(!strncmp($aUri, "?", 1)) {
			$aUri = WS_SITELINK . "{$aUri}";
		} else {
			$aUri = WS_SITELINK . "?p={$aUri}";
		}

		header("Location: {$aUri}");
		exit;
	}


	// ------------------------------------------------------------------------------------
	//
	// Static function
	// Create a URL to the current page.
	//
	public static function CurrentURL() {

		// Create link to current page
		$refToThisPage = "http";
		$refToThisPage .= (@$_SERVER["HTTPS"] == "on") ? 's' : '';
		$refToThisPage .= "://";
		$serverPort = ($_SERVER["SERVER_PORT"] == "80") ? '' : ":{$_SERVER['SERVER_PORT']}";
		$refToThisPage .= $serverPort . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

		return $refToThisPage;
	}
        
        /**
         * 
         * @param type $totalrows
         * @param type $numLimit
         * @param type $amm
         * @param type $queryStr
         * @return type a string array. This array contains:
         *              pos 0 - mysql LIMIT clause -> this is to be added last
         *                      in the query
         *              pos 1 - html presenting current selection
         *              pos 2 - html control panel (with step left/right arrows)
         */
        public static function pageBrowser($totalrows,$numLimit,$amm,$queryStr) {
            
            $pc = CPageController::getInstance();
            
            // Get parameters defining current selection
            $numBegin = $pc->GETisSetOrSetDefault('numBegin', '');
            $begin = $pc->GETisSetOrSetDefault('begin', '');
            $num = $pc->GETisSetOrSetDefault('num', '');
            
            // Check parameters (only if not empty)
            if (!empty($numBegin)) $pc->IsNumericOrDie($numBegin, 0);
            if (!empty($begin)) $pc->IsNumericOrDie($begin, 0);
            if (!empty($num)) $pc->IsNumericOrDie($num, 0);

            $larrow = " << Föregående ".$numLimit." "; //You can either have an image or text, eg. Previous
            $rarrow = " Nästa ".$numLimit." >> "; //You can either have an image or text, eg. Next
            $wholePiece = "";
            //$wholePiece .= "Alla attribut: {$totalrows} {$numLimit} {$amm} {$queryStr} {$numBegin} {$begin} {$num}   ";
            $wholePiece .= "Sida: "; //This appears in front of your page numbers

            if ($totalrows > 0) {
                $numSoFar = 1;
                $cycle = ceil($totalrows/$amm);
                if (empty($numBegin) || $numBegin < 1) {
                    $numBegin = 1;
                    $num = 1;
                }
                $minus = $numBegin-1;
                $start = $minus*$amm;
                if (empty($begin)) {
                    $begin = $start;
                }
                $preBegin = $numBegin-$numLimit;
                $preStart = $amm*$numLimit;
                $preStart = $start-$preStart;
                $preVBegin = $start-$amm;
                $preRedBegin = $numBegin-1;
                if ($start > 0 || $numBegin > 1) {
                    $wholePiece .= "<a href='?num=".$preRedBegin
                            ."&numBegin=".$preBegin
                            ."&begin=".$preVBegin
                            ."&p="
                            .$queryStr."'>"
                            .$larrow."</a>\n";
                }
                for ($i=$numBegin;$i<=$cycle;$i++) {
                    if ($numSoFar == $numLimit+1) {
                        $piece = "<a href='?numBegin=".$i
                            ."&num=".$i
                            ."&begin=".$start
                            ."&p="
                            .$queryStr."'>"
                            .$rarrow."</a>\n";
                        $wholePiece .= $piece;
                        break;
                    }
                    $piece = "<a href='?begin=".$start
                        ."&num=".$i
                        ."&numBegin=".$numBegin
                        ."&p="
                        .$queryStr
                        ."'>";
                    if ($num == $i) {
                        $piece .= "</a><b>$i</b><a>";
                    } else {
                        $piece .= "$i";
                    }
                    $piece .= "</a>\n";
                    $start = $start+$amm;
                    $numSoFar++;
                    $wholePiece .= $piece;
                }
                $wholePiece .= "\n";
                $wheBeg = $begin+1;
                $wheEnd = $begin+$amm;
                $wheToWhe = "<b>".$wheBeg."</b> - <b id='endValue'>";
                if ($totalrows <= $wheEnd) {
                    $wheToWhe .= $totalrows."</b>";
                } else {
                    $wheToWhe .= $wheEnd."</b>";
                }
                $sqlprod = " LIMIT ".$begin.", ".$amm;
            } else {
                $wholePiece = "Det finns inget att lista.";
                $wheToWhe = "<b>0</b> - <b id='endValue'>0</b>";
            }

            return array($sqlprod,$wheToWhe,$wholePiece);
        }


} // End of Of Class

?>