<?php
// ===========================================================================================
//
// Class CLoggerDummy
//
// Simple logger - a dummy class for no logging.
// 
// Author: Mats Ljungquist
//
class logging_CLoggerDummy extends logging_CLogger {
	// ------------------------------------------------------------------------------------
	//
	// Constructor
        //
	private function __construct($aLogger) {
            $this -> logger = $aLogger;
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
        public static function getInstance($aLogger) {
            return new self($aLogger);
        }

        public function debug($aMessage) {
            // do nothing
            ;
        }
	
} // End of Of Class

?>