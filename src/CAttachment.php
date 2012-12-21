<?php

// ===========================================================================================
//
// Class CAttachment
//
// Provides support for attatchment of files. This includes upload, view and download of files.
// 
// The class supports two modes:
// - Ajax: jQuery.form-plugin is used. getHead() and getJavaScript() must be used by the page to set upp the plugin.
// - Non ajax: Only getAsHTML() has to be used. BUT all parameters that has to be preserved has to be sent into the method.
// 
// The getAsHTML()-method shall be used by the FORM-page.
// 
// Author: Mats Ljungquist
//
class CAttachment {

        // All attachements use the same processing page. So all upload forms
        // have the same action attribute. This attribute is defined by FILE_ACTION in config.php.
        private $action = "";

	// ------------------------------------------------------------------------------------
	//
	// Internal variables
	//

	// ------------------------------------------------------------------------------------
	//
	// Constructor
        //
	public function __construct() {
            $this -> action = FILE_ACTION;
	}


	// ------------------------------------------------------------------------------------
	//
	// Destructor
	//
	public function __destruct() {
	}
        
        // ------------------------------------------------------------------------------------
	//
	// Head nesseccary for the javascript of this class to work. The class works without
        // javascript though.
	//
        public function getHead() {
            $js = WS_JAVASCRIPT;
            $htmlHead = <<<EOD
                <!-- jGrowl notices -->
                <link rel='stylesheet' href='{$js}jgrowl/jquery.jgrowl.css' type='text/css' />
                <script type='text/javascript' src='{$js}jgrowl/jquery.jgrowl.js'></script>
                <!-- jQuery Form Plugin -->
                <script type='text/javascript' src='{$js}jquery-form/jquery.form.js'></script>
EOD;
                return $htmlHead;
        }
        
        // ------------------------------------------------------------------------------------
        // 
        // href='http://jquery.malsup.com/form/#file-upload'
	//
	// The javascript of this class. The class works without it, but it will work neater
        // with ajax capabilities. If you have multiple forms on the page (and hence have
        // called the getAsHtml() multiple times, you still only have to call this method once.
        // It is constructed to handle multiple forms.
	//
        public function getJavaScript() {
            // Link to images
            $imageLink = WS_IMAGES;
            
            $javaScript = <<<EOD
                // ----------------------------------------------------------------------------------------------
                //
                // Initiate JavaScript when document is loaded.
                //
                $(document).ready(function() {
                    // Preload loader image
                    var loader = new Image();
                    loader.src = "{$imageLink}/loader.gif";
                    loader.align = "baseline";

                    // ----------------------------------------------------------------------------------------------
                    //
                    // Use jQuery.form-plugin to make Ajax submit
                    //
                    // http://malsup.com/jquery/form/
                    //

                    // Preparing jQuery.form-options
                    // $.ajax options can be used here too, see http://api.jquery.com/jQuery.ajax/
                    var options = {
                        //timeout: 1000,
                        // return a datatype of json
                        //dataType: 'json',
                        // remove short delay before posting form when uploading files
                        //forceSync: true,	
                        // form should always target the server response to an iframe. This is useful in conjuction with file uploads.
                        //iframe: true,

                        beforeSubmit: prepareSubmit,
                        success: showResponse
                    };

                    $('form').ajaxForm(options);

                    // pre-submit callback 
                    function prepareSubmit(formData, \$form, options) { 
                        $.jGrowl('Before submit...');
                         \$form.find('span.status').html(loader); // Write to status element -> loader image
                        return true; // True = do not abort submit
                    } 

                    // post-submit callback 
                    function showResponse(responseText, statusText, xhr, \$form)  { 
                        $.jGrowl("Uploaded file. Done.");
                        \$form.find('span.status').html(responseText);
                    }
                });
EOD;
            return $javaScript;
        }

        // ------------------------------------------------------------------------------------
	//
	// Returns the attachment presentation in html form.
        // 
        // @param parameters - if ajax isn't used, the whole form has to be preserved. To do this
        //                     all parameters/form inputs has to be sent to the processpage as a key value pair
        //                     array. The processpage will then return all the parameters to the caller.
	// @return the captcha in HTML form
        public function getAsHTML($parameters = null) {
            // Ajax or non ajax submit?
            $typeOfSubmit = is_null($parameters) ? 'upload-return-html' : 'single-by-traditional-form';
            $params = "";
            if (!is_null($parameters) && is_array($parameters)) {
                foreach ($parameters as $key => $value) {
                    $params .= "<input type='hidden' name='{$key}' value='{$value}'>";
                }
            }
            $maxFileSize = FILE_MAX_SIZE;
            $formId = uniqid(); // Maybe it should be a parameter instead
            $html = <<<EOD
                <form id='form{$formId}' enctype="multipart/form-data" action="{$this -> action}" method="post">
                    <fieldset class='standard'>
                        <legend>Attachments</legend>
                        <input type="hidden" name="MAX_FILE_SIZE" value="{$maxFileSize}">
                        {$params}
                        <label for='file'>File to upload:</label>
                        <input name='file' type='file'>
                        <div id='file'>&nbsp;</div>
                            <button id='submit-ajax' type='submit' name='do-submit' value='{$typeOfSubmit}'>Upload</button>
                            <span class='status'></span>
                        </div>
                    </fieldset>
                </form>
EOD;
            return $html;
        }
        
