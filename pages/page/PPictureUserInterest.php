<?php
// ===========================================================================================
//
// File: PPictureUserInterest.php
//
// Description: 
// Presents which objects a user are interested in. 
// 
// This page has three columns:
// - left:   list of available folders with number of files of interest in each folder.
//           The number of files in each folder varies depending on choices in the right column.
// - middle: the result of selections made in left column and right column; it shows the
//           which pictures a user are interested in.
// - right:  list of users who has any kind of interest of any file in any folder.
//           The number of files on each user varies depending on choices in the left column.
//
// Author: Mats Ljungquist
//

$log = logging_CLogger::getInstance(__FILE__);

// -------------------------------------------------------------------------------------------
//
// Get pagecontroller helpers. Useful methods to use in most pagecontrollers
//
$pc = CPageController::getInstance();
//$pc->LoadLanguage(__FILE__);

// -------------------------------------------------------------------------------------------
//
// Interception Filter, controlling access, authorithy and other checks.
//
$intFilter = new CInterceptionFilter();

$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRecirectToSignIn();

// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
$folderFilter = $pc->GETisSetOrSetDefault('ff', '');
$userFilter = $pc->GETisSetOrSetDefault('uf', '');

// Validate input data - don't want any funny business here
if (!empty($folderFilter)) {
    $pc->IsNumericOrDie($folderFilter, 0);
}
if (!empty($userFilter)) {
    $pc->IsNumericOrDie($userFilter, 0);
}

// Deside which page this is and compute redirect and action page (process page)
$redirect       = "?p=" . $pc->computePage();
$action = $redirect . "p";

$uo = CUserData::getInstance();
$account = $uo -> getAccount();
$userId	= $uo -> getId();

// -------------------------------------------------------------------------------------------
//
// Get content of file archive from database
//
$db = new CDatabaseController();
$mysqli = $db->Connect();

// Get all the folders from db. This will form the left side folder nav system.
$folderHtml = "";
$currentFolderName = "";
$currentUserName = "";
$usersHtml = "";
$usersHandler = user_CUserRepository::getInstance($db);
$tempUsers = $usersHandler->getUsers();

// Create file handler (CAttachment()). The file handler presents html
// for listing files.
// $attachment = new CAttachment();
// $archiveDb = $attachment ->getFilesOfInterestAsJSON($db, "", "", "", "");

// Table definitions
$tBildIntresse = DBT_BildIntresse;
$tFile         = DBT_File;
$tFolder       = DBT_Folder;
$tFolderUser   = DBT_FolderUser;
$tUser         = DBT_User;

// **************************************************************************************
// *
// * List all folders and store folder id as key and foldername as value in an array.
// * This array will be used when constructing the left side column. So this part
// * is just some pre work for the left side column presentation.
// *
$query = <<< EOD
    SELECT
        idFolder AS folderId,
        nameFolder AS name
    FROM {$tFolder}
    ;
EOD;

$result = Array();

// Perform the query and manage results
$result = $db->Query($query);
$tempFolders = array();
while($row = $result->fetch_object()) {
    $tempFolders[$row->folderId] = $row->name;
}
$result -> close(); // closing the resultset containing user interesst

// **************************************************************************************
// *
// * Get number of hits/user (hits = files marked as interesting by user)
// * in a specific folder (or all folders if no specific folder is chosen)
// * 
// * Also, pick out the current user.
// *
$folderWhere = empty($folderFilter) ? "" : " INNER JOIN {$tFile} ON BildIntresse_idFile = idFile WHERE File_idFolder = {$folderFilter}";

$query = <<< EOD
    SELECT
        BildIntresse_idUser AS userId,
        count(BildIntresse_idUser) AS antal
    FROM {$tBildIntresse}
        {$folderWhere}
    GROUP BY BildIntresse_idUser
    ;
EOD;

$result = Array();

// Perform the query and manage results
$result = $db->Query($query);
$interestUser = array();
while($row = $result->fetch_object()) {
    $interestUser[$row->userId] = $row->antal;
}
$result -> close(); // closing the resultset containing user interesst
$folderPar = empty($folderFilter) ? "" : "&ff={$folderFilter}";
$total = 0;
foreach ($tempUsers as $key => $value) {
    $tempTot = 0;
    if(isset($interestUser[$key])) {
        $tempTot = $interestUser[$key];
        $total = $total + $tempTot;
    }
    $classSelected = "";
    if ($key == $userFilter) {
        $currentUserName = $value;
        $classSelected = " selected";
    }
    $usersHtml .= "<div class='row{$classSelected}'><a href='{$redirect}&uf={$value->getId()}{$folderPar}'>{$value->getName()} ({$tempTot})</a></div>";
}
$markRow = empty($currentUserName) ? " selected" : "";
$usersHtml = "<div class='row all{$markRow}'><a href='{$redirect}{$folderPar}'>Alla ({$total})</a></div>{$usersHtml}";

