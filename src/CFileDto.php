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
    
    // Page pagination
    private $pageCriteria;     // A criteria by which the query should be limited
    private $currentSelection; // html for current selection
    private $navbar;           // html seletion pages nav bar
    
    public function __construct($userId, $referer, $folderId = "", $chkDisable = false) {
        $this->userId = $userId;
        $this->referer = $referer;
        $this->folderId = $folderId;
        $this->chkDisable = $chkDisable;
    }

    public function __destruct() {
        ;
    }
    
    public function setPagePagination($navigate) {
        $this->pageCriteria = $navigate[0];
        $this->currentSelection = $navigate[1];
        $this->navbar = $navigate[2];
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
    
    public function getPageCriteria() {
        return $this->pageCriteria;
    }

    public function setPageCriteria($pageCriteria) {
        $this->pageCriteria = $pageCriteria;
    }
    
    public function getCurrentSelection() {
        return $this->currentSelection;
    }

    public function setCurrentSelection($currentSelection) {
        $this->currentSelection = $currentSelection;
    }

    public function getNavbar() {
        return $this->navbar;
    }

    public function setNavbar($navbar) {
        $this->navbar = $navbar;
    }

}

?>