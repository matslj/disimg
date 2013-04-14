<?php

// ===========================================================================================
// CUserData
//
// Description: 
// Class for storing user data. Requires an active session (i.e session_start()).
// The class follows a singelton pattern which retrieves a requested instance either from
// active request, from session och creates a new object (and places it in session).
// 
// The user saving to session part is transparent to the user of this class (except from the session_start()).
// 
// REMEMBER: __autoload before session_start(); Otherwise odd errors might occur.
//
// Author: Mats Ljungquist
//
class CUserData {
    
    private $id;
    private $account;
    private $name;
    private $email;
    private $avatar;
    private $idGroup;

    private function __construct() {
        ;
    }

    public function __destruct() {
        ;
    }
    
    public static function getInstance() {
        if (!isset($_SESSION[__CLASS__]['uo'])) {
            $_SESSION[__CLASS__]['uo'] = new self();
        }
        return $_SESSION[__CLASS__]['uo'];
    }
    
    // This method does some additional handling when a store is made
    // All methods manipulating private data in this class must call this method.
    private function storeEvent() {
        // In order to make this class backwards compatible, with my legacy code
        // I do this (for code not using the CUser-object):
        $_SESSION['idUser'] = $this -> id;
        $_SESSION['accountUser'] = $this -> account;
        $_SESSION['groupMemberUser']= $this -> idGroup;       
    }
    
    public function populateUserData($idUser, $accountUser, $nameUser, $emailUser, $avatarUser, $idGroup) {
        $this -> id = $idUser;
        $this -> account = $accountUser;
        $this -> name = $nameUser;
        $this -> email = $emailUser;
        $this -> avatar = $avatarUser;
        $this -> idGroup = $idGroup;
        
        $this -> storeEvent();
    }
    
    public function getId() {
        return $this -> id;
    }

    public function setId($id) {
        $this -> id = $id;
        $this -> storeEvent();
    }

    public function getAccount() {
        return $this -> account;
    }

    public function setAccount($account) {
        $this -> account = $account;
        $this -> storeEvent();
    }

    public function getName() {
        return $this -> name;
    }

    public function setName($name) {
        $this -> name = $name;
        $this -> storeEvent();
    }

    public function getEmail() {
        return $this -> email;
    }

    public function setEmail($email) {
        $this -> email = $email;
        $this -> storeEvent();
    }

    public function getAvatar() {
        return $this -> avatar;
    }

    public function setAvatar($avatar) {
        $this -> avatar = $avatar;
        $this -> storeEvent();
    }

    public function getIdGroup() {
        return $this -> idGroup;
    }

    public function setIdGroup($idGroup) {
        $this -> idGroup = $idGroup;
        $this -> storeEvent();
    }
    
    public function isAuthenticated() {
        return empty($this -> id) ? false : true;
    }
    
    public function isAdmin() {
        return strcmp($this->idGroup, 'adm') == 0;
    }
    
    public function isUser($aUserId) {
        return $aUserId === $this -> id;
    }

}

?>