// **************************************************************************************
// *
// * Get number of hits/folder (hits = files marked as interesting by any user)
// * in a specific folder (or all folders if no specific folder is chosen)
// *
// * Also, pick out the current folder.
// *
$userWhere = empty($userFilter) ? "" : " WHERE BildIntresse_idUser = {$userFilter}";
 // INNER JOIN {$tBildIntresse} ON BildIntresse_idFile = idFile

$query = <<< EOD
    SELECT
        A.File_idFolder AS folderId,
        count(A.idFile) AS antal
    FROM {$tFile} AS A
    INNER JOIN (SELECT DISTINCT BildIntresse_idFile FROM {$tBildIntresse}
                INNER JOIN {$tFile} ON BildIntresse_idFile = idFile{$userWhere}) AS C ON BildIntresse_idFile = idFile
    GROUP BY A.File_idFolder
    ;
EOD;

$result = Array();

// Perform the query and manage results
$result = $db->Query($query);
$interestFolder = array();
while($row = $result->fetch_object()) {
    $interestFolder[$row->folderId] = $row->antal;
}
$result -> close(); // closing the resultset containing user interesst
$userPar = empty($userFilter) ? "" : "&uf={$userFilter}";
$total = 0;
foreach ($tempFolders as $key => $value) {
    $tempTot = 0;
    if(isset($interestFolder[$key])) {
        $tempTot = $interestFolder[$key];
        $total = $total + $tempTot;
    }
    $classSelected = "";
    if ($key == $folderFilter) {
        $currentFolderName = $value;
        $classSelected = " selected";
    }
    $folderHtml .= "<div class='row{$classSelected}'><a href='{$redirect}&ff={$key}{$userPar}'>{$value} ({$tempTot})</a></div>";
}
$markRow = empty($currentFolderName) ? " selected" : "";
$folderHtml = "<div class='row all{$markRow}'><a href='{$redirect}{$userPar}'>Alla ({$total})</a></div>{$folderHtml}";

// ****************************************************************************
// **
// **            Create the middle part of the page
// ** This is the part that contains the result of folderid/userid selections
// ** 
// ** Observe that this part presents the union of folder- and user-selections
// **

$folderWhere = empty($folderFilter) ? "" : " AND File_idFolder = {$folderFilter}";
$userWhere = empty($userFilter) ? "" : " AND BildIntresse_idUser = {$userFilter}";

// Create query
$query 	= <<< EOD
    SELECT 
        A.idFile AS id,
        A.nameFile AS name,
        A.uniqueNameFile AS uniquename,
        A.pathToDiskFile AS path,
        A.createdFile AS created,
        A.File_idUser AS userIdCreator,
        BI.BildIntresse_idUser as userId,
        BI.dateBildIntresse as date,
        A.File_idFolder as folderId
    FROM {$tFile} AS A
        INNER JOIN {$tBildIntresse} AS BI
                ON A.idFile = BI.BildIntresse_idFile
    WHERE
        A.File_idUser = {$uo->getId()} AND
        deletedFile IS NULL
        {$folderWhere}{$userWhere}
        ORDER BY folderId, id, date
        ;
EOD;

// Perform the query and manage results
$results = $db->Query($query);

$prevFolder = 0;
$prevFile = 0;
$contentHtml = "";
$thumbFolder = WS_SITELINK . FILE_ARCHIVE_FOLDER . '/';
$downloadFile = "?p=file-download&amp;referer={$redirect}&amp;file=";
while($row = $results->fetch_object()) {
    if ($prevFolder == 0 || $prevFolder != $row->folderId) {
        if ($prevFolder != 0) {
            $contentHtml .= "</tbody></table>";
        }
        $contentHtml .= "<div class='folderHeader'>" . $tempFolders[$row->folderId] . "</div>";
        $contentHtml .= <<<EOD
            <table class="userInterest">
            <thead>
                <th class="thumb">Tumme</th>
                <th>Filnamn</th>
            </thead>
            <tbody>
EOD;
    }
    $prevFolder = $row->folderId;
    
    if ($prevFile == 0 || $prevFile != $row->id) {
        $thumbs = $thumbFolder . $tempUsers[$row->userIdCreator] -> getAccount() . '/thumbs/' . '80px_thumb_' . $row -> uniquename . ".jpg";
        $ext = pathinfo($row->path, PATHINFO_EXTENSION);
        $imgs = $thumbFolder . $tempUsers[$row->userIdCreator] -> getAccount() . '/' . $row -> uniquename . '.' . $ext;

        // Print file information
        $contentHtml .= <<<EOD
            <tr id='row{$row->uniquename}'>
                <td class="thumb"><a href='{$imgs}'><img src='{$thumbs}' title='Klicka för att titta på bilden' /></a></td>
                <td><a href='{$downloadFile}{$row->uniquename}' title='Click to download file.'>{$row -> name}</a></td>
            </tr>
EOD;
    }
    $prevFile = $row->id;
    $contentHtml .= "<tr class='users'><td>" . $tempUsers[$row->userId] -> getName() . "</td><td style='font-weight: normal; font-size: 8px;'>({$row->date})</td></tr>";

    
    // Print which users are interested in file.
}
if ($prevFolder != 0) {
    $contentHtml .= "</tbody></table>";
}

