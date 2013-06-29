<?php

// ===========================================================================================
//
// Class CLogger
//
// Simple logger.
// 
// Author: Mats Ljungquist
//
abstract class logging_CLogger {

	// ------------------------------------------------------------------------------------
	//
	// Internal variables
	//
        protected $logger = "";


	// ------------------------------------------------------------------------------------
	//
	// Constructor
        //
	private function __construct() {
	}


	// ------------------------------------------------------------------------------------
	//
	// Destructor
	//
	public function __destruct() {
	}
        
        // ------------------------------------------------------------------------------------
	//
	// Factory method pattern
        // This class should at its minimum be used as follows:
        // $log = logging_CLogger::getInstance(__FILE__);
        // from the file needing the logger.
        // 
	// @param aLogger For this class to be meningful the caller should use __FILE__ as a parameter
        public static function getInstance($aLogger) {
            switch (WS_LOGGER) {
                case 'file':
                    // file based logger
                    return logging_CLoggerFile::getInstance($aLogger);
                    break;
                default:
                    // dummy = no logging
                    return logging_CLoggerDummy::getInstance($aLogger);
                    break;
            }
        }

        // ------------------------------------------------------------------------------------
	//
	// Writes a message to the logging system.
        // 
	// @param the message to write.
        abstract function debug($aMessage);

} // End of Of Class

?>