        // ------------------------------------------------------------------------------------
	//
	// Returns a list of files as HTML. The list consists of a download link and a delete link.
        // 
        // @param db an active database connection. Mandatory.
        // @param userId a userId. Mandatory.
        // @param refId Optional. If present with idReference set to refId will be listed
        //                        otherwise files belonging to userId will be listed. 
	// @return a list of files in the form of a HTML table.
        public function getDownloads($db, $userId, $referer, $refId = "") {
            // Assumes the presence of a working mysqli-object
            // No defensive programming!
            
            // List by userId or list by refId
            $spListFiles = DBSP_UseReferenceToListFiles; 
            if (empty($refId)) {
                $spListFiles = DBSP_ListFiles;
                $refId = $userId;
            }
            
            // Create the query
            $query 	= <<< EOD
            CALL {$spListFiles}('{$refId}');
EOD;

            // Perform the query
            $res = $db->MultiQuery($query);

            // Use results
            $results = Array();
            $db->RetrieveAndStoreResultsFromMultiQuery($results);

            $downloadFile = "?p=file-download&amp;referer={$referer}&amp;file=";
            $archiveDb = "";
            
                        // Start table
            $archiveDb .= <<<EOD
                <table width='99%'>
                <thead>
                <th>Download</th>
                <th>Size</th>
                <th>Modified</th>
                </thead>
                <tbody>
EOD;
            
            $nrOfRows = 0;
            // Populate table with content
            while($row = $results[0]->fetch_object()) {
                $nrOfRows++;
                $archiveDb .= <<<EOD
                    <tr>
                    <td><a href='{$downloadFile}{$row->uniquename}' title='Click to download file.'>{$row -> name}</a></td>
                    <td>{$row->size}</td>
                    <td>{$row->modified}</td>
                    </tr>
EOD;
            }
            
            // Finish table
            $archiveDb .= <<<EOD
                </tbody>
                <tfoot>
                </tfoot>
                </table>
EOD;

            // Close result set
            $results[0]->close();
            $archiveDb = $nrOfRows == 0 ? "" : $archiveDb;
            return $archiveDb;
        }

        // ------------------------------------------------------------------------------------
	//
	// Returns a list of files as HTML. The list consists of a download link and a delete link.
        // 
        // @param db an active database connection. Mandatory.
        // @param userId a userId. Mandatory.
        // @param refId Optional. If present with idReference set to refId will be listed
        //                        otherwise files belonging to userId will be listed. 
	// @return a list of files in the form of a HTML table.
        public function getFileList($db, $userId, $referer) {
            // Assumes the presence of a working mysqli-object
            // No defensive programming!
            
            $spListFiles = DBSP_ListFiles;
            
            // Create the query
            $query 	= <<< EOD
            CALL {$spListFiles}('{$userId}');
EOD;

            // Perform the query
            $res = $db->MultiQuery($query);

            // Use results
            $results = Array();
            $db->RetrieveAndStoreResultsFromMultiQuery($results);
            
            $deleteFile = "?p=file-delete&amp;referer={$referer}&amp;file=";
            $deleteCol = "";
            $someDelete = FALSE;
            $downloadFile = "?p=file-download&amp;referer={$referer}&amp;file=";
            $archiveDb = "";
            
            // Populate table with content
            while($row = $results[0]->fetch_object()) {
                if ($row->owner == $userId) {
                    $deleteCol = "<td><a href='{$deleteFile}{$row->uniquename}' title='Click to delete file.'>[delete]</a></td>";
                    $someDelete = TRUE;
                } else {
                    $deleteCol = "";
                }
                $archiveDb .= <<<EOD
                    <tr>
                    <td><a href='{$downloadFile}{$row->uniquename}' title='Click to download file.'>{$row -> name}</a></td>
                    <!--
                    <td title='{$row->path}'>{$row->uniquename}</td>
                    <td>{$row->size}</td>
                    <td>{$row->mimetype}</td>
                    <td>{$row->created}</td>
                    <td>{$row->modified}</td>
                    -->
                    {$deleteCol}
                    </tr>
EOD;
            }
            
            $deleteCol = $someDelete == TRUE ? "<th>Delete</th>" : "";
            
            // Start table
            $archiveDbStart = <<<EOD
                <table width='99%'>
                <thead>
                <th>Download</th>
                <!--
                <th>Unique</th>
                <th>Size</th>
                <th>Type</th>
                <th>Created</th>
                <th>Modified</th>
                -->
                {$deleteCol}
                </thead>
                <tbody>
EOD;
            
            $archiveDb = $archiveDbStart . $archiveDb;
            
            // Finish table
            $archiveDb .= <<<EOD
                </tbody>
                <tfoot>
                </tfoot>
                </table>
EOD;

            // Close result set
            $results[0]->close();
            
            return $archiveDb;
        }
        
} // End of Of Class

?>