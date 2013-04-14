<?php
// ===========================================================================================
//
// Class CDatabaseController
//
// To ease database usage for pagecontroller. Supports MySQLi.
//
// Author : Mats Ljungquist
//

// Include commons for database
require_once(TP_SQLPATH . 'config.php');


class CDatabaseController {

	// ------------------------------------------------------------------------------------
	//
	// Internal variables
	//
	protected $iMysqli;


	// ------------------------------------------------------------------------------------
	//
	// Constructor
	//
	public function __construct() {

		$this->iMysqli = FALSE;
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
	// Connect to the database, return a database object.
	//
	public function Connect() {
            if (!$this->isResource($this->iMysqli)) {

		$this->iMysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);

		if (mysqli_connect_error()) {
   			echo "Connect to database failed: ".mysqli_connect_error()."<br>";
   			exit();
		}
            }

            return $this->iMysqli;
	}
        
        private function isResource($possibleResource) { 
            return !is_null(@get_resource_type($possibleResource));
        }


	// ------------------------------------------------------------------------------------
	//
	// Execute a database multi_query
	//
	public function MultiQuery($aQuery) {

		$res = $this->iMysqli->multi_query($aQuery)
			or die("Could not query database, query =<br/><pre>{$aQuery}</pre><br/>{$this->iMysqli->error}");

		return $res;
	}
        
        // ------------------------------------------------------------------------------------
	//
	// Execute a database multi_query
        // Den här metoden används specifikt för när man ska skapa en ny användare,
        // den fångar nämligen felkoden 1062, och lägger ut ett felmeddelande.
	//
	public function MultiQuerySpecial($aQuery) {

		$res = $this->iMysqli->multi_query($aQuery);
                if ($res == null) {
                    if ($this->iMysqli->errno == 1062) {
                        $_SESSION['errorMessage'] = "Fel: Det finns redan en användare med det användarnamnet/epostadressen";
                        $res = null;
                    } else die("Could not query database, query =<br/><pre>{$aQuery}</pre><br/>{$this->iMysqli->error}");
                }

		return $res;
	}


	// ------------------------------------------------------------------------------------
	//
	// Retrieve and store results from multiquery in an array.
	//
	public function RetrieveAndStoreResultsFromMultiQuery(&$aResults) {

		$mysqli = $this->iMysqli;

		$i = 0;
		do {
			$aResults[$i++] = $mysqli->store_result();
		} while($mysqli->more_results() && $mysqli->next_result());

		// Check if there is a database error
                !$mysqli->errno
        	or die("<p>Failed retrieving resultsets.</p><p>Query =<br/><pre>{$query}</pre><br/>Error code: {$this->iMysqli->errno} ({$this->iMysqli->error})</p>");
	}


	// ------------------------------------------------------------------------------------
	//
	// Retrieve and ignore results from multiquery, count number of successful statements
	// Some succeed and some fail, must count to really know.
	//
	public function RetrieveAndIgnoreResultsFromMultiQuery() {

		$mysqli = $this->iMysqli;

		$statements = 0;
		do {
			$res = $mysqli->store_result();
			$statements++;
		} while($mysqli->more_results() && $mysqli->next_result());

		return $statements;
	}


	// ------------------------------------------------------------------------------------
	//
	// Load a database query from file in the directory TP_SQLPATH
	//
	public function LoadSQL($aFile) {

		$mysqli = $this->iMysqli;
		require(TP_SQLPATH . $aFile);
		return $query;
	}


	// ------------------------------------------------------------------------------------
	//
	// Execute a database query
	//
	public function Query($aQuery) {

		$res = $this->iMysqli->query($aQuery)
			or die("Could not query database, query =<br/><pre>{$aQuery}</pre><br/>{$this->iMysqli->error}");

		return $res;
	}


} // End of Of Class

?>