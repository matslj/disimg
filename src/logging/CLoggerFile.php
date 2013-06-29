<?php
define("NL", "\r\n"); // New Line - this one is suited for win-environment
define("FILENAME", "sitelog.txt"); // Name of logfile - dwells in TP_LOGPATH
// ===========================================================================================
//
// Class CLoggerFile
//
// Simple logger - file based.
// 
// Author: Mats Ljungquist
//
class logging_CLoggerFile extends logging_CLogger {

	// ------------------------------------------------------------------------------------
	//
	// Internal variables
	//
        private $fh = null;

	// ------------------------------------------------------------------------------------
	//
	// Constructor
        //
	private function __construct($aLogger) {
            $this -> logger = $aLogger;
            $filename = TP_LOGPATH . FILENAME;
            $this -> fh = null;

            $log = "*********** " . date("Y/m/d H:i:s"). substr((string)microtime(), 1, 6) . " ***********" . NL;
            
            $mode = "";
            if (file_exists($filename)) {
                $mode = "ab";
                $log = NL . NL . $log;
            } else {
                $mode = "wb";
            }
            $this -> fh = fopen($filename, $mode) or die("CLoggerFile.php - ERROR - can't open file");
            fwrite($this -> fh, $log);
	}


	// ------------------------------------------------------------------------------------
	//
	// Destructor
	//
	public function __destruct() {
                fclose($this -> fh);
	}
        
        // ------------------------------------------------------------------------------------
	//
        public static function getInstance($aLogger) {
            return new self($aLogger);
        }

        public function debug($aMessage) {
            $log = NL . basename($this -> logger) . ": " . $aMessage;
            fwrite($this -> fh, $log);
        }
	

} // End of Of Class

?>