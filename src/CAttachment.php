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
        
        private $nrOfFilesRead = 0;

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
        
        private function clear() {
            $this->nrOfFilesRead = 0;
        }
        
        public function getNrOfFilesRead() {
            return $this->nrOfFilesRead;
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
                            updateFolderListItemAll();
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
                                        "------" +
                                        "</td><td class='delCol'><input id='" + data.uploadedFile.id + "#" + data.uploadedFile.uniqueName + "#" + data.uploadedFile.extension + "' class='cbMark' type='checkbox' name='cbMark#" + data.uploadedFile.uniqueName + "'/></td></tr>");
                            var message = "<span class='userFeedbackPositive' style=\"background: url('{$imageLink}/silk/accept.png') no-repeat; padding-left: 20px;\">filen är uppladdad</span>";
                            \$form.find('span.status').html(message);
                        } else {
                            var message = "<span class='userFeedbackNegative' style=\"background: url('{$imageLink}/silk/cancel.png') no-repeat; padding-left: 20px;\">" + data.errorMessage + "</span>";
                            \$form.find('span.status').html(message);
                        }
                        //$.jGrowl("Uploaded file. Done.");
                        // \$form.find('span.status').html(responseText);
                    }
                });
                
                /*
                 * This function updates the list of folders in the left navigation menu
                 * This is a bit of a secret/customized operation and therefore not very good but
                 * I want to make it easy for myself in this case.
                 *
                 * Anyhow, with the help of jQuery, this retrievs all elements with
                 * the '.row' selector and updates their child a-href html text if
                 * the row is a row with the '.all'-class or if the row has the
                 * folder name folderName.
                 *
                 */
                function updateFolderListItemAll() {
                    var e = $('.row.all').first();
                    var h = e.html();
                    e.html(incOrDecNumber(h, true));
                    incPageNumber();
                    uncheckMasterCheckbox();
                }
                
                function moveFolderInList(source, destination) {
                    $('.row').each(function() {
                        var e = $(this).first();
                        var h = e.html();
                        
                        if (h.indexOf(destination) > 0) {
                            e.html(incOrDecNumber(h, true));
                        } else if (h.indexOf(source) > 0) {
                            e.html(incOrDecNumber(h, false));
                        } 
                    });
                    uncheckMasterCheckbox();
                }
                
                function deleteFolderInList(source) {
                    var e = $('.row.all').first();
                    var h = e.html();
                    e.html(incOrDecNumber(h, false));
                    
                    $('.row').each(function() {
                        var e = $(this).first();
                        var h = e.html();
                        
                        if (h.indexOf(source) > 0) {
                            e.html(incOrDecNumber(h, false));
                        } 
                    });
                    uncheckMasterCheckbox();
                }
                
                function incOrDecNumber(html, inc) {
                    var h = html;
                    var start = h.indexOf('(') + 1;
                    var end = h.indexOf(')');
                    var number = parseInt(h.substring(start, end));
                    if (inc) {
                        number++;
                    } else {
                        number--;
                    }
                    var newH = h.substring(0,start) + number + h.substring(end);
                    return newH;
                }
                
                function incPageNumber() {
                    var e = $('#endValue');
                    var h = e.html();
                    var number = parseInt(h);
                    number++;
                    e.html(number);
                }
                
                function checkUncheckAllCheckboxes(element) {
                    var bool = $(element).attr('checked') ? true : false;
                    $('input.cbMark').attr('checked', bool);
                }
                
                function uncheckMasterCheckbox() {
                    $('#masterChk').attr('checked', false);
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
                                            var oldText = $('#row' + indexName + ' td.folderName').text();
                                            deleteFolderInList(oldText);
                                            $('#row' + indexName).remove();
                                        } else {
                                            var oldText = $('#row' + indexName + ' td.folderName').text();
                                            $('#row' + indexName + ' td.folderName').text(data.folderName);
                                            moveFolderInList(oldText, data.folderName);
                                            // $('select').find(":selected").text(data.folderName + ' (' + data.facet + ')');
                                        }
                                        console.log(index1);
                                        console.log(checkedList[i].substring(index1 + 1, index2));
                                    }
                                }
                                $('.cbMark').attr('checked', false);
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
                $spListFiles = DBSP_ListFilesXXX;
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
        
        /**
         * Returns the total number of files uploaded by the current (admin) user.
         * This method is only to be used by admins.
         * 
         * @param type $db a reference to a database object
         * @return int the number of files uploaded by the current admin user
         *             if the user is not an admin -1 will be returned, signaling
         *             that something is wrong (in effect, that this method is
         *             not to be used in this context).
         */
        public function getTotalNrOfFiles($db) {
            $total = -1;
            // Get user-object
            $uo = CUserData::getInstance();
            if ($uo -> isAdmin()) {
                $total = 0;
                $spListFiles = DBSP_ListFilesXXX;
                $query = "CALL {$spListFiles}('{$uo->getId()}');";
            
                // Perform the query
                $res = $db->MultiQuery($query);

                // Use results
                $results = Array();
                $db->RetrieveAndStoreResultsFromMultiQuery($results);
                while($row = $results[0]->fetch_object()) {
                    $total++;
                }
                // Close result set
                $results[0]->close();
            }
            return $total;
        }
        
        /**
         * Returns a list of files as a HTML table. The list consist of varius file info and
         * a column with checkboxes. This checkbox can be used for whatever.
         * 
         * @param type $db an active database connection. Mandatory.
         * @param type $userId a userId. Mandatory (I do not know why though, could get the data from the user object).
         * @param type $referer Optional. If present with idReference set to refId will be listed
         *                      otherwise files belonging to userId will be listed. 
         * @param type $folderId if this parameter is present, the method will only list files
         *                       from the specified folder.
         * @param type $chkDisable
         * @return type a list of files in the form of a HTML table.
         */
        public function getFileList($db, $fileDto) {
            // Assumes the presence of a working mysqli-object
            // No defensive programming!
            
            $this->clear();

            $userId = $fileDto -> getUserId();
            $referer = $fileDto -> getReferer();
            $folderId = $fileDto -> getFolderId();
            $chkDisable = $fileDto -> getChkDisable();

            // Get user-object
            $uo = CUserData::getInstance();
            
            $query = "";
            
            // If user is admin, all files uploaded by the user can be viewed
            // either as a whole or by folder.
            if ($uo -> isAdmin()) {
                if (empty($folderId)) {
                    $spListFiles = DBSP_ListFiles;
                    $query 	= <<< EOD
                    CALL {$spListFiles}('{$userId}','{$fileDto->getPageCriteria()}');
EOD;
                } else {
                    $spListFiles = DBSP_ListFilesInFolder;
                    $query 	= <<< EOD
                    CALL {$spListFiles}('{$userId}', $folderId,'{$fileDto->getPageCriteria()}');
EOD;
                }
            } else {
                // If user is not admin, only files made accessible by an admin
                // can be viewed; either as a whole or by folder.
                if (empty($folderId)) {
                    $spListFiles = DBSP_ListAllAccessedFiles;
                    $query 	= <<< EOD
                    CALL {$spListFiles}('{$userId}','{$fileDto->getPageCriteria()}');
EOD;
                } else {
                    $spListFiles = DBSP_ListAllAccessedFilesInFolder;
                    $query 	= <<< EOD
                    CALL {$spListFiles}('{$userId}', $folderId,'{$fileDto->getPageCriteria()}');
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
                $this -> nrOfFilesRead++;
                $checked = "";
                
                    if(isset($row->interest) && $row->interest != null) {
                        if ($row->interest == 1) {
                            $checked = " checked";
                        }
                    }
                
                $thumbs = $thumbFolder . $row -> account . '/thumbs/' . '80px_thumb_' . $row -> uniquename . ".jpg";
                $ext = pathinfo($row->path, PATHINFO_EXTENSION);
                $imgs = $thumbFolder . $row -> account . '/' . $row -> uniquename . '.' . $ext;
                // $deleteCol = "<td><a href='{$deleteFile}{$row->uniquename}&amp;ext={$ext}' title='Click to delete file.'>[delete]</a></td>";
                $disabled = $chkDisable && $uo -> isAdmin() ? " disabled" : "";
                $deleteCol = "<td class='delCol'><input id='{$row->id}#{$row->uniquename}#{$ext}' class='cbMark' type='checkbox' name='cbMark#{$row->uniquename}'{$disabled}{$checked}/></td>";
                $adminColumns = "";
                if ($uo -> isAdmin()) {
                    $adminColumns .= <<<EOD
                        <td>{$row->size}</td>
                        <td>{$row->created}</td>
                        <!--
                        <td title='{$row->path}'>{$row->uniquename}</td>

                        <td>{$row->mimetype}</td>

                        <td>{$row->modified}</td>
                        -->
                        
EOD;
                }
                $archiveDb .= <<<EOD
                    <tr id='row{$row->uniquename}'>
                    <td><a href='{$imgs}'><img src='{$thumbs}' title='Klicka för att titta på bilden' /></a></td>
                    <td><a href='{$downloadFile}{$row->uniquename}' title='Click to download file.'>{$row -> name}</a></td>
                    {$adminColumns}
                    <td class='folderName'>{$row->foldername}</td>
                    {$deleteCol}
                    </tr>
EOD;
            }
            
            $adminColumns = "";
            if ($uo -> isAdmin()) {
                $adminColumns .= <<<EOD
                    <th>Storlek</th>
                    <th>Skapad</th>
                    <!--
                    <th>Unique</th>

                    <th>Type</th>

                    <th>Modified</th>
                    -->
EOD;
            }
            
            $masterChk = "<input id='masterChk' type='checkbox' name='masterChk' onclick='checkUncheckAllCheckboxes(this);' />";
            if ($chkDisable || !$uo -> isAdmin()) {
                $masterChk = '&nbsp;';
            }
            
            // Start table
            $archiveDbStart = <<<EOD
                Visar objekt {$fileDto->getCurrentSelection()}
                <table class="disImgTable" style="width:100%">
                <thead>
                <th class="thumb">Tumme</th>
                <th>Filnamn</th>
                {$adminColumns}
                <th>Kategori</th>
                <th class="knapp">{$masterChk}</th>
                </thead>
                <tbody id='{$this -> fileListId}'>
EOD;
            
            $archiveDb = $archiveDbStart . $archiveDb;
            
            // Finish table
            $archiveDb .= <<<EOD
                </tbody>
                <!--
                <tfoot>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                </tfoot>
                -->
                </table>
                {$fileDto->getNavbar()}
EOD;

            // Close result set
            $results[0]->close();
            
            return $archiveDb;
        }
        
        public function getFilesOfInterestAsJSON($db, $userId, $folderId, $orderOn, $orderOrder) {
            // I'm assuming that all incoming data has been properly sanitized.
            
            $this->clear();
            
            // Get user-object
            $uo = CUserData::getInstance();
            
            // Table defenitions
            $tBildIntresse = DBT_BildIntresse;
            $tFile         = DBT_File;
            $tFolder       = DBT_Folder;
            $tFolderUser   = DBT_FolderUser;
            $tUser         = DBT_User;
            
            $bildIntresseJoin = "";
            $userWhere = "";
            
            // If a userId is provided; list only files which the user with that
            // userId has expressed an interest in.
            if (!empty($userId)) {
                $bildIntresseJoin = " INNER JOIN {$tBildIntresse} AS BI ON BI.BildIntresse_idFile = A.idFile ";
                $userWhere = " AND WHERE BI.BildIntresse_idUser = {$userId} ";
            }
            
            // If a folderId is provided; list only files in that folder
            $folderWhere = empty($folderId) ? "" : " AND A.File_idFolder = {$folderId}";
            
            // If a field, to order on, has been provided - order on that field by the
            // provided order order (and ASC if no order order is provided).
            $orderClause = "";
            if (!empty($orderOn)) {
                $orderClause = " ORDER BY {$orderOn} ";
                $tempOrder = empty($orderOrder) ? "ASC" : $orderOrder;
                $orderClause .= $tempOrder;
            }
            
            // Create query
            $query 	= <<< EOD
                SELECT 
                    A.idFile AS id,
                    A.nameFile AS name,
                    A.uniqueNameFile AS uniquename,
                    A.pathToDiskFile AS path,
                    A.createdFile AS created,
                    U.accountUser AS account,
                    IFNULL(F.nameFolder, "------") AS foldername
                FROM {$tFile} AS A
                    INNER JOIN {$tUser} AS U
                            ON A.File_idUser = U.idUser
                    INNER JOIN {$tFolder} AS F
                            ON A.File_idFolder = F.idFolder
                WHERE
                        A.File_idUser = {$uo->getId()} AND
                        deletedFile IS NULL
                        {$folderWhere}
                {$orderClause};
EOD;
            
            $resultArray = Array();
            
            // Perform the query and manage results
            $results = $db->Query($query);
            
            while($row = $results->fetch_object()) {
                $ext = pathinfo($row->path, PATHINFO_EXTENSION);
                $rowArray = Array();
                $rowArray['id'] = $row->id;
                $rowArray['name'] = $row->name;
                $rowArray['uniquename'] = $row->uniquename;
                $rowArray['path'] = $row->path;
                $rowArray['ext'] = $ext;
                $rowArray['created'] = $row->created;
                $rowArray['account'] = $row->account;
                $rowArray['foldername'] = $row->foldername;
                $resultArray[] = $rowArray;
            }

            $results->close();
            
            return json_encode($resultArray);
        }
        
} // End of Of Class

?>