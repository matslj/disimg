<?php

/**
 * A dto for file handling.
 * 
 * @author Mats Ljungquist
 */
class CFileDto {
    
    // Input fields
    private $userId;       // id of current user
    private $referer;      // referring html page
    private $folderId;     // id of current folder
    private $chkDisable;   // true to disable checkboxes
    
    private $pageCriteria; // A criteria by which the query should be limited
    
    // Output fields
    private $htmlTable;  // the f

    
    
    public function __construct($userId, $referer, $folderId = "", $chkDisable = false) {
        $this->userId = $userId;
        $this->referer = $referer;
        $this->folderId = $folderId;
        $this->chkDisable = $chkDisable;
        $this->htmlTable = "";
    }

    public function __destruct() {
        ;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function getReferer() {
        return $this->referer;
    }

    public function setReferer($referer) {
        $this->referer = $referer;
    }

    public function getFolderId() {
        return $this->folderId;
    }

    public function setFolderId($folderId) {
        $this->folderId = $folderId;
    }

    public function getChkDisable() {
        return $this->chkDisable;
    }

    public function setChkDisable($chkDisable) {
        $this->chkDisable = $chkDisable;
    }

    public function getHtmlTable() {
        return $this->htmlTable;
    }

    public function setHtmlTable($htmlTable) {
        $this->htmlTable = $htmlTable;
    }
    
    public function getPageCriteria() {
        return $this->pageCriteria;
    }

    public function setPageCriteria($pageCriteria) {
        $this->pageCriteria = $pageCriteria;
    }

}

?>