// $contentHtml .= "<pre>{$query}</pre>";

$results -> close();

// ******************************************************************
// **
// **           Prepare page edit dialog
// **

//$htmlHead = "";
//$javaScript = "";
//
//// Read editable text for page
//$pageName = basename(__FILE__);
//$title          = "";
//$content 	= "";
//$pageId         = 0;
//
//// Get the SP names
//$spGetSidaDetails	= DBSP_PGetSidaDetails;
//
//$query = <<< EOD
//CALL {$spGetSidaDetails}('$pageName');
//EOD;
//
//// Perform the query
//$results = Array();
//$res = $db->MultiQuery($query);
//$db->RetrieveAndStoreResultsFromMultiQuery($results);
//
//// Get article details
//$row = $results[0]->fetch_object();
//if ($row) {
//    $pageId     = $row->id;
//    $content    = $row->content;
//}
//$results[0]->close();
//
//// Create the title of the page (the middle column title)
//$currentFolderName = empty($currentFolderName) ? "Alla" : $currentFolderName;
//$currentUserName = empty($currentUserName) ? "Alla" : $currentUserName;
$title = "";

$htmlPageTitleLink = "Visat intresse";
$htmlPageContent = "";
$htmlPageTextDialog = "";
$hideTitle = true;

// require_once(TP_PAGESPATH . 'page/PPageEditDialog.php');

// -------------------------------------------------------------------------------------------
//
// Close DB connection
//
$mysqli->close();

// -------------------------------------------------------------------------------------------
//
// Add JavaScript and html head stuff related to JavaScript
//
$js = WS_JAVASCRIPT;
$needjQuery = TRUE;
$htmlHead .= <<<EOD
    <!-- jQuery UI -->
    <script src="{$js}jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>

    <!-- jQuery Form Plugin -->
EOD;
    
// ------------------------------------------------------------
// --
// --                  Systemhjälp
// --
$helpContent = <<<EOD
<p>
    Den här sidan visar vilka objekt användare är intresserade av. Sidan
    är uppdelad i tre kolumner:
</p>
<ul>
    <li>
        Kolumn 1 - Tillsammans med kolumn 3 så används den här kolumnen för att 
        filtrera fram de objekt som någon uttryckt intresse för. Den här kolumen
        används för att filtrera på kategori och visar även antalet intresseobjekt
        inom respektive kategori (den här siffran påverkas även av val man gör i
        kolumn 3).
    </li>
    <li>
        Kolumn 2 - Innehåller det filtrerade resultatet av de val man gjort
        i kolumn 1 och 3. Standardresultatet, det som visas när man kommer in på
        sidan, är att visa alla objekt i någon kategori och av någon användare.
    </li>
    <li>
        Kolumn 3 - Tillsammans med kolumn 1 så används den här kolumnen för att 
        filtrera fram de objekt som någon uttryckt intresse för. Den här kolumen
        används för att filtrera på användare och visar även antalet objekt som 
        respektive användare har uttryckt ett intresse för (den här siffran 
        påverkas även av val man gör i kolumn 1).
    </li>
</ul>
EOD;

// Provides help facility - include $htmlHelp in main content
require_once(TP_PAGESPATH . 'admin/PHelpFragment.php');
// -------------------- Slut Systemhjälp ----------------------

// -------------------------------------------------------------------------------------------
//
// Create HTML for middle column
//
$htmlMain = <<<EOD
    <h1>{$htmlPageTitleLink}</h1>
    {$htmlHelp}
    <p>
        {$htmlPageContent}
    </p>
    <div class='section'>
        {$contentHtml}
    </div>
    {$htmlPageTextDialog}
EOD;

// $htmlRight = "<p class='note'>(x) anger antal bilder en användare är intresserad av. 'Alla' visar här den totala 'intressesumman'.</p><div id='navigation'>{$usersHtml}</div>";
$htmlRight = <<<EOD
<div class="Box-A" style="width: 100%; margin-top: 4px;">
    <div class="boxhead">
        <h2>
            <span>Användare </span>
        </h2>
    </div>
    <div class="boxbody" style="padding: 5px;">
        <div id='navigation'>{$usersHtml}</div>
    </div>
</div>
EOD;
        
// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

// Creating the left menu panel
// $htmlLeft = "<p class='note'>(x) anger antal bilder som någon användare visat intresse för i respektive katalog.</p><div id='navigation'>{$folderHtml}</div>";
$htmlLeft = <<<EOD
<div class="Box-A" style="width: 100%;">
    <div class="boxhead">
        <h2>
            <span>Kategori </span>
        </h2>
    </div>
    <div class="boxbody" style="padding: 5px;">
        <div id='navigation'>{$folderHtml}</div>
    </div>
</div>
EOD;

$page->printPage('Bildarkiv', $htmlLeft, $htmlMain, $htmlRight, $htmlHead, $javaScript, $needjQuery);
exit;

?>