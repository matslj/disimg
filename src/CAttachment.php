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

        private $fileListId = "fileList";

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
            $this -> fileListId = "fileList";
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
        public function getJavaScript($redirect) {
            // Link to images
            $imageLink = WS_IMAGES;
            $uploadLink = WS_SITELINK . FILE_ARCHIVE_FOLDER;
            $pages = TP_PAGESPATH;
            $uo = CUserData::getInstance();
            $admin = $uo -> isAdmin() ? "true" : "false";
             
            
            $javaScript = <<<EOD
                // ----------------------------------------------------------------------------------------------
                //
                // Initiate JavaScript when document is loaded.
                //
                $(document).ready(function() {
                    var listElementSelector = "#{$this -> fileListId}";
                    var listElement = $(listElementSelector);
//                    $(listElementSelector + '.delete').on('click', function() {
//                        $(this).closest('li').remove();
//                    }
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
                        dataType: 'json',
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
                        var queryString = $.param(formData);
                        console.log(queryString);
                        var file = $('#fileInput').val();
                        if (typeof file === 'undefined' || file == '') {
                            var message = "<span class='userFeedbackNegative' style=\"background: url('{$imageLink}/silk/cancel.png') no-repeat; padding-left: 20px;\">Ingen fil angiven</span>";
                            \$form.find('span.status').html(message);
                            return false;
                        } else {
                            $.jGrowl('Before submit...');
                            \$form.find('span.status').html(loader); // Write to status element -> loader image
                            return true; // True = do not abort submit
                        }
                    } 

                    // post-submit callback 
                    // old delete-link <a href='?p=file-delete&amp;referer={$redirect}&amp;file=" + data.uploadedFile.uniqueName + "&amp;ext=" + data.uploadedFile.extension + "' title='Klicka för att radera bilden.'>[delete]</a>
                    function showResponse(data, statusText, xhr, \$form) {
                        if (typeof data.errorMessage === 'undefined') {
                            var link = "{$uploadLink}/" + data.uploadedFile.accountName + "/";
                            var theFile = data.uploadedFile.uniqueName + "." + data.uploadedFile.extension;
                            // window.location = "?p={$redirect}";
                            listElement.prepend("<tr id='row"+ data.uploadedFile.uniqueName + "'><td><a href='" + link + theFile + "'><img src='" + link + "thumbs/80px_thumb_" + data.uploadedFile.uniqueName + ".jpg' title='Klicka för att se bilden' /></a></td><td><a href='?p=file-download&amp;file=" +
                                        data.uploadedFile.uniqueName +
                                        "' title='Klicka för att ladda ner fil.'>" +
                                        data.uploadedFile.fileName +
                                        "</a> NEW!</td><td>" +
                                        data.uploadedFile.size +
                                        "</td><td>" +
                                        data.uploadedFile.created +
                                        "</td><td class='folderName'>" +
                                        "Ingen katalog" +
                                        "</td><td><input id='" + data.uploadedFile.id + "#" + data.uploadedFile.uniqueName + "#" + data.uploadedFile.extension + "' class='cbMark' type='checkbox' name='cbMark#" + data.uploadedFile.uniqueName + "'/></td></tr>");
                            var message = "<span class='userFeedbackPositive' style=\"background: url('{$imageLink}/silk/accept.png') no-repeat; padding-left: 20px;\">filen är uppladdad</span>";
                            \$form.find('span.status').html(message);
                        } else {
                            var message = "<span class='userFeedbackNegative' style=\"background: url('{$imageLink}/silk/cancel.png') no-repeat; padding-left: 20px;\">" + data.errorMessage + "</span>";
                            \$form.find('span.status').html(message);
                        }
                        $.jGrowl("Uploaded file. Done.");
                        // \$form.find('span.status').html(responseText);
                    }
                });
                
                function getFileDataFromCheckbox() {
                }
                
                // Hämtar id på alla checkade checkboxar, lägger dessa i en array
                // och skickar iväg den.
                function sendCheckedCheckboxes(actionType, idFolder) {
                    if (!{$admin}) {
                        return false;
                    }
                    actionType = typeof actionType !== 'undefined' ? actionType : 'file-deleteMulti';
                    idFolder = typeof idFolder !== 'undefined' ? '&folderid=' + idFolder : '';
                    
                    // Sök ut markerade checkboxar
                    var checkedList = [];
                    $('input.cbMark').each( function() {
                        if ($(this).attr('checked')) {
                            checkedList.push($(this).attr('id'));
                        }
                    });
                    
                    // Gör bara något om någon checkbox var markerad
                    if (checkedList.length != 0) {
                        // Sätt jGrowl-message utifrån den action som ska
                        // utföras.
                        var jGrowlMsg = "Raderar";
                        if (actionType != 'file-deleteMulti') {
                            jGrowlMsg = "Flyttar";
                        }
                        $.jGrowl(jGrowlMsg + " filer...");

                        // Förbered Ajax-call
                        $.ajax({
                            url:'?p=' + actionType + idFolder,
                            type:'POST',
                            dataType: "json",
                            data: {filenames:checkedList, action:actionType},
                            success: function(data) {
                                $.jGrowl("Klar");
                                for (var i = 0; i < checkedList.length; i++) {
                                    var index1 = checkedList[i].indexOf('#');
                                    var index2 = checkedList[i].indexOf('#', index1 + 2);
                                    if (index1 >= 0 && index2 >= 0) {
                                        var indexName = checkedList[i].substring(index1 + 1, index2);
                                        if (data.action == 'file-deleteMulti') {
                                            $('#row' + indexName).remove();
                                        } else {
                                            $('#row' + indexName + ' td.folderName').text(data.folderName);
                                            // $('select').find(":selected").text(data.folderName + ' (' + data.facet + ')');
                                        }
                                        console.log(index1);
                                        console.log(checkedList[i].substring(index1 + 1, index2));
                                    }
                                }
                                //$('input.cbMark').remove();
                                // alert(res);
                            }
                        });
                    } else {
                        $.jGrowl("Inget händer - ingen kryssruta är markerad.");
                    }
                }
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
                        <input type="hidden" name="MAX_FILE_SIZE" value="{$maxFileSize}">
                        {$params}
                        <label for='file'>Fil:</label>
                        <input id='fileInput' name='file' type='file' />
                        <div id='file'>
                            <div>&nbsp;</div>
                            <button id='submit-ajax' type='submit' name='do-submit' value='{$typeOfSubmit}'>Ladda upp</button>
                            <span class='status'></span>
                        </div>
                    </fieldset>
                </form>
EOD;
            return $html;
        }

                // ------------------------------------------------------------------------------------
	//
	// Returns the attachment presentation in html form.
        //
        // @param parameters - if ajax isn't used, the whole form has to be preserved. To do this
        //                     all parameters/form inputs has to be sent to the processpage as a key value pair
        //                     array. The processpage will then return all the parameters to the caller.
	// @return the captcha in HTML form
        public function getAsHTMLNoUploadButton() {
            $maxFileSize = FILE_MAX_SIZE;
            $formId = uniqid(); // Maybe it should be a parameter instead
            $html = <<<EOD
                <form id='form{$formId}' enctype="multipart/form-data" action="{$this -> action}" method="post">
                    <fieldset class='standard'>
                        <input type="hidden" name="MAX_FILE_SIZE" value="{$maxFileSize}">
                        <label for='file'>Fil:</label>
                        <input name='file' type='file'>
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
	// Returns a list of files as a HTML table. The list consist of varius file info and
        // a column with checkboxes. This checkbox can be used for whatever.
        // 
        // @param db an active database connection. Mandatory.
        // @param userId a userId. Mandatory (I do not know why though, could get the data from the user object).
        // @param refId Optional. If present with idReference set to refId will be listed
        //                        otherwise files belonging to userId will be listed. 
        // @param folderId if this parameter is present, the method will only list files
        //                 from the specified folder.
	// @return a list of files in the form of a HTML table.
        public function getFileList($db, $userId, $referer, $folderId) {
            // Assumes the presence of a working mysqli-object
            // No defensive programming!

            // Get user-object
            $uo = CUserData::getInstance();
            
            $query = "";
            
            // If user is admin, all files uploaded by the user can be viewed
            // either as a whole or by folder.
            if ($uo -> isAdmin()) {
                if (empty($folderId)) {
                    $spListFiles = DBSP_ListFiles;
                    $query 	= <<< EOD
                    CALL {$spListFiles}('{$userId}');
EOD;
                } else {
                    $spListFiles = DBSP_ListFilesInFolder;
                    $query 	= <<< EOD
                    CALL {$spListFiles}('{$userId}', $folderId);
EOD;
                }
            } else {
                // If user is not admin, only files made accessible by an admin
                // can be viewed; either as a whole or by folder.
                if (empty($folderId)) {
                    $spListFiles = DBSP_ListAllAccessedFiles;
                    $query 	= <<< EOD
                    CALL {$spListFiles}('{$userId}');
EOD;
                } else {
                    $spListFiles = DBSP_ListAllAccessedFilesInFolder;
                    $query 	= <<< EOD
                    CALL {$spListFiles}('{$userId}', $folderId);
EOD;
                }
            }
            
            // Perform the query
            $res = $db->MultiQuery($query);

            // Use results
            $results = Array();
            $db->RetrieveAndStoreResultsFromMultiQuery($results);
            
            // $deleteFile = "?p=file-delete&amp;referer={$referer}&amp;file=";
            // <input type="checkbox" name="vehicle" value="Bike">
            $deleteCol = "";
            $downloadFile = "?p=file-download&amp;referer={$referer}&amp;file=";
            $archiveDb = "";
            $thumbFolder = WS_SITELINK . FILE_ARCHIVE_FOLDER . '/';
            
            // Populate table with content
            while($row = $results[0]->fetch_object()) {
                $checked = "";
                if ($row->interest != null) {
                    if ($row->interest == 1) {
                        $checked = " checked";
                    }
                }
                $thumbs = $thumbFolder . $row -> account . '/thumbs/' . '80px_thumb_' . $row -> uniquename . ".jpg";
                $ext = pathinfo($row->path, PATHINFO_EXTENSION);
                $imgs = $thumbFolder . $row -> account . '/' . $row -> uniquename . '.' . $ext;
                // $deleteCol = "<td><a href='{$deleteFile}{$row->uniquename}&amp;ext={$ext}' title='Click to delete file.'>[delete]</a></td>";
                $deleteCol = "<td><input id='{$row->id}#{$row->uniquename}#{$ext}' class='cbMark' type='checkbox' name='cbMark#{$row->uniquename}'{$checked}/></td>";
                $archiveDb .= <<<EOD
                    <tr id='row{$row->uniquename}'>
                    <td><a href='{$imgs}'><img src='{$thumbs}' title='Klicka för att titta på bilden' /></a></td>
                    <td><a href='{$downloadFile}{$row->uniquename}' title='Click to download file.'>{$row -> name}</a></td>
                    <td>{$row->size}</td>
                    <td>{$row->created}</td>
                    <!--
                    <td title='{$row->path}'>{$row->uniquename}</td>
                    
                    <td>{$row->mimetype}</td>
                    
                    <td>{$row->modified}</td>
                    -->
                    <td class='folderName'>{$row->foldername}</td>
                    {$deleteCol}
                    </tr>
EOD;
            }
            
            // Start table
            $archiveDbStart = <<<EOD
                <table class="disImgTable" style="width:100%">
                <thead>
                <th>Tumme</th>
                <th>Filnamn</th>
                <th>Storlek</th>
                <th>Skapad</th>
                <!--
                <th>Unique</th>
                
                <th>Type</th>
                
                <th>Modified</th>
                -->
                <th>katalog</th>
                <th class="knapp">&nbsp;</th>
                </thead>
                <tbody id='{$this -> fileListId}'>
EOD;
            
            $archiveDb = $archiveDbStart . $archiveDb;
            
            // Finish table
            $archiveDb .= <<<EOD
                </tbody>
                <tfoot>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                </tfoot>
                </table>
EOD;

            // Close result set
            $results[0]->close();
            
            return $archiveDb;
        }
        
        public function getOptionList($db, $userId, $referer) {
            $options = "";
            
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
            
            while($row = $results[0]->fetch_object()) {
                $options .= <<<EOD
                    <option value="{$row->uniquename}">{$row -> name}</option>
EOD;
            }
        }
        
} // End of Of Class